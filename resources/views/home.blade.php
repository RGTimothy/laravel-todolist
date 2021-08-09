@extends('layouts.app')

@section('content')
<div class="container">
    <!-- The Modal -->
    <div id="editModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close">&times;</span>
            <input id="updatingId" type="hidden">
            <form style="margin: 0; padding: 0;" id="editItemForm" method="post" enctype="multipart/form-data">
                <div class="container">
                    <label for="title" style="width: 20%"><b>Title</b></label>
                    <input style="display: inline; width: 60%;" type="text" placeholder="Enter Title" name="title" id="edit_title" required>
                    <br>
                    <label for="body" style="width: 20%"><b>Description</b></label>
                    <input  style="display: inline; width: 60%;" type="text" placeholder="Enter Description" name="body" id="edit_body" required>
                    <br>
                    <label for="due_date" style="width: 20%"><b>Due Date</b></label>
                    <input  style="display: inline; width: 60%;" type="date" placeholder="Enter Due Date" name="due_date" id="edit_due_date" min="<?php echo date('Y-m-d'); ?>">
                    <br>
                    <label for="attachment" style="width: 20%"><b>Attachment</b></label>
                    <input  style="display: inline; width: 60%;" type="file" placeholder="Attachment" name="attachment" id="edit_attachment" required>
                    <br>
                    <label for="reminder_id" style="width: 20%"><b>Reminder</b></label>
                    <select id="edit_reminder_id" name="reminder_id">
                        @foreach($reminders as $reminder)
                        <option value="{{$reminder->id}}">{{$reminder->name}}</option>
                        @endforeach
                    </select>
                    <hr>
                    <button type="submit">Update</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Hello, <b>{{$user_name}}</b>. Add your new to-do list here!</div>
                    <!-- {{ Auth::user()->access_token }} -->
                <div class="card-body">
                   <form style="margin: 0; padding: 0;" id="newItemForm" method="post" enctype="multipart/form-data">
                        <div class="container">
                            <label for="title" style="width: 20%"><b>Title</b></label>
                            <input style="display: inline; width: 60%;" type="text" placeholder="Enter Title" name="title" id="title" required>
                            <br>
                            <label for="body" style="width: 20%"><b>Description</b></label>
                            <input  style="display: inline; width: 60%;" type="text" placeholder="Enter Description" name="body" id="body" required>
                            <br>
                            <label for="due_date" style="width: 20%"><b>Due Date</b></label>
                            <input  style="display: inline; width: 60%;" type="date" placeholder="Enter Due Date" name="due_date" id="due_date" min="<?php echo date('Y-m-d'); ?>">
                            <br>
                            <label for="attachment" style="width: 20%"><b>Attachment</b></label>
                            <input  style="display: inline; width: 60%;" type="file" placeholder="Attachment" name="attachment" id="attachment" required>
                            <br>
                            <label for="reminder_id" style="width: 20%"><b>Reminder</b></label>
                            <select id="reminder_id" name="reminder_id">
                                @foreach($reminders as $reminder)
                                    <option value="{{$reminder->id}}">{{$reminder->name}}</option>
                                @endforeach
                            </select>
                            <hr>
                            <button type="submit">Add</button>
                        </div>
                    </form> 
                    <!-- @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif -->

                    <!-- You are logged in! -->
                </div>
            </div>
        </div>

        <div>

            <br>
            <br>
            <label><b>Filter by Status</b></label>
            <select id="filter_status" name="filter_status">
                <option value="">ALL</option>
                <option value="COMPLETE">COMPLETE</option>
                <option value="INCOMPLETE">INCOMPLETE</option>
            </select>
            <br>
            <div>
                <input type="checkbox" id="order_by_due_date" name="order_by_due_date" value="due_date">
                <label for="order_by_due_date"> Order by Due Date</label><br>
            </div>
            <table border="1" id=tableItems>
                <thead>
                    <tr style="bgcolor:yellow">
                        <th> Title </th>
                        <th> Description </th>
                        <th> Due Date  </th>
                        <th> Reminder </th>
                        <th> Attachment </th>
                        <th> Status </th>
                        <th> Action </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                      <td> {{$item->title}} </td>
                      <td> {{$item->body}} </td>
                      <td> {{$item->due_date}} </td>
                      <td> {{$item->reminder_name}} </td>
                      <td> <a href="{{$item->attachmentUrl}}"> {{$item->attachment}} </a> </td>
                      <td> {{$item->status}} </td>
                      <td> <button type="button" id="editBtn" value="{{$item->id}}" onclick="clickEdit(this.value)">Edit</button> <button type="button" id="deleteBtn" value="{{$item->id}}" onclick="clickDelete(this.value)">Delete</button> <button type="button" id="completeBtn" value="{{$item->id}}" onclick="clickComplete(this.value)">Complete</button></td>
                  </tr>
                  @endforeach
              </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
