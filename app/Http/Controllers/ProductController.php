<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Auth;
use Illuminate\Http\Exceptions\HttpResponseException;
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

        if($product instanceof Response) {
            return $product;
        }

        $product->update($request->all());

        return response($product, 200);
    }

    public function delete($id) {

        $product = $this->findOrFailProduct($id);

        if($product instanceof Response) {
            return $product;
        }

        $product->delete();
        return response(null, 204);
    }

    private function findOrFailProduct($id): Response|Product {
        $product = Product::find($id);
        if(is_null($product)) {
            throw new HttpResponseException(response([
                'message' => trans('product-validation.product_not_found', ['attribute' => $id]),
            ], 404));
        }
        return $product;
    }

}
