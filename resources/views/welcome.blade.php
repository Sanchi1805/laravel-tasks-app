<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>PHP - Simple To Do List App</title>
</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-4">PHP - Simple To Do List App</h2>

        <!-- Form to Add Task -->
        <div class="input-group mb-3">
            <input type="text" id="name_todo" class="form-control" placeholder="Enter Task Name">
            <button class="btn btn-primary" id="add_todo">Add Task</button>&nbsp;
            <button type="button" id="show_all_tasks" class="btn btn-secondary">Show All Tasks</button>
        </div>

        <!-- Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Task</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="list-todo">
                @foreach ($todos as $todo)
                <tr id="row_todo_{{ $todo->id }}" class="{{ $todo->completed ? 'completed' : '' }}">
                    <td>{{ $todo->id }}</td>
                    <td>{{ $todo->name }}</td>
                    <td>
                        <input type="checkbox" class="mark-complete" data-id="{{ $todo->id }}" {{ $todo->completed ? 'checked' : '' }}>
                        <button class="btn btn-sm btn-danger delete-todo ms-2" data-id="{{ $todo->id }}">✘</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>

    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'x-csrf-token': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Add task
            $('#add_todo').on('click', function () {
                var taskName = $('#name_todo').val().trim();
                if (taskName) {
                    $.ajax({
                        url: "/todos/store",
                        data: { name: taskName },
                        type: 'POST',
                        success: function (res) {
                            if (res.error) {
                                alert(res.error);
                            } else {
                                var row = '<tr id="row_todo_' + res.id + '">';
                                row += '<td>' + res.id + '</td>';
                                row += '<td>' + res.name + '</td>';
                                row += '<td>';
                                row += '<input type="checkbox" class="mark-complete" data-id="' + res.id + '">';
                                row += '<button class="btn btn-sm btn-danger delete-todo ms-2" data-id="' + res.id + '">✘</button>';
                                row += '</td>';
                                row += '</tr>';
                                $('#list-todo').prepend(row);
                                $('#name_todo').val('');
                            }
                        },
                        error: function (xhr) {
                            if (xhr.status === 409) {
                                alert('Task already exists.');
                            } else {
                                console.error('Error saving task:', xhr);
                            }
                        }
                    });
                } else {
                    alert("Please enter a task name.");
                }
            });

            // Mark task as completed
            $(document).on('change', '.mark-complete', function () {
                var taskId = $(this).data('id');
                var isChecked = $(this).is(':checked');

                $.ajax({
                    url: "/todos/complete/" + taskId,
                    type: 'POST',
                    data: { completed: isChecked },
                    success: function (res) {
                        if (res.success) {
                            $('#row_todo_' + taskId).toggle(!isChecked);
                        } else {
                            console.log('Failed to update task status');
                        }
                    }
                });
            });

            // Delete task
            $(document).on('click', '.delete-todo', function () {
                var taskId = $(this).data('id');
                if (confirm('Are you sure you want to delete this task?')) {
                    $.ajax({
                        url: "/todos/delete/" + taskId,
                        type: 'DELETE',
                        success: function (res) {
                            $('#row_todo_' + taskId).remove();
                        }
                    });
                }
            });

            // Show all tasks
            $('#show_all_tasks').on('click', function () {
                $.ajax({
                    url: "/todos/all",
                    type: 'GET',
                    success: function (res) {
                        var rows = '';
                        $.each(res.todos, function (index, todo) {
                            var completedClass = todo.completed ? 'completed' : '';
                            var checkedAttr = todo.completed ? 'checked' : '';
                            rows += '<tr id="row_todo_' + todo.id + '" class="' + completedClass + '">';
                            rows += '<td>' + todo.id + '</td>';
                            rows += '<td>' + todo.name + '</td>';
                            rows += '<td>';
                            rows += '<input type="checkbox" class="mark-complete" data-id="' + todo.id + '" ' + checkedAttr + '>';
                            rows += '<button class="btn btn-sm btn-danger delete-todo ms-2" data-id="' + todo.id + '">✘</button>';
                            rows += '</td>';
                            rows += '</tr>';
                        });
                        $('#list-todo').html(rows);
                    },
                    error: function (xhr) {
                        console.error('Error fetching tasks:', xhr);
                    }
                });
            });

        });
    </script>

</body>

</html>
