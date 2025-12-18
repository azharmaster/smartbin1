@extends($layout)

@section('content')
<h1>Task Details</h1>

<p><strong>ID:</strong> {{ $task->id }}</p>
<p><strong>User:</strong> {{ $task->user->name ?? $task->userID }}</p>
<p><strong>Asset:</strong> {{ $task->asset->name ?? $task->assetID }}</p>
<p><strong>Description:</strong> {{ $task->description }}</p>

<a href="{{ route('tasks.index') }}">Back to list</a>
@endsection
