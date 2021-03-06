<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\TodoItem;
use App\Reminder;
use App\Util;

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

            $newData = [];
            foreach ($data as $item) {
                $attachmentUrl = '';
                if (!is_null($item->attachment)) {
                    $attachmentUrl = env('APP_URL') . '/' . env('UPLOAD_FOLDER') . '/' . $item->attachment;
                }
                array_push($newData, [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'user_name' => $item->user_name,
                    'user_email' => $item->user_email,
                    'title' => $item->title,
                    'body' => $item->body,
                    'due_date' => $item->due_date,
                    'due_date_unix' => $item->due_date_unix,
                    'attachment' => $item->attachment,
                    'attachmentUrl' => $attachmentUrl,
                    'reminder_id' => $item->reminder_id,
                    'reminder_name' => $item->reminder_name,
                    'reminder_unix_value' => $item->reminder_unix_value,
                    'status' => $item->status,
                    'created_at' => (string) $item->created_at,
                ]);
            }

            $response = [
            	'list' => $newData
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

            // check & validate due date and reminder time
            if ($request->get('due_date') && $request->get('reminder_id')) {
            	$currentUnix = Util::getCurrentUnixTimestamp();
            	$reminder = Reminder::find($request->get('reminder_id'));

            	$dueDateUnix = strtotime($request->get('due_date'));
            	$reminderUnix = $dueDateUnix - $reminder->unix_value;

            	if ($currentUnix > $reminderUnix) {
            		$error = [ 'reminder_id' => \Config::get('messages.ReminderTimeHasAlreadyPassed') ];
            		return $this->errorResponse($error);
            	}
            }

            \DB::beginTransaction();

            // upload attachment file
            $profileFile = null;
            if ($file = $request->file('attachment')) {
	           $destinationPath = env('UPLOAD_FOLDER', 'uploads') . '/'; // upload path
	           $profileFile = $userId . '-' . date('YmdHis') . '.' . $file->getClientOriginalExtension();

	           Util::uploadFile($file, $destinationPath, $profileFile);
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
	       		
	       		if (!is_null($profileFile)) {
	       			// delete file from storage
		       		$fullPathFile = public_path() . '/' . $destinationPath . $profileFile;
		       		Util::deleteFile($fullPathFile);
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

    public function update(Request $request, $id, $isMark = false) {
    	try {
    		// validate user id from header
    		$userId = $this->getUserIdFromRequest($request);
    		if ($userId <= 0) {
    			return $this->errorResponse(['user_id' => [\Config::get('messages.UserNotFound')]]);
    		}

    		// validate request body
    		$request['id'] = $id;
    		$rules = [
    			'id' => 'exists:todo_items,id,user_id,' . $userId . ',deleted_at,NULL'
    		];
    		if ($isMark) {
    			$rules['status'] = 'required|in:'. TodoItem::STATUS_COMPLETE . ',' . TodoItem::STATUS_INCOMPLETE;
    		} else {
    			$rules['title'] = 'required|max:50';
    			$rules['body'] = 'required';
    			$rules['due_date'] = 'nullable|date';
    			$rules['attachment'] = 'required|file|max:2048';
    			$rules['reminder_id'] = 'required|exists:reminders,id,deleted_at,NULL';
    		}
		    $validationErrors = $this->validateRequest($request, $rules);
		    if (count($validationErrors) > 0) {
                return $this->errorResponse($validationErrors);
            }

            // check & validate due date and reminder time
            if ($request->get('due_date') && $request->get('reminder_id')) {
            	$currentUnix = Util::getCurrentUnixTimestamp();
            	$reminder = Reminder::find($request->get('reminder_id'));

            	$dueDateUnix = strtotime($request->get('due_date'));
            	$reminderUnix = $dueDateUnix - $reminder->unix_value;

            	if ($currentUnix > $reminderUnix) {
            		$error = [ 'reminder_id' => \Config::get('messages.ReminderTimeHasAlreadyPassed') ];
            		return $this->errorResponse($error);
            	}
            }

            // update data based on request
            $profileFile = null;
            $data = [];
            if ($isMark) {
            	$data['status'] = $request->get('status');
            } else {
            	// upload attachment file
	            if ($file = $request->file('attachment')) {
		           $destinationPath = env('UPLOAD_FOLDER', 'uploads') . '/'; // upload path
		           $profileFile = $userId . '-' . date('YmdHis') . '.' . $file->getClientOriginalExtension();

		           Util::uploadFile($file, $destinationPath, $profileFile);
		       	}

            	$data['title'] = $request->get('title');
				$data['body'] = $request->get('body');
				$data['due_date'] = $request->get('due_date');
				$data['attachment'] = $profileFile;
				$data['reminder_id'] = $request->get('reminder_id');
            }

            \DB::beginTransaction();
            
            $itemBeforeUpdate = TodoItem::find($id);
            $updateItem = TodoItem::updateItem($id, $data);

            $responseStatus = 1;
            $responseMessage = \Config::get('messages.UpdateItemSuccessful');
            if ($updateItem) {
            	\DB::commit();

            	// if updated, delete old attachment from storage if exists
            	if (!is_null($profileFile)) {
            		if (!is_null($itemBeforeUpdate->attachment)) {
            			$fullPathOldFile = public_path() . '/' . $destinationPath . $itemBeforeUpdate->attachment;
            			Util::deleteFile($fullPathOldFile);
            		}
	       		}
            } else {
            	\DB::rollBack();

            	// if fail to update, delete new uploaded file
            	if (!is_null($profileFile)) {
	       			// delete file from storage
		       		$fullPathFile = public_path() . '/' . $destinationPath . $profileFile;
		       		Util::deleteFile($fullPathFile);
	       		}

            	$responseStatus = 0;
            	$responseMessage = \Config::get('messages.UpdateItemFailed');
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

    public function delete(Request $request, $id) {
    	try {
    		// validate user id from header
    		$userId = $this->getUserIdFromRequest($request);
    		if ($userId <= 0) {
    			return $this->errorResponse(['user_id' => [\Config::get('messages.UserNotFound')]]);
    		}

    		// validate item id
    		$request['id'] = $id;
    		$rules = [
    			'id' => 'exists:todo_items,id,user_id,' . $userId . ',deleted_at,NULL'
		    ];
		    $validationErrors = $this->validateRequest($request, $rules);
		    if (count($validationErrors) > 0) {
                return $this->errorResponse($validationErrors);
            }

            \DB::beginTransaction();

            $itemBeforeDelete = TodoItem::find($id);
            $deleteItem = TodoItem::deleteItem($id);

            $responseStatus = 1;
            $responseMessage = \Config::get('messages.DeleteItemSuccessful');
            if ($deleteItem) {
            	\DB::commit();

            	// if data deleted, then delete the uploaded file
            	if (!is_null($itemBeforeDelete->attachment)) {
            		$destinationPath = env('UPLOAD_FOLDER', 'uploads') . '/'; // upload path
            		$fullPathOldFile = public_path() . '/' . $destinationPath . $itemBeforeDelete->attachment;
            		Util::deleteFile($fullPathOldFile);
	       		}
            } else {
            	\DB::rollBack();

            	$responseStatus = 0;
            	$responseMessage = \Config::get('messages.DeleteItemFailed');
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

    public function mark(Request $request, $id) {
    	$isMark = true;
    	return $this->update($request, $id, $isMark);
    }
}
