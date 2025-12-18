@extends($layout)

@section('content')
<h1>Edit Task</h1>

<x-task.form-task :id="$task->id" />
@endsection
