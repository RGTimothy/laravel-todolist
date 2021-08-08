<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TodoItem extends Model
{
	const STATUS_COMPLETE = 'COMPLETE',
		  STATUS_INCOMPLETE = 'INCOMPLETE';

    use SoftDeletes;

    protected $softDelete = true;
    protected $fillable = [
    	'user_id',
    	'title',
    	'body',
    	'due_date',
    	'attachment',
    	'reminder_id',
    	'status'
    ];

    private static function defaultQuery($withTrashed = 0) {
    	if ($withTrashed == 1) {
    		$query = self::withTrashed();
    	} else {
    		$query = self::whereNull('todo_items.deleted_at');
    	}
    	return $query;
    }

    public static function getList(
    	$userId = null, 
    	$id = null, 
    	$status = null, 
    	$orderBy = 'todo_items.id', 
    	$orderState = 'ASC'
    ) {
    	$query = self::defaultQuery()
    				->leftJoin('users', 'users.id', '=', 'todo_items.user_id')
    				->leftJoin('reminders', 'reminders.id', '=', 'todo_items.reminder_id');

    	if (!is_null($userId)) {
    		$query->where('todo_items.user_id', $userId);
    	}

    	if (!is_null($id)) {
    		$query->where('todo_items.id', $id);
    	}

    	if (!is_null($status)) {
    		$query->where('todo_items.status', $status);
    	}

    	if (is_null($orderBy)) {
    		$orderBy = 'todo_items.id';
    	}

    	if (is_null($orderState)) {
    		$orderState = 'ASC';
    	}

    	$query->select([
    		'todo_items.id',
    		'todo_items.user_id',
    		'users.name AS user_name',
    		'users.email AS user_email',
	    	'todo_items.title',
	    	'todo_items.body',
	    	'todo_items.due_date',
	    	'todo_items.attachment',
	    	'todo_items.reminder_id',
	    	'todo_items.status',
	    	'todo_items.created_at'
    	]);

    	$query->orderBy($orderBy, $orderState);

    	return $query->get();
    }

    public static function insertItem($item = []) {
    	$data = new TodoItem;
    	$data->user_id = $item['user_id'];
    	$data->title = $item['title'];
    	$data->body = $item['body'];
    	$data->due_date = $item['due_date'];
    	$data->attachment = $item['attachment'];
    	$data->reminder_id = $item['reminder_id'];
    	$data->status = self::STATUS_INCOMPLETE;
    	$data->save();

    	return $data->id;
    }

    public static function updateItem($id, $data = []) {
    	$item = TodoItem::find($id);

    	if (is_null($item)) {
    		return false;
    	}

    	$item->title = $data['title'];
    	$item->body = $data['body'];
    	$item->due_date = $data['due_date'];
    	$item->attachment = $data['attachment'];
    	$item->reminder_id = $data['reminder_id'];

    	return $item->save();
    } 
}
