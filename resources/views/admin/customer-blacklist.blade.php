@extends('layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin_css/customer.css') }}">
    <style>
        .bl-card { background:#fff; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden; }
        .bl-top { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:20px; }
        .bl-title { color:#7f1d1d; font-size:1.5rem; font-weight:700; margin:0; }
        .bl-table { width:100%; border-collapse:collapse; font-size:14px; min-width:700px; }
        .bl-table th { background:#7f1d1d; color:#fff; text-align:left; padding:14px 20px; }
        .bl-table td { padding:14px 20px; border-bottom:1px solid #f1f5f9; }
        .bl-table tbody tr { cursor:pointer; }
        .bl-table tbody tr:hover { background:#fef2f2; }
        .bl-empty { text-align:center; color:#94a3b8; padding:40px !important; }
        .bl-scroll { overflow-x:auto; }
    </style>
@endpush

@section('content')
<div class="customer-container">

    <div class="bl-top">
        <h2 class="bl-title">
            <i class="fa-solid fa-user-slash"></i>
            <span class="khmer-os">បញ្ជីអតិថិជនប្រវត្តិមិនល្អ</span> / Blacklist ({{ $customers->count() }})
        </h2>
        <a href="{{ route('customer') }}" class="btn btn-export" style="text-decoration:none;">
            ← <span class="khmer-os">ត្រឡប់ក្រោយ</span> / Back
        </a>
    </div>

    <div class="bl-card">
        <div class="bl-scroll">
            <table class="bl-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>ID/Passport</th>
                        <th>Phone</th>
                        <th>Province</th>
                        <th>Pledges</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $b)
                        <tr onclick="window.location='{{ route('customer.show', $b->id) }}'">
                            <td>{{ $b->id }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    @if($b->customer_avatar)
                                        <img src="{{ asset('storage/' . $b->customer_avatar) }}"
                                            style="width:35px;height:35px;border-radius:50%;object-fit:cover;">
                                    @else
                                        <div style="width:35px;height:35px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;color:#6b7280;">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                    @endif
                                    <b style="color:#b91c1c;">{{ $b->customer_name }}</b>
                                </div>
                            </td>
                            <td>{{ $b->passport_id ?? '-' }}</td>
                            <td>{{ $b->phone_number }}</td>
                            <td>{{ $b->address_province ?? '-' }}</td>
                            <td>{{ $b->pawnItems->count() }}</td>
                            <td>{{ ucfirst($b->customer_status) }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($b->notes, 50) ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="bl-empty">មិនមានអតិថិជនប្រវត្តិមិនល្អទេ / No blacklisted customers.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection