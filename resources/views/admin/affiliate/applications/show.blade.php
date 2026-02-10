@extends('layouts.admin')

@section('content')
    <h1 class="mb-4">Affiliate Application Details</h1>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Applicant Information</strong>
        </div>
        <div class="card-body">
            @php
                $appData = json_decode($application->application_data, true);
            @endphp
            <p><strong>Name:</strong> {{ $appData['full_name'] ?? ($application->user->name ?? '-') }}</p>
            <p><strong>Email:</strong> {{ $appData['email'] ?? ($application->user->email ?? '-') }}</p>
            <p><strong>County:</strong> {{ $appData['county'] ?? '-' }}</p>
            <p><strong>MPESA Number:</strong> {{ $appData['payment_details'] ?? '-' }}</p>
            <p><strong>Applied At:</strong> {{ $application->created_at->format('Y-m-d H:i') }}</p>
            <p><strong>Status:</strong>
                <span class="badge badge-{{ $application->status == 'approved' ? 'success' : ($application->status == 'rejected' ? 'danger' : 'secondary') }}">
                    {{ ucfirst($application->status) }}
                </span>
            </p>
            @if($application->admin_feedback)
                <p><strong>Admin Feedback:</strong> {{ $application->admin_feedback }}</p>
            @endif
        </div>
    </div>

    @if($application->status === 'pending')
        <div class="row">
            <div class="col-md-6">
                <form action="{{ route('admin.affiliate.applications.approve', $application) }}" method="POST" class="mb-3">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <label for="approve_feedback">Admin Feedback (optional)</label>
                        <textarea name="admin_feedback" id="approve_feedback" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Approve Application</button>
                </form>
            </div>
            <div class="col-md-6">
                <form action="{{ route('admin.affiliate.applications.reject', $application) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <label for="reject_feedback">Admin Feedback (required for rejection)</label>
                        <textarea name="admin_feedback" id="reject_feedback" class="form-control" rows="2" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Reject Application</button>
                </form>
            </div>
        </div>
    @else
        <a href="{{ route('admin.affiliate.applications') }}" class="btn btn-secondary">Back to Applications</a>
    @endif
@endsection