@extends('layouts.master')

<!-- បាញ់ហ្វាយ CSS របស់ Dashboard ទៅកាន់ក្បាលរបស់ Master Layout -->
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin_css/dashboard.css') }}">
@endpush

@section('content')
    <!-- ជួរប្រអប់រាប់សរុប (Top Cards) -->
    <div class="top-cards">
        <!-- Card 1: Total Customers -->
        <div class="card" style="border: 1px solid #2563eb;">
            <div class="icon-circle blue">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="card-info">
                <span>Total Customers</span>
                <h3>{{ number_format($totalCustomers) }}</h3>
            </div>
        </div>

        <!-- Card 2: Active Pawn Items -->
        <div class="card" style="border: 1px solid #16a34a;">
            <div class="icon-circle green">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <div class="card-info">
                <span>Active Pawn Items</span>
                <h3>{{ number_format($activePawnItems) }}</h3>
            </div>
        </div>

        <!-- Card 3: Total Loan Amount -->
        <div class="card" style="border: 1px solid #7c3aed;">
            <div class="icon-circle purple">
                <i class="fa-solid fa-dollar-sign"></i>
            </div>
            <div class="card-info">
                <span>Total Loan Amount</span>
                <h3>${{ number_format($totalLoanAmount, 2) }}</h3>
            </div>
        </div>

        <!-- Card 4: Total Interest Earned -->
        <div class="card" style="border: 1px solid #ea580c;">
            <div class="icon-circle orange">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div class="card-info">
                <span>Total Interest Earned</span>
                <h3>$12,580</h3>
            </div>
        </div>
    </div>


    <!-- ===================================================================================================================== -->


    <!-- Selection Data -->
    <div class="dashboard-grid">

        <!-- 1. Loan Overview Card -->
        <div class="grid-card">
            <div class="card-header">
                <h3>Loan Overview <span class="subtitle">({{ now()->format('F Y') }})</span></h3>
            </div>
            <div style="position:relative;height:300px;">
                <canvas id="loanChart"></canvas>
            </div>
        </div>

        <!-- 2. Pawn Items by Category Card -->
        <div class="grid-card">
            <div class="card-header">
                <h3>Pawn Items by Category</h3>
            </div>
            <div class="donut-container">
                <div style="position:relative;width:200px;height:200px;flex-shrink:0;">
                    <canvas id="categoryChart"></canvas>
                    <div class="donut-center"
                        style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;">
                        <strong>{{ number_format($grand) }}</strong>
                        <span>Items</span>
                    </div>
                </div>
                <div class="category-legend">
                    @php $colors = ['#2563eb', '#1e3a8a', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6']; @endphp
                    @forelse ($categories as $i => $c)
                        <div class="legend-item">
                            <span class="dot" style="background: {{ $colors[$i % count($colors)] }};"></span>
                            {{ $c['name'] }}
                            <span class="count">{{ number_format($c['count']) }} ({{ $c['percent'] }}%)</span>
                        </div>
                    @empty
                        <div class="legend-item" style="color:#94a3b8;">មិនទាន់មានទំនិញ / No items yet</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 3. Recent Activity Card -->
        <div class="grid-card">
            <div class="card-header">
                <h3>Recent Activity</h3>
            </div>
            <div class="activity-list">
                @php
                    $activities = [
                        ['green', 'fa-user-plus', 'New Customer Added', 'Today, 10:30 AM', 'customer', 'customers.view'],
                        ['blue', 'fa-briefcase', 'Pawn Item Added', 'Today, 09:45 AM', 'pawnItem', 'pawnitems.view'],
                        ['orange', 'fa-file-invoice', 'Loan Contract Created', 'Today, 09:20 AM', 'loanContract', 'contracts.view'],
                        ['emerald', 'fa-money-bill-wave', 'Payment Received', 'Today, 08:15 AM', 'payments', 'payments.view'],
                        ['purple', 'fa-hand-holding-hand', 'Item Redeemed', 'Yesterday, 04:40 PM', 'loanContract', 'contracts.view'],
                    ];
                @endphp

                @foreach ($activities as [$color, $icon, $title, $time, $route, $perm])
                    @php $ok = auth()->user()->can($perm); @endphp
                    <{{ $ok ? 'a' : 'div' }} class="activity-item" @if ($ok) href="{{ route($route) }}" @endif>
                        <div class="activity-icon {{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
                        <div class="activity-details">
                            <h4>{{ $title }}</h4>
                            <span>{{ $time }}</span>
                        </div>
                    </{{ $ok ? 'a' : 'div' }}>
                @endforeach
            </div>
        </div>

    </div>




    <!-- =========================================================================================== -->





    <div class="dashboard-bottom-grid">


        <!-- Recent Pawn Items Table Card -->
        <div class="grid-card">
            <div class="card-header">
                <h3>Recent Pawn Items</h3>
                <!-- Link button directly to the main pawn item directory page -->
                <a href="{{ route('pawnItem') }}" class="view-all-btn" style="text-decoration: none;">View All</a>
            </div>

            <div class="table-container">
                <table class="pawn-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Item Name</th>
                            <th>Customer</th>
                            <th>Loan Amount</th>
                            <th>Date</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 1. Start dynamic database collection loop -->
                        @forelse($recentPawnItems as $item)
                            <!-- Clickable row links to specific schedule details page -->
                            <tr onclick="window.location='{{ route('pawnItem.show', $item->id) }}'" style="cursor: pointer;">
                                <!-- Format primary key digits into consistent prefix formats -->
                                <td class="id-text">PWN{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</td>

                                <!-- Display category matching dynamic visual icons -->
                                <td class="item-name">
                                    <span class="item-icon">
                                        @if($item->category == 'electronics')
                                        @elseif($item->category == 'jewelry')
                                        @elseif($item->category == 'vehicle')
                                        @else
                                        @endif
                                    </span>
                                    {{ $item->item_name }}
                                </td>

                                <!-- Fetch active relationship owner names cleanly -->
                                <td>{{ $item->customer->customer_name ?? 'N/A' }}</td>

                                <!-- Print current primary active values with decimal filters -->
                                <td class="amount">$ {{ number_format($item->approved_loan, 0) }}</td>

                                <!-- Format timestamp logs into clean readability views -->
                                <td>{{ \Carbon\Carbon::parse($item->pawn_date)->format('d/m/Y') }}</td>

                                <!-- Display dynamic status class badge components based on data -->
                                <td class="status-cell">
                                    <span class="badge {{ strtolower($item->item_status ?? $item->status) }}">
                                        {{ ucfirst($item->item_status ?? $item->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <!-- 2. Fallback row displays when no database history is discovered -->
                            <tr>
                                <td colspan="6" style="text-align: center; color: #94a3b8; padding: 30px 10px;">
                                    No recent pawn entries recorded in the system logs.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


        <!-- 2. Loan Status Summary Card -->
        <div class="grid-card">
            <div class="card-header">
                <h3>Loan Status Summary</h3>
            </div>

            <div class="summary-list">
                @foreach ($loanStatus as $s)
                    <div class="summary-item">
                        <div class="summary-info">
                            <span class="status-label">{{ $s['label'] }}</span>
                            <span class="status-count">{{ number_format($s['count']) }}
                                <span class="pct">({{ $s['percent'] }}%)</span>
                            </span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill {{ $s['bar'] }}" style="width: {{ $s['percent'] }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>




@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        window.dashboardData = {
            loan: @json($loanChart),
            cats: @json($categories),
        };
    </script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush