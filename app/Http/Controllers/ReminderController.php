<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Reminder;

class ReminderController extends BaseController
{
    public function list(Request $request) {
    	try {
    		$reminders = Reminder::all();

    		$arrReminders = [];
    		foreach ($reminders as $item) {
    			array_push($arrReminders, [
    				'id' => $item->id,
    				'name' => $item->name,
    				'unix_value' => $item->unix_value
    			]);
    		}

    		$response = [
    			'list' => $arrReminders
    		];

    		return $this->successResponse($response);
    	} catch (\Exception $e) {
    		return $this->exception($e);
    	}
    }
}
