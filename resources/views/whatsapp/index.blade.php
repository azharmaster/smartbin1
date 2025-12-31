@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">WhatsApp Notifications</h1>

    <a href="{{ route('whatsapp.create') }}" class="btn btn-primary mb-3">Create New Notification</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Title</th>
                <th>Message</th>
                <th>Target</th>
                <th>Status</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Last Sent</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    @foreach($notifications as $notif)
    <tr>
        <td>{{ $notif->title }}</td>
        <td>{{ Str::limit($notif->message, 50) }}</td>
        <td>{{ $notif->target ?? '-' }}</td>
        <td>
            @if($notif->is_active)
                <span class="badge bg-success">ON</span>
            @else
                <span class="badge bg-danger">OFF</span>
            @endif
        </td>
        <td>{{ optional($notif->start_time)->format('Y-m-d H:i') ?? '-' }}</td>
        <td>{{ optional($notif->end_time)->format('Y-m-d H:i') ?? '-' }}</td>
        <td>{{ optional($notif->last_sent_at)->format('Y-m-d H:i') ?? '-' }}</td>
        <td>
            <a href="{{ route('whatsapp.edit', $notif->id) }}" class="btn btn-sm btn-warning">Edit</a>

            <form action="{{ route('whatsapp.destroy', $notif->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this notification?')">Delete</button>
            </form>

            <!-- Optional: Manual send button -->
            <form action="{{ route('whatsapp.send', $notif->id) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-sm btn-info">Send Now</button>
            </form>
        </td>
    </tr>
    @endforeach
</tbody>
    </table>
</div>
@endsection
