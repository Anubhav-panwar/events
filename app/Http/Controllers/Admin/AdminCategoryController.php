<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminCategoryController extends Controller
{
    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:categories,slug'],
        ]);

        $name = trim($validated['name']);
        $baseSlug = filled($validated['slug'] ?? null) ? Str::slug($validated['slug']) : Str::slug($name);
        $slug = $baseSlug ?: 'category';

        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $counter++;
            $slug = "{$baseSlug}-{$counter}";
        }

        $category = Category::create([
            'name' => $name,
            'slug' => $slug,
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'categories'])
            ->with('success', "Category '{$category->name}' created successfully.");
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'slug' => ['required', 'string', 'max:100', Rule::unique('categories', 'slug')->ignore($category->id)],
        ]);

        $category->update([
            'name' => trim($validated['name']),
            'slug' => Str::slug($validated['slug']),
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'categories'])
            ->with('success', "Category '{$category->name}' updated successfully.");
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        Gate::authorize('admin');

        $categoryName = $category->name;

        // Disassociate events before deletion so events are not orphaned
        Event::where('category_id', $category->id)->update(['category_id' => null]);
        $category->vendorProfiles()->detach();

        $category->delete();

        return redirect()->route('admin.dashboard', ['tab' => 'categories'])
            ->with('success', "Category '{$categoryName}' deleted successfully.");
    }
}
