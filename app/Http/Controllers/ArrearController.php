<?php

namespace App\Http\Controllers;

use App\Models\Arrear;
use App\Models\Cut;
use Auth;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class ArrearController extends Controller
{

    public function index($cutOffId) {
        $cutOff = $this->findOrFailCutOff($cutOffId);
        return response($cutOff->arrears, 200);
    }

    public function store(Request $request, $cutOffId) {

        $request->validate([
            'date' => 'date|required',
            'percentage' => 'numeric|nullable',
            'amount' => 'numeric|required'
        ]);

        $cutOff = $this->findOrFailCutOff($cutOffId);

        if($cutOff->balance <= 0) {
            throw new HttpResponseException(response([
                'message' => trans('arrear.could_not_apply_an_arrear_to_a_cut_off_already_paid'),
            ], 422));
        }

        if(!is_null($request->percentage)) {
            $amountByPercentage = $cutOff->balance * ($request->percentage / 100);
            if($amountByPercentage != $request->amount) {
                throw new HttpResponseException(response([
                    'message' => trans('arrear.the_amount_by_percentage_provided_were_but_it_must_be', ['amount_provided' => $request->amount,
                                                                                                             'right_amount' => $amountByPercentage]),
                ], 422));
            }
        }

        $balance_before_arrear = (float) $cutOff->balance;
        $balance_after_arrear = $balance_before_arrear + $request->amount;

        $cutOff->balance = $balance_after_arrear;
        $cutOff->total_arrears += $request->amount;
        $cutOff->update();

        $arrear = Arrear::create([
            'cut_id' => $cutOff->id,
            'date' => $request->date,
            'balance_before_arrear' => $balance_before_arrear,
            'balance_after_arrear' => $balance_after_arrear,
            'percentage' => $request->percentage,
            'amount' => $request->amount,
        ]);

        return response($arrear, 200);

    }

    public function findOrFailCutOff($id): Cut {
        $user = Auth::user();
        $cutOff = Cut::select('cuts.*')
                     ->join('customers', 'cuts.customer_id', '=', 'customers.id')
                     ->join('business_customer', 'customers.id', '=', 'business_customer.customer_id')
                     ->join('businesses', 'business_customer.business_id', '=', 'businesses.id')
                     ->where('businesses.id', $user->business_id)
                     ->where("cuts.id", $id)
                     ->get()
                     ->first();

        if(is_null($cutOff)) {
            throw new HttpResponseException(response([
                'message' => trans('arrear.your_business_does_not_have_a_cut_off_with_id', ['attribute' => $id]),
            ], 404));
        }

        return $cutOff;

    }

}
