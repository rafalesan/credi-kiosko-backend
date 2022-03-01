<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Auth;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index() {
        $user = Auth::user();
        $paginatedProducts = $user->business->products()->paginate();
        return response($paginatedProducts, 200);
    }

    public function getSingleProduct($id) {
        $product = $this->findOrFailProduct($id);
        return response($product, 200);
    }

    public function store(Request $request) {

        $this->validate($request,  [
            'name'  => 'required|string|max:100',
            'price' => 'required',
        ]);

        $user = Auth::user();

        $product = Product::create([
            'business_id' => $user->business->id,
            'name' => $request->name,
            'price' => $request->price,
        ]);

        return response($product, 200);

    }

    public function update(Request $request, $id) {
        $product = $this->findOrFailProduct($id);
        $product->update($request->all());
        return response($product, 200);
    }

    public function delete($id) {
        $product = $this->findOrFailProduct($id);
        $product->delete();
        return response()->json([
            'message' => trans('product-validation.product_deleted_successful')
        ]);
    }

    private function findOrFailProduct($id): Product {
        $user = Auth::user();
        $product = $user->business->products()->find($id);

        if(is_null($product)) {
            throw new HttpResponseException(response([
                'message' => trans('product-validation.product_not_found', ['attribute' => $id]),
            ], 404));
        }
        return $product;
    }

}
