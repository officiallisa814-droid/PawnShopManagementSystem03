@extends('layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin_css/payments.css') }}">
@endpush

@section('content')
    <div id="pm-app" data-csrf="{{ csrf_token() }}" data-data="{{ route('payments.data') }}"
        data-pay="{{ url('payments') }}" data-customer="{{ url('customer') }}" data-pawn="{{ url('pawnItem') }}"
        data-contract="{{ route('loanContract') }}">

        <div class="pm-head">
            <h1>Payments <small>ការបង់ប្រាក់</small></h1>
            <div class="pm-links">
                <a href="{{ route('pawnItem') }}">Pawn Item</a>
                <a href="{{ route('loanContract') }}">Loan Contract</a>
                <a href="{{ route('auction-sales') }}">Auction &amp; Sales</a>
            </div>
        </div>

        <div class="pm-stats" id="pm-stats"></div>

        <div class="pm-tabs" role="tablist">
            <button type="button" class="tab active" data-tab="schedules">Interest schedule · Pawn Item</button>
            <button type="button" class="tab" data-tab="contracts">Loan Contract payments</button>
        </div>

        <div class="pm-filters">
            <input type="search" id="pm-search" placeholder="Search customer, phone, item, contract no…">
            <select id="pm-status">
                <option value="open">កំពុងដំណើរការ / Active</option>
                <option value="overdue">ហួសកំណត់ / Overdue</option>
                <option value="closed">បិទហើយ / Closed</option>
                <option value="all">ទាំងអស់ / All</option>
            </select>
        </div>

        <div class="pm-card" id="pane-schedules">
            <table class="pm-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Pawn Item</th>
                        <th>Next due</th>
                        <th class="num">Interest</th>
                        <th>Paid</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="pm-sched">
                    <tr>
                        <td colspan="6" class="empty">Loading…</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pm-card" id="pane-contracts" hidden>
            <table class="pm-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Contract</th>
                        <th>Customer</th>
                        <th>Pawn Item</th>
                        <th>Type</th>
                        <th class="num">Amount</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody id="pm-contract">
                    <tr>
                        <td colspan="7" class="empty">Loading…</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pm-modal" id="pm-modal" hidden>
            <div class="pm-dialog">
                <header>
                    <h2>Record payment / កត់ត្រាការបង់ប្រាក់</h2><button type="button" id="pm-x"
                        aria-label="Close">×</button>
                </header>
                <div id="pm-modal-body"></div>
                <footer>
                    <button type="button" class="btn ghost" id="pm-cancel">Cancel</button>
                    <button type="button" class="btn" id="pm-ok">Confirm payment</button>
                </footer>
            </div>
        </div>
        <div class="pm-toast" id="pm-toast" hidden></div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/payments.js') }}"></script>
@endpush