<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    public function terms(): View
    {
        return view('legal.terms');
    }

    public function privacy(): View
    {
        return view('legal.privacy');
    }

    public function cookies(): View
    {
        return view('legal.cookies');
    }

    public function cancellations(): View
    {
        return view('legal.cancellations');
    }

    public function professionals(): View
    {
        return view('legal.professionals');
    }

    public function contact(): View
    {
        return view('legal.contact');
    }
}
