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
}
