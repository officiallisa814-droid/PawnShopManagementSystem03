{{-- ហៅគ្រោងឆ្អឹងរួម (Sidebar & Header) ពីហ្វាយមេមកប្រើ --}}
@extends('layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin_css/loanContract.css') }}">
@endpush

@section('content')
    <div id="lc-app" data-csrf="{{ csrf_token() }}" data-base="{{ url('loan-contracts') }}"
        data-show="{{ url('loanContract') }}" data-rate="{{ $defaults['default_rate'] }}"
        data-term="{{ $defaults['default_term'] }}" data-max-loan="{{ $defaults['max_loan_percent'] }}"
        data-symbol="{{ $defaults['currency_symbol'] }}" data-tax-on="{{ $defaults['tax_enabled'] }}"
        data-tax-name="{{ $defaults['tax_name'] }}" data-tax-rate="{{ $defaults['tax_rate'] }}"
        data-customers="{{ json_encode($customers ?? []) }}">

        <div class="lc-head">
            <h1>Loan Contract <small>កិច្ចសន្យាបញ្ចាំ</small></h1>
            <select id="lc-actions" class="lc-select">
                <option value="">⚙ សកម្មភាព / Actions</option>
                <option value="create">Create Pawn Contract</option>
                <option value="refresh">Refresh data</option>
            </select>
        </div>

        <div class="lc-stats" id="lc-stats"></div>

        <div class="lc-filters">
            <input type="search" id="lc-search" placeholder="Search contract no, customer, item…">
            <select id="lc-status" class="lc-select">
                <option value="">All status / គ្រប់ស្ថានភាព</option>
                <option value="active">Active</option>
                <option value="renewed">Renewed</option>
                <option value="redeemed">Redeemed</option>
                <option value="expired">Expired</option>
                <option value="auctioned">Auctioned</option>
            </select>
        </div>

        <div class="lc-table-wrap">
            <table class="lc-table">
                <thead>
                    <tr>
                        <th>Contract Number</th>
                        <th>Customer</th>
                        <th>Pawn Item</th>
                        <th class="num">Loan Amount</th>
                        <th class="num">Interest Rate</th>
                        <th>Loan Date</th>
                        <th>Due Date</th>
                        <th class="num">Interest Due</th>
                        <th class="num">Total to Redeem</th>
                        <th>Contract Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="lc-body">
                    <tr>
                        <td colspan="11" class="empty">Loading…</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="lc-modal" id="lc-modal" hidden>
            <div class="lc-dialog">
                <header>
                    <h2 id="lc-modal-title"></h2>
                    <button type="button" id="lc-modal-x" aria-label="Close">×</button>
                </header>
                <div id="lc-modal-body"></div>
                <footer>
                    <button type="button" class="btn ghost" id="lc-modal-cancel">Cancel</button>
                    <button type="button" class="btn" id="lc-modal-ok">Save</button>
                </footer>
            </div>
        </div>

        <div class="lc-toast" id="lc-toast" hidden></div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/loanContract.js') }}"></script>
@endpush