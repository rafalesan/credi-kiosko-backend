<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class CreditController extends Controller
{

    public function index(Request $request) {
        $user = Auth::user();

        if($request->includeDeleted()) {
            $credits = $user->business->credits()
                                      ->withTrashed()
                                      ->with('creditProducts')
                                      ->simplePaginate();
        } else {
            $credits = $user->business->credits()
                                      ->with('creditProducts')
                                      ->simplePaginate();
        }

        return response($credits, 200);
    }

    public function show(Request $request, $id) {
        $credit = $this->findOrFailCredit($id, $request->includeDeleted());
        return response($credit, 200);
    }

    public function store(Request $request) {

        $this->validateCreditRequest($request);

        $user = Auth::user();

        $creditRequest = $request;

        $credit = Credit::create([
            'business_id' => $user->business_id,
            'user_id' => $user->id,
            'customer_id' => $creditRequest->customer_id,
            'date' => $creditRequest->date,
            'total' => $creditRequest->total,
        ]);

        foreach($creditRequest->credit_products as $product) {
            $credit->products()->attach($product['product_id'],
                                        ['product_name' => $product['product_name'],
                                         'price' => $product['price'],
                                         'quantity' => $product['quantity'],
                                         'total' => $product['total'],
                                         'created_at' => Carbon::now(),
                                         'updated_at' => Carbon::now()]);
        }

        $creditSaved = Credit::with('creditProducts')->find($credit->id);

        return response($creditSaved, 200);

    }

    public function update(Request $request, $id) {
        $this->validateCreditRequest($request);

        $credit = $this->findOrFailCredit($id);
        $credit->update($request->all(['customer_id',
                                       'date',
                                       'total']));

        $credit->products()->detach();

        foreach ($request->credit_products as $product) {
            $credit->products()->attach($product['product_id'], ['product_name' => $product['product_name'],
                                                                 'price' => $product['price'],
                                                                 'quantity' => $product['quantity'],
                                                                 'total' => $product['total'],
                                                                 'created_at' => Carbon::now(),
                                                                 'updated_at' => Carbon::now()]);
        }

        $creditUpdated = $this->findOrFailCredit($credit->id);

        return response($creditUpdated, 200);

    }

    public function delete($id) {
        $credit = $this->findOrFailCredit($id);
        $credit->delete();
        return response()->json([
            'message' => trans('credit.credit_deleted_successful')
        ]);
    }

    public function restore($id) {
        $user = Auth::user();
        $credit = $user->business->credits()->with('creditProducts')->onlyTrashed()->find($id);
        if(is_null($credit)) {
            throw new HttpResponseException(response([
                'message' => trans('credit.credit_to_restore_not_found', ['attribute' => $id]),
            ], 404));
        }
        if($credit->restore()) {
            return response()->json([
                'message' => trans('credit.credit_restored_successful'),
                'data' => $credit,
            ]);
        }
        throw new HttpResponseException(response([
            'message' => trans('credit.credit_could_not_be_restored', ['attribute' => $id]),
        ], 500));
    }

    private function validateCreditRequest(Request $creditRequest) {
        $creditRequest->validate([
            'customer_id' => 'numeric|required',
            'date' => 'string|required',
            'total' => 'string|required',
            'credit_products' => 'array|required|min:1',
            'credit_products.*.product_id' => 'numeric|required',
            'credit_products.*.product_name' => 'string|required',
            'credit_products.*.price' => 'string|required',
            'credit_products.*.quantity' => 'string|required',
            'credit_products.*.total' => "string|required"
        ]);
    }

    private function findOrFailCredit($id, $includeDeleted = false) : Credit {
        $user = Auth::user();
        if($includeDeleted){
            $credit = $user->business->credits()->withTrashed()->with('creditProducts')->find($id);
        } else {
            $credit = $user->business->credits()->with('creditProducts')->find($id);
        }
        if(is_null($credit)) {
            throw new HttpResponseException(response([
                'message' => trans('credit.credit_not_found', ['attribute' => $id]),
            ], 404));
        }
        return $credit;
    }

}
