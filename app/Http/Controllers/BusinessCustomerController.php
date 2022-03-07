<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class BusinessCustomerController extends Controller
{

    public function index(Request $request) {
        $user = Auth::user();
        if($request->includeDeleted()) {
            $paginatedCustomers = $user->business->customersWithPivotWithTrashed()
                                                 ->simplePaginate();
        } else {
            $paginatedCustomers = $user->business->customersWithPivot()
                                                 ->simplePaginate();
        }
        return response($paginatedCustomers, 200);
    }

    public function show(Request $request, $id) {
        $customer = $this->findOfFailCustomer($id, $request->includeDeleted());
        return response($customer, 200);
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:100',
            'nickname' => 'string|max:30',
            'email' => 'string|max:150'
        ]);

        $user = Auth::user();

        $customer = Customer::create([
            'name' => null,
            'nickname' => null,
            'email' => $request->email,
        ]);

        $user->business->customersWithPivot()->attach($customer->id,
                                             ['business_customer_name' => $request->name,
                                              'business_customer_nickname' => $request->nickname,
                                              'created_at' => Carbon::now(),
                                              'updated_at' => Carbon::now()],
                                       true);

        $customerWithPivot = $this->findOfFailCustomer($customer->id);

        return response($customerWithPivot, 200);
    }

    public function update(Request $request, $id) {
        $customer = $this->findOfFailCustomer($id);
        $user = Auth::user();
        $user->business->customersWithPivot()->updateExistingPivot($customer->id, ['business_customer_name' => $request->name ?? $customer->pivot->business_customer_name,
                                                                          'business_customer_nickname' => $request->nickname,
                                                                          'updated_at' => Carbon::now()]);
        $customer = $this->findOfFailCustomer($id);
        return response($customer, 200);
    }

    public function delete($id) {
        $customer = $this->findOfFailCustomer($id);

        $user = Auth::user();
        $user->business->customersWithPivot()->updateExistingPivot($customer->id, ['deleted_at' => Carbon::now()]);
        return response()->json([
            'message' => trans('customer.customer_deleted_successful')
        ]);
    }

    public function restore($id) {
        $user = Auth::user();
        $customer = $user->business->customersWithPivotWithTrashed()->find($id);
        if(is_null($customer)) {
            throw new HttpResponseException(response([
                'message' => trans('customer.customer_to_restore_not_found', ['attribute' => $id]),
            ], 404));
        }
        $user->business->customersWithPivot()->updateExistingPivot($customer->id, ['deleted_at' => null]);
        $customer = $this->findOfFailCustomer($id);
        return response()->json([
            'message' => trans('customer.customer_restored_successful'),
            'data' => $customer,
        ]);
    }

    private function findOfFailCustomer($id, $includeDeleted = false) : Customer {
        $user = Auth::user();
        if($includeDeleted) {
            $customer = $user->business->customersWithPivotWithTrashed()->find($id);
        } else {
            $customer = $user->business->customersWithPivot()
                                       ->find($id);
        }
        if(is_null($customer)) {
            throw new HttpResponseException(response([
                'message' => trans('customer.customer_not_found', ['attribute' => $id]),
            ], 404));
        }
        return $customer;
    }

}
