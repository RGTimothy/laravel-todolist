<?php

namespace App\Http\Controllers;

use App\User;
use App\Util;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    protected function validateRequest(&$request, &$rules) {
        $validator = \Validator::make($request->all(), $rules);
        $errors = array();
        if ($validator->fails()) {
            $validatorMessages = $validator->messages()->getMessages();
            $errors = $validatorMessages;
        }
        return $errors;
    }

    protected function errorResponse($errors = []) {
    	return $this->response([], $errors);
    }

    protected function successResponse($response = [], $errors = []){
        return $this->response($response, $errors);
    }

    protected function exception($e) {
    	$exceptionArr = [
    		'message' => $e->getMessage(),
    		'code' => $e->getCode()
    	];
    	
    	return $this->response([], [], $exceptionArr);
    }

    /**
     * Returns user ID which the request's access token belongs to.
     *
     * @param Request $request
     * @return int
     */
    protected function getUserIdFromRequest(Request $request) {
    	if (is_null($request->header('Authorization'))) {
    		return 0;
    	}

    	$accessToken = base64_decode($request->header('Authorization'));
    	$userId = User::where(['access_token' => $accessToken])->first();

    	return is_null($userId) ? 0 : $userId->id;
    }

    private function response($response = [], $errors = [], $exception = []) {
    	return Util::response($response, $errors, $exception);
    }
}
