<?php

namespace App\Http\Controllers;

use App\Models\Cut;
use App\Models\Payment;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    public function index(Request $request) {
        $user = Auth::user();
        if($request->includeDeleted()) {
            $paginatedPayments = $user->business->payments()->withTrashed()->simplePaginate();
        } else {
            $paginatedPayments = $user->business->payments()->simplePaginate();
        }
        return response($paginatedPayments, 200);
    }

    public function show(Request $request, $id) {
        $payment = $this->findOrFailPayment($id, $request->includeDeleted());
        return response($payment, 200);
    }

    public function store(Request $request) {
        $request->validate([
            'customer_id' => 'required|numeric',
            'cut_id' => 'numeric',
            'date' => 'required|string',
            'amount' => 'required|numeric|gt:0'
        ]);

        $user = Auth::user();

        $balanceBeforePayment = "0";
        $balanceAfterPayment = "0";

        if(!is_null($request->cut_id)) {

            $cutOff = Cut::find($request->cut_id);

            if($cutOff->balance <= 0) {
                throw new HttpResponseException(response([
                    'message' => trans('payment.could_not_apply_payment_to_a_cut_off_already_paid'),
                ], 422));
            }

            $lastPayment = $user->business->payments()
                                          ->where('cut_id', $request->cut_id)
                                          ->latest()
                                          ->first();
            $balanceBeforePayment = $lastPayment->balance_after_payment;
            $balanceAfterPayment = $balanceBeforePayment - $request->amount;
            $cutOffBalance = $balanceAfterPayment;

            if($cutOffBalance < 0) {
                $surplusPayment = Payment::create([
                    'business_id' => $user->business_id,
                    'customer_id' => $request->customer_id,
                    'is_surplus' => true,
                    'date' => Carbon::now(),
                    'balance_before_payment' => "0",
                    'balance_after_payment' => "0",
                    'amount' => abs($cutOffBalance),
                ]);
                $cutOffBalance = 0;
                $cutOff->surplus_payment_id = $surplusPayment->id;
            }

            $cutOff->balance = $cutOffBalance;
            $cutOff->update();
        }

        $payment = Payment::create([
            'business_id' => $user->business_id,
            'customer_id' => $request->customer_id,
            'cut_id' => $request->cut_id,
            'date' => $request->date,
            'balance_before_payment' => $balanceBeforePayment,
            'balance_after_payment' => $balanceAfterPayment,
            'amount' => $request->amount,
        ]);

        return response($payment, 200);
    }

    public function update(Request $request, $id) {
        $payment = $this->findOrFailPayment($id);

        if(!is_null($payment->cut_id)) {
            throw new HttpResponseException(response([
                'message' => trans('payment.can_not_update_payment_with_cut_off'),
            ], 422));
        }

        $payment->update($request->all());
        return response($payment, 200);
    }

    public function delete($id) {
        $payment = $this->findOrFailPayment($id);

        if(!is_null($payment->cut_id)) {
            throw new HttpResponseException(response([
                'message' => trans('payment.can_not_remove_payment_with_cut_off'),
            ], 422));
        }

        $payment->forceDelete();
        return response()->json([
            'message' => trans('payment.payment_deleted_successful')
        ]);
    }

    private function findOrFailPayment($id, $includeDeleted = false) {
        $user = Auth::user();
        if($includeDeleted) {
            $payment = $user->business->payments()->withTrashed()->find($id);
        } else {
            $payment = $user->business->payments()->find($id);
        }

        if(is_null($payment)) {
            throw new HttpResponseException(response([
                'message' => trans('payment.payment_not_found', ['attribute' => $id]),
            ], 404));
        }
        return $payment;
    }

}
