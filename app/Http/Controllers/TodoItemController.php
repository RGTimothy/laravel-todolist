<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\TodoItem;
use Illuminate\Support\Facades\File;

class TodoItemController extends BaseController
{
	public function list(Request $request, $id = null) {
		try {
			// validate user id from header
    		$userId = $this->getUserIdFromRequest($request);
    		if ($userId <= 0) {
    			return $this->errorResponse(['user_id' => [\Config::get('messages.UserNotFound')]]);
    		}

    		// validate request body
    		$rules = [
		        'status' => 'nullable|in:'. TodoItem::STATUS_COMPLETE . ',' . TodoItem::STATUS_INCOMPLETE,
		        'order_by' => 'nullable|string|in:id,due_date',
		        'order_state' => 'nullable|string|in:asc,desc'
		    ];
		    $validationErrors = $this->validateRequest($request, $rules);
		    if (count($validationErrors) > 0) {
                return $this->errorResponse($validationErrors);
            }

            $statusFilter = $request->get('status');
            $orderBy = 'todo_items';
            if (!is_null($request->get('order_by'))) {
            	$orderBy = $orderBy . '.' . $request->get('order_by');
            } else {
            	$orderBy = $orderBy . '.id';
            }
            $orderState = $request->get('order_state');

            $data = TodoItem::getList($userId, $id, $statusFilter, $orderBy, $orderState);

            $response = [
            	'list' => $data
            ];

			return $this->successResponse($response);
		} catch (\Exception $e) {
			return $this->exception($e);
		}
	}

    public function create(Request $request) {
    	try {
    		// validate user id from header
    		$userId = $this->getUserIdFromRequest($request);
    		if ($userId <= 0) {
    			return $this->errorResponse(['user_id' => [\Config::get('messages.UserNotFound')]]);
    		}

    		// validate request body
    		$rules = [
		        'title' => 'required|max:50',
		        'body' => 'required',
		        'due_date' => 'nullable|date',
		        'attachment' => 'required|file|max:2048',
		        'reminder_id' => 'required|exists:reminders,id,deleted_at,NULL'
		    ];
		    $validationErrors = $this->validateRequest($request, $rules);
		    if (count($validationErrors) > 0) {
                return $this->errorResponse($validationErrors);
            }

            \DB::beginTransaction();

            // upload attachment file
            $profileFile = null;
            if ($files = $request->file('attachment')) {
	           $destinationPath = 'uploads/'; // upload path
	           $profileFile = $userId . '-' . date('YmdHis') . '.' . $files->getClientOriginalExtension();
	           $files->move($destinationPath, $profileFile);
	       	}

	       	$insert = [
	       		'user_id' => $userId,
		    	'title' => $request->get('title'),
		    	'body' => $request->get('body'),
		    	'due_date' => $request->get('due_date'),
		    	'attachment' => $profileFile,
		    	'reminder_id' => $request->get('reminder_id')
	       	];

	       	$itemId = TodoItem::insertItem($insert);

	       	$responseStatus = 1;
	       	$responseMessage = \Config::get('messages.AddItemSuccessful');
	       	if ($itemId) {
	       		\DB::commit();
	       	} else {
	       		\DB::rollBack();

	       		// delete file from storage
	       		if (!is_null($profileFile)) {
	       			if (File::exists($destinationPath . $profileFile)) {
	       				File::delete(public_path() . '/' . $destinationPath . $profileFile);
	       			}
	       		}

	       		$responseStatus = 0;
	       		$responseMessage = \Config::get('messages.AddItemFailed');
	       	}

	       	$response = [
	       		'success' => $responseStatus,
	       		'message' => $responseMessage
	       	];

	       	return $this->successResponse($response);

    	} catch (\Exception $e) {
    		\DB::rollBack();
    		return $this->exception($e);
    	}
    	
    }
}
