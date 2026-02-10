<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;

class AccountSavedController extends Controller
{
    public function index()
    {
        $events = auth()->user()->savedEvents()->with('media')->paginate(12);
        return view('account.saved', compact('events'));
    }
}
