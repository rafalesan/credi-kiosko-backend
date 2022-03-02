<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Auth;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index(Request $request) {
        $user = Auth::user();
        if($request->includeDeleted()) {
            $paginatedProducts = $user->business->products()->withTrashed()->paginate();
        } else {
            $paginatedProducts = $user->business->products()->paginate();
        }
        return response($paginatedProducts, 200);
    }

    public function getSingleProduct(Request $request, $id) {
        $product = $this->findOrFailProduct($id, $request->includeDeleted());
        return response($product, 200);
    }

    public function store(Request $request) {

        $request->validate([
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

    public function restore($id) {
        $user = Auth::user();
        $product = $user->business->products()->onlyTrashed()->find($id);
        if(is_null($product)) {
            throw new HttpResponseException(response([
                'message' => trans('product-validation.product_to_restore_not_found', ['attribute' => $id]),
            ], 404));
        }
        if($product->restore()) {
            return response()->json([
                'message' => trans('product-validation.product_restored_successful'),
                'data' => $product,
            ]);
        }
        throw new HttpResponseException(response([
            'message' => trans('product-validation.product_could_not_be_restored', ['attribute' => $id]),
        ], 500));
    }

    private function findOrFailProduct($id, $includeDeleted = false): Product {
        $user = Auth::user();
        if($includeDeleted){
            $product = $user->business->products()->withTrashed()->find($id);
        } else {
            $product = $user->business->products()->find($id);
        }

        if(is_null($product)) {
            throw new HttpResponseException(response([
                'message' => trans('product-validation.product_not_found', ['attribute' => $id]),
            ], 404));
        }
        return $product;
    }

}
