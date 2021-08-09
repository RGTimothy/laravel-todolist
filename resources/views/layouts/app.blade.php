<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script type="text/javascript">
        function resetModalValues() {
            $("#edit_title").val("");
            $("#edit_body").val("");
            $("#edit_due_date").val("");
            $("#edit_reminder_id").val("");
            $("#updatingId").val("");
        }

        function getDataAndUpdateTable() {
            var filter_status = $("#filter_status").find('option:selected');
            filter_status = filter_status.val();

            var order_by = "id";
            var order_by_state = "asc";

            var order_by_due_date = $("#order_by_due_date").is(":checked");
            if (order_by_due_date) {
                order_by = "due_date";
                order_by_state = "asc";
            }

            var access_token = "{{ $access_token ?? '' }}";
            var api_baseurl = "{{ $api_baseurl ?? '' }}";

            $.ajax({
                type: "GET",
                url: api_baseurl + "/api/to-do",
                headers: {"Authorization": access_token},
                dataType: "json",
                data: {"status": filter_status, "order_by": order_by, "order_state": order_by_state},
                success: function (result, status, xhr) {
                    console.log(result);
                    if (result["errors"][0] !== undefined) {
                        alert(result["errors"][0]["message"]);
                    } else {
                        if (result["data"]["list"] !== undefined) {
                            var newTableBody = "";
                            $.each(result["data"]["list"], function (i, val) {
                                newTableBody += "<tr>";
                                    // alert(val["title"]);
                                    newTableBody += "<td> " + val["title"] + " </td>";
                                    newTableBody += "<td> " + val["body"] + " </td>";
                                    newTableBody += "<td> " + val["due_date"] + " </td>";
                                    newTableBody += "<td> " + val["reminder_name"] + " </td>";
                                    newTableBody += '<td> <a href="'+val["attachmentUrl"]+'">' + val["attachment"] + "</a> </td>";
                                    newTableBody += "<td> " + val["status"] + " </td>";
                                    newTableBody += '<td> <button type="button" id="editBtn" value="'+val["id"]+'" onclick="clickEdit(this.value)">Edit</button> <button type="button" id="deleteBtn" value="'+val["id"]+'" onclick="clickDelete(this.value)">Delete</button> <button type="button" id="completeBtn" value="'+val["id"]+'" onclick="clickComplete(this.value)">Complete</button></td>';

                                    newTableBody += "</tr>";
                                });

                            console.log(newTableBody);
                            $("#tableItems tbody").html(newTableBody);
                        }
                    }
                },
                error: function (xhr, status, error) {
                    alert("Result: " + status + " " + error + " " + xhr.status + " " + xhr.statusText)
                }
            });
        }

        function clickComplete(val) {
            if(confirm("Complete this item?"))
            {
                var access_token = "{{ $access_token ?? '' }}";
                var api_baseurl = "{{ $api_baseurl ?? '' }}";

                $.ajax({
                    type: "POST",
                    url: api_baseurl + "/api/to-do/mark/" + val,
                    headers: {"Authorization": access_token},
                    dataType: "json",
                    data: {"status": "COMPLETE"},
                    success: function (result, status, xhr) {
                        console.log(result);
                        if (result["errors"][0] !== undefined) {
                            alert(result["errors"][0]["message"]);
                        } else {
                            alert(result["data"]["message"]);
                            location.reload();
                        }
                    },
                    error: function (xhr, status, error) {
                        alert("Result: " + status + " " + error + " " + xhr.status + " " + xhr.statusText)
                    }
                });
            }
        }

        function clickDelete(val) {
            if(confirm("Delete this item?"))
            {
                var access_token = "{{ $access_token ?? '' }}";
                var api_baseurl = "{{ $api_baseurl ?? '' }}";

                $.ajax({
                    type: "DELETE",
                    url: api_baseurl + "/api/to-do/" + val,
                    headers: {"Authorization": access_token},
                    dataType: "json",
                    success: function (result, status, xhr) {
                        console.log(result);
                        if (result["errors"][0] !== undefined) {
                            alert(result["errors"][0]["message"]);
                        } else {
                            alert(result["data"]["message"]);
                            location.reload();
                        }
                    },
                    error: function (xhr, status, error) {
                        alert("Result: " + status + " " + error + " " + xhr.status + " " + xhr.statusText)
                    }
                });
            }
        }

        function clickEdit(val) {
            $("#updatingId").val(val);
            editModal.style.display = "block";

            var access_token = "{{ $access_token ?? '' }}";
            var api_baseurl = "{{ $api_baseurl ?? '' }}";

            $.ajax({
                type: "GET",
                url: api_baseurl + "/api/to-do/" + val,
                headers: {"Authorization": access_token},
                dataType: "json",
                success: function (result, status, xhr) {
                    console.log(result);
                    if (result["errors"][0] !== undefined) {
                        alert(result["errors"][0]["message"]);
                    } else {
                        if (result["data"]["list"][0] !== undefined) {
                            $("#edit_title").val(result["data"]["list"][0]["title"]);
                            $("#edit_body").val(result["data"]["list"][0]["body"]);
                            $("#edit_due_date").val(result["data"]["list"][0]["due_date"]);
                            $("#edit_reminder_id").val(result["data"]["list"][0]["reminder_id"]);
                        }
                    }
                },
                error: function (xhr, status, error) {
                    alert("Result: " + status + " " + error + " " + xhr.status + " " + xhr.statusText)
                }
            });
        }
        $(document).ready(function() {
            var editModal = document.getElementById("editModal");
            var editBtn = document.getElementById("editBtn");
            var span = document.getElementsByClassName("close")[0];

            /*editBtn.onclick = function(event) {
                editModal.style.display = "block";
            }*/

            span.onclick = function() {
                editModal.style.display = "none";
                resetModalValues();
            }

            window.onclick = function(event) {
                if (event.target == editModal) {
                    editModal.style.display = "none";
                    resetModalValues();
                }
            }

            $("form#newItemForm").submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                var access_token = "{{ $access_token ?? '' }}";
                var api_baseurl = "{{ $api_baseurl ?? '' }}";

                $.ajax({
                    url: api_baseurl + "/api/to-do",
                    headers: {"Authorization": access_token},
                    type: 'POST',
                    data: formData,
                    success: function (data) {
                        if (data["errors"][0] !== undefined) {
                            alert(data["errors"][0]["message"]);
                        } else {
                            alert(data["data"]["message"]);
                            location.reload();
                        }
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

            $("form#editItemForm").submit(function(e) {
                var updatingId = $("#updatingId").val();
                e.preventDefault();
                var formData = new FormData(this);
                var access_token = "{{ $access_token ?? '' }}";
                var api_baseurl = "{{ $api_baseurl ?? '' }}";

                $.ajax({
                    url: api_baseurl + "/api/to-do/" + updatingId,
                    headers: {"Authorization": access_token},
                    type: 'POST',
                    data: formData,
                    success: function (data) {
                        if (data["errors"][0] !== undefined) {
                            alert(data["errors"][0]["message"]);
                        } else {
                            alert(data["data"]["message"]);
                            location.reload();
                        }
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

            $('#filter_status').change(function() {
                getDataAndUpdateTable();
            });

            $("#order_by_due_date").change(function() {
                getDataAndUpdateTable();
            });
        });
    </script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style type="text/css">
       /* The Modal (background) */
       .modal {
          display: none; /* Hidden by default */
          position: fixed; /* Stay in place */
          z-index: 1; /* Sit on top */
          left: 0;
          top: 0;
          width: 100%; /* Full width */
          height: 100%; /* Full height */
          overflow: auto; /* Enable scroll if needed */
          background-color: rgb(0,0,0); /* Fallback color */
          background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
      }

      /* Modal Content/Box */
      .modal-content {
          background-color: #fefefe;
          margin: 15% auto; /* 15% from the top and centered */
          padding: 20px;
          border: 1px solid #888;
          width: 80%; /* Could be more or less, depending on screen size */
      }

      /* The Close Button */
      .close {
          color: #aaa;
          float: right;
          font-size: 28px;
          font-weight: bold;
      }

      .close:hover,
      .close:focus {
          color: black;
          text-decoration: none;
          cursor: pointer;
      } 
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
