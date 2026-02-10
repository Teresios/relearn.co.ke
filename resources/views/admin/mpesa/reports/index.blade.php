@extends('admin.mpesa.layout')

@section('mpesa-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-bar-graph me-2"></i>Reports</h2>
    <div class="btn-group">
        <a href="{{ route('admin.mpesa.reports.daily') }}" class="btn btn-outline-primary">Daily</a>
        <a href="{{ route('admin.mpesa.reports.weekly') }}" class="btn btn-outline-primary">Weekly</a>
        <a href="{{ route('admin.mpesa.reports.monthly') }}" class="btn btn-outline-primary">Monthly</a>
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#customReportModal">Custom</button>
    </div>
</div>

<div class="row g-4">
    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Generate Report</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.mpesa.reports.generate') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Report Type</label>
                        <select name="report_type" class="form-select" id="reportType">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div class="custom-dates" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-file-earmark-plus me-1"></i>Generate Report
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Analytics Link -->
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Reports</h5>
                <a href="{{ route('admin.mpesa.reports.analytics') }}" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-graph-up me-1"></i>View Analytics
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Period</th>
                                <th>Inbound</th>
                                <th>Outbound</th>
                                <th>Net</th>
                                <th>Generated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ ucfirst($report->report_type) }}</span></td>
                                    <td>{{ $report->period_start->format('M d') }} - {{ $report->period_end->format('M d, Y') }}</td>
                                    <td class="text-success">KES {{ number_format($report->total_inbound, 0) }}</td>
                                    <td class="text-danger">KES {{ number_format($report->total_outbound, 0) }}</td>
                                    <td class="{{ $report->net_amount >= 0 ? 'text-success' : 'text-danger' }}">
                                        KES {{ number_format($report->net_amount, 0) }}
                                    </td>
                                    <td>{{ $report->generated_at->diffForHumans() }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.mpesa.reports.show', $report) }}" class="btn btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.mpesa.reports.export.pdf', $report) }}" class="btn btn-outline-danger">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                            <a href="{{ route('admin.mpesa.reports.export.csv', $report) }}" class="btn btn-outline-success">
                                                <i class="bi bi-file-csv"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No reports generated yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($reports->hasPages())
                <div class="card-footer">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('reportType').addEventListener('change', function() {
        document.querySelectorAll('.custom-dates').forEach(el => {
            el.style.display = this.value === 'custom' ? 'block' : 'none';
        });
    });
</script>
@endpush
@endsection
