<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PasswordController extends Controller
{
    public function index(): View
    {
        return view('passwords.index');
    }
}
