<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductPhotoController extends Controller
{
    public function index()
    {
        return Machine::with('products.photos')->get();
    }
}
