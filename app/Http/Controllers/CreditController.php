<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class CreditController extends Controller
{

    public function index() {
        $user = Auth::user();

        $credits = $user->business->credits()->with('creditProducts')->simplePaginate();

        return response($credits, 200);
    }

    public function show($id) {
        $user = Auth::user();

        $credit = $user->business->credits()->with('creditProducts')->find($id);

        if(is_null($credit)) {
            throw new HttpResponseException(response([
                'message' => trans('credit.credit_not_found', ['attribute' => $id]),
            ], 404));
        }

        return response($credit, 200);

    }

    public function store(Request $request) {
        $request->validate([
            'customer_id' => 'numeric|required',
            'date' => 'string|required',
            'total' => 'string|required',
            'products' => 'array|required|min:1',
            'products.*.product_id' => 'numeric|required',
            'products.*.product_name' => 'string|required',
            'products.*.price' => 'string|required',
            'products.*.quantity' => 'string|required',
            'products.*.total' => "string|required"
        ]);

        $user = Auth::user();

        $creditRequest = $request;

        $credit = Credit::create([
            'business_id' => $user->business_id,
            'user_id' => $user->id,
            'customer_id' => $creditRequest->customer_id,
            'date' => $creditRequest->date,
            'total' => $creditRequest->total,
        ]);

        foreach($creditRequest->products as $product) {
            $credit->products()->attach($product['product_id'],
                                        ['product_name' => $product['product_name'],
                                         'price' => $product['price'],
                                         'quantity' => $product['quantity'],
                                         'total' => $product['total'],
                                         'created_at' => Carbon::now(),
                                         'updated_at' => Carbon::now()]);
        }

        $creditSaved = Credit::with('products')->find($credit->id);

        return response($creditSaved, 200);

    }

}
