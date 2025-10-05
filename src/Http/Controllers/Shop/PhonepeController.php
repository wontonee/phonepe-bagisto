<?php

namespace Wontonee\Phonepe\Http\Controllers\Shop;

use Illuminate\View\View;
use Webkul\Shop\Http\Controllers\Controller;

class PhonepeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('Phonepe::shop.index');
    }
}
