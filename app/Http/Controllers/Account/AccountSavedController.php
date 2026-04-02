<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;

class AccountSavedController extends Controller
{
    public function index()
    {
        $events = auth()->user()->savedEvents()->with(['media', 'vendorProfile', 'ticketTypes'])->paginate(12);
        $events->getCollection()->transform(function ($event) {
            $event->is_saved = true;
            return $event;
        });
        return view('account.saved', compact('events'));
    }
}
