<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\Customer;
use App\Models\Cut;
use App\Models\Payment;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class CutOffController extends Controller
{

    public function index() {
        $user = Auth::user();
        $cutOffs = Cut::select('cuts.*')
                      ->join('customers', 'cuts.customer_id', '=', 'customers.id')
                      ->join('business_customer', 'customers.id', '=', 'business_customer.customer_id')
                      ->join('businesses', 'business_customer.business_id', '=', 'businesses.id')
                      ->where('businesses.id', $user->business_id)
                      ->simplePaginate();
        return response($cutOffs);
    }

    public function store(Request $request) {
        $request->validate([
            'cut_off_date' => 'required|string'
        ]);

        $user = Auth::user();

        $customers = $user->business->customers;

        $cutOffs = [];

        foreach ($customers as $customer) {
            $cutOff = $this->applyCutOffToCustomer($customer, $user, $request);
            $cutOffs[] = $cutOff;
        }

        $result = array_filter($cutOffs, function ($cutoff) {
           return $cutoff !== NULL;
        });

        return response($result);

    }

    public function storeCustomerCutOff(Request $request, $customerId) {

        $request->validate([
            'cut_off_date' => 'required|string'
        ]);

        $user = Auth::user();

        $customer = $this->findOrFailCustomer($customerId);

        $cutOff = $this->applyCutOffToCustomer($customer, $user, $request);

        return response($cutOff);

    }

    private function applyCutOffToCustomer($customer, $user, $request) : ?Cut {

        $lastCutOff = $customer->cutoffs()->latest()->first();

        $lastCutOffDate = $lastCutOff->cut_off_date ?? Carbon::createFromTimestamp(0);

        $credits = Credit::where('customer_id', $customer->id)
            ->whereNull('cut_id')
            ->whereBetween('date', [$lastCutOffDate,
                $request->cut_off_date]);

        if($credits->get()->count() == 0) {
            return null;
        }

        $payments = Payment::where('customer_id', $customer->id)
            ->whereNull('cut_id')
            ->whereBetween('date', [$lastCutOffDate,
                $request->cut_off_date]);

        $totalCredits = $credits->get()->sum('total');
        $totalPayments = $payments->get()->sum('amount');
        $balance = $totalCredits - $totalPayments;

        $surplusPaymentId = null;

        if($balance < 0) {
            $surplusPayment = Payment::create([
                'business_id' => $user->business_id,
                'customer_id' => $customer->id,
                'is_surplus' => true,
                'date' => Carbon::now(),
                'balance_before_payment' => "0",
                'balance_after_payment' => "0",
                'amount' => abs($balance),
            ]);
            $balance = 0;
            $surplusPaymentId = $surplusPayment->id;
        }

        $cutOff = Cut::create([
            'customer_id' => $customer->id,
            'cut_off_date' => $request->cut_off_date,
            'total_credits' => $totalCredits,
            'surplus_payment_id' => $surplusPaymentId,
            'total_payments' => $totalPayments,
            'total_arrears' => "0",
            'balance' => $balance
        ]);

        $credits->update(['cut_id' => $cutOff->id]);
        $payments->update(['cut_id' => $cutOff->id]);

        return $cutOff;

    }

    private function findOrFailCustomer($id): Customer {
        $user = Auth::user();
        $customer = $user->business->customers()->find($id);

        if(is_null($customer)) {
            throw new HttpResponseException(response([
                'message' => trans('customer.customer_not_found', ['attribute' => $id]),
            ], 404));
        }
        return $customer;
    }

}
