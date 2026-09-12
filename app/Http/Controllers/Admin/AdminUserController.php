<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * Create a new user (customer, vendor, or admin)
     */
    public function store(Request $request)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['user', 'vendor', 'admin'])],
            'business_name' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'is_approved' => ['nullable', 'boolean'],
            'mark_verified' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'email_verified_at' => $request->boolean('mark_verified', true) ? now() : null,
        ]);

        // If role is vendor, automatically create or link VendorProfile
        if ($user->isVendor()) {
            $businessName = filled($request->business_name) ? trim($request->business_name) : ($user->name . ' Events');
            $baseSlug = Str::slug($businessName ?: 'vendor-' . $user->id);
            $slug = $baseSlug;
            $c = 1;
            while (VendorProfile::where('slug', $slug)->exists()) {
                $c++;
                $slug = "{$baseSlug}-{$c}";
            }

            VendorProfile::create([
                'user_id' => $user->id,
                'business_name' => $businessName,
                'slug' => $slug,
                'city' => $request->city ?: null,
                'phone' => $request->phone ?: null,
                'is_approved' => $request->boolean('is_approved', true),
            ]);
        }

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
            ->with('success', "User '{$user->name}' created successfully with role '" . ucfirst($user->role) . "'.");
    }

    /**
     * Quick role updater for admin table
     */
    public function updateRole(Request $request, User $user)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(['user', 'vendor', 'admin'])],
        ]);

        // Safeguard: Do not allow current logged-in admin to demote themselves
        if (auth()->id() === $user->id && $validated['role'] !== 'admin') {
            return back()->with('error', 'Security warning: You cannot change your own admin role.');
        }

        $oldRole = $user->role;
        $newRole = $validated['role'];

        $user->role = $newRole;
        $user->save();

        // If newly switched to vendor, ensure VendorProfile exists
        if ($newRole === 'vendor') {
            if (!$user->vendorProfile) {
                $businessName = $user->name . ' Events';
                $baseSlug = Str::slug($businessName ?: 'vendor-' . $user->id);
                $slug = $baseSlug;
                $c = 1;
                while (VendorProfile::where('slug', $slug)->exists()) {
                    $c++;
                    $slug = "{$baseSlug}-{$c}";
                }

                VendorProfile::create([
                    'user_id' => $user->id,
                    'business_name' => $businessName,
                    'slug' => $slug,
                    'is_approved' => true,
                ]);
            } else {
                $user->vendorProfile->update(['is_approved' => true]);
            }
        }

        return back()->with('success', "Role for '{$user->name}' changed from " . ucfirst($oldRole) . " to " . ucfirst($newRole) . ".");
    }

    /**
     * Update user details, role, password, and vendor profile
     */
    public function update(Request $request, User $user)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in(['user', 'vendor', 'admin'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'is_approved' => ['nullable', 'boolean'],
        ]);

        // Safeguard: Cannot demote current logged-in admin
        if (auth()->id() === $user->id && $validated['role'] !== 'admin') {
            return back()->with('error', 'Security warning: You cannot change your own admin role.');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (filled($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Handle Vendor Profile synchronization
        if ($user->isVendor()) {
            $businessName = filled($request->business_name) ? trim($request->business_name) : ($user->name . ' Events');

            if ($user->vendorProfile) {
                $user->vendorProfile->update([
                    'business_name' => $businessName,
                    'city' => $request->city ?: $user->vendorProfile->city,
                    'phone' => $request->phone ?: $user->vendorProfile->phone,
                    'is_approved' => $request->boolean('is_approved', true),
                ]);
            } else {
                $baseSlug = Str::slug($businessName ?: 'vendor-' . $user->id);
                $slug = $baseSlug;
                $c = 1;
                while (VendorProfile::where('slug', $slug)->exists()) {
                    $c++;
                    $slug = "{$baseSlug}-{$c}";
                }

                VendorProfile::create([
                    'user_id' => $user->id,
                    'business_name' => $businessName,
                    'slug' => $slug,
                    'city' => $request->city ?: null,
                    'phone' => $request->phone ?: null,
                    'is_approved' => $request->boolean('is_approved', true),
                ]);
            }
        }

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
            ->with('success', "User '{$user->name}' details updated successfully.");
    }

    /**
     * Delete user with safeguards
     */
    public function destroy(User $user)
    {
        Gate::authorize('admin');

        // Safeguard: Cannot delete own logged-in admin account
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Action prohibited: You cannot delete your own logged-in admin account.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
            ->with('success', "User '{$userName}' has been permanently deleted.");
    }
}
