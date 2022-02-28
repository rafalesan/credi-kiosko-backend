<?php
namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

trait RequestValidator {

    public function validateRequest(Request $request, array $rules): Response|null
    {
        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()) {
            return response([
                'message' => trans('validation.request_error'),
                'errors'  => $validator->errors()->all()
            ], 422);
        }
        return null;
    }

}
