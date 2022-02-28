<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

    public function update(Request $request, $id) {

        $product = $this->validateProduct($id);

        if($product instanceof Response) {
            return $product;
        }

        $product->update($request->all());

        return response($product, 200);
    }

    public function delete($id) {

        $product = $this->validateProduct($id);

        if($product instanceof Response) {
            return $product;
        }

        $product->delete();
        return response(null, 204);
    }

    private function validateProduct($id): Response|Product {
        $product = Product::find($id);
        if(is_null($product)) {
            return response([
                'message' => trans('product-validation.product_not_found', ['attribute' => $id]),
            ], 404);
        }
        return $product;
    }

}
