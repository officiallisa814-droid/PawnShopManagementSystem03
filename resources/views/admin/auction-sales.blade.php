@extends('layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin_css/auction-sales.css') }}">
@endpush

@section('content')
    <div id="as-app" data-csrf="{{ csrf_token() }}" data-base="{{ url('auction-sales') }}"
        data-contract="{{ url('loanContract') }}">

        <div class="as-head">
            <h1>Auction &amp; Sales <small>ការដេញថ្លៃ និងលក់</small></h1>
            <a class="as-link" href="{{ route('loanContract') }}">Loan Contract</a>
        </div>

        <div class="as-stats" id="as-stats"></div>

        <div class="as-tabs">
            <button class="tab active" data-tab="expired">Expired Items</button>
            <button class="tab" data-tab="scheduled">Scheduled Auctions</button>
            <button class="tab" data-tab="history">Sales History</button>
        </div>

        <div class="as-card" id="pane-expired">
            <table class="as-table">
                <thead>
                    <tr>
                        <th>Contract</th>
                        <th>Customer</th>
                        <th>Item</th>
                        <th class="num">Loan</th>
                        <th class="num">Owed now</th>
                        <th>Overdue</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="as-expired">
                    <tr>
                        <td colspan="7" class="empty">Loading…</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="as-card" id="pane-scheduled" hidden>
            <table class="as-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Contract</th>
                        <th>Customer</th>
                        <th>Item</th>
                        <th class="num">Starting price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="as-scheduled">
                    <tr>
                        <td colspan="6" class="empty">Loading…</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="as-card" id="pane-history" hidden>
            <table class="as-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Contract</th>
                        <th>Item</th>
                        <th>Buyer</th>
                        <th class="num">Owed</th>
                        <th class="num">Sale price</th>
                        <th class="num">Result</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="as-history">
                    <tr>
                        <td colspan="8" class="empty">Loading…</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="as-modal" id="as-modal" hidden>
            <div class="as-dialog">
                <header>
                    <h2 id="as-modal-title"></h2><button type="button" id="as-x">​x</button>
                </header>
                <div id="as-modal-body"></div>
                <footer>
                    <button type="button" class="btn ghost" id="as-cancel">Cancel</button>
                    <button type="button" class="btn" id="as-ok">Save</button>
                </footer>
            </div>
        </div>
        <div class="as-toast" id="as-toast" hidden></div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/auction-sales.js') }}"></script>
@endpush