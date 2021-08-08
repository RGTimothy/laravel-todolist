<html>
    <head>
    </head>
    <body>
        <h1>Hi there! Just to remind you that this task needs to be completed:</h1>
        <hr>
        <table>
            <tr>
                <td><b>Title: </b>{{ $title }}</td>
            </tr>
            <tr>
                <td><b>Body: </b>{{ $body }}</td>
            </tr>
            <tr>
                <td><b>Due Date: </b>{{ $due_date }}</td>
            </tr>
            <tr>
                <td><b>Status: </b>{{ $status }}</td>
            </tr>
            <tr>
                <td><b>Created At: </b>{{ $created_at }}</td>
            </tr>
        </table>
    </body>
</html>