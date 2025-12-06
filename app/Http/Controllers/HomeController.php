<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function publicQueue()
    {
        $services = \App\Models\Service::where('is_active', true)->get();
        return view('public.queue', compact('services'));
    }
}