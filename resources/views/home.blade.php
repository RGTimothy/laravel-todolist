@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>
                    <!-- {{ Auth::user()->access_token }} -->
                <div class="card-body">
                    <table border="1">
                        <thead>
                            <tr>
                                <th> Title </th>
                                <th> Description </th>
                                <th> Due Date  </th>
                                <th> Reminder </th>
                                <th> Attachment </th>
                                <th> Status </th>
                            </tr>
                        </thead>
                        <tbody>
                              <tr>
                                  <td> test </td>
                                  <td> test </td>
                                  <td> test </td>
                                  <td> test </td>
                                  <td> test </td>
                                  <td> test </td>
                              </tr>
                       </tbody>
                    </table>
                    <!-- @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif -->

                    <!-- You are logged in! -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
