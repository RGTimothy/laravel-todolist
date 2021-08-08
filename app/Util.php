<?php

namespace App;

use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;

class Util extends Model
{
    public static function response($response = [], $errors = [], $exceptions = []) {
    	// check if response is array
    	if (!is_array($response)) {
    		$response = new \stdClass();
    		$exceptions = [
    			'message' => \Config::get('messages.InvalidResponseFormat'),
    			'code' => 0
    		];
    	} else {
    		if (count($response) <= 0) {
    			$response = new \stdClass();
    		}
    	}

    	$errorsArr = [];
    	if (count($errors) > 0) {
    		foreach ($errors as $key => $value) {
    			$message = $value;
    			if (is_array($value)) {
    				$message = $value[0];
    			}
    			array_push($errorsArr, [
    				'attribute' => $key,
    				'message' => $message
    			]);
    		}
    	}

    	$exceptionArr = [];
    	if (count($exceptions) > 0) {
    		array_push($exceptionArr, $exceptions);
    	}

    	$data = [
    		'data' => $response,
    		'errors' => $errorsArr,
    		'exception' => $exceptionArr
    	];

    	return response()->json($data);
    }

    public static function uploadFile($file, $destinationPath, $filename) {
    	$file->move($destinationPath, $filename);

    	return true;
    }

    public static function deleteFile($fullPath) {
    	if (File::exists($fullPath)) {
    		File::delete($fullPath);
    	}

    	return true;
    }
}
