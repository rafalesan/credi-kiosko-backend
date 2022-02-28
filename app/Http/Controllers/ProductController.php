<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Auth;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index() {
        $user = Auth::user();
        $paginatedProducts = $user->business->products()->paginate();
        return response($paginatedProducts, 200);
    }

    public function store(Request $request) {

        $validationsResponse = $this->validateRequest($request, [
            'name'  => 'required|string|max:100',
            'price' => 'required',
        ]);

        if(!is_null($validationsResponse)) {
            return $validationsResponse;
        }

        $user = Auth::user();

        $product = Product::create([
            'business_id' => $user->business->id,
            'name' => $request->name,
            'price' => $request->price,
        ]);

        return response($product, 200);

    }

}
