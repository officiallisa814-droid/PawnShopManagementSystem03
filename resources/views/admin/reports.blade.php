@extends('layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin_css/reports.css') }}">
@endpush

@section('content')
    <div id="rp-app" data-data="{{ route('reports.data') }}">


        <div class="rp-head">
            <h1>Reports <small>របាយការណ៍</small></h1>
            <div class="rp-export">
                <button type="button" class="btn ghost" id="rp-print"><i class="fa-solid fa-print"></i> Print</button>
                <button type="button" class="btn ghost" id="rp-pdf"><i class="fa-solid fa-file-pdf"></i> PDF</button>
                <button type="button" class="btn" id="rp-excel"><i class="fa-solid fa-file-excel"></i> Excel</button>
            </div>
        </div>

        <div class="rp-filters">
            <div class="f">
                <label>Report</label>
                <select id="rp-type">
                    @foreach ($types as $k => $v)
                        <option value="{{ $k }}" {{ $k === 'revenue' ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="f"><label>From</label><input type="date" id="rp-from"
                    value="{{ now()->startOfMonth()->toDateString() }}"></div>
            <div class="f"><label>To</label><input type="date" id="rp-to" value="{{ now()->endOfMonth()->toDateString() }}">
            </div>
            <div class="f"><label>&nbsp;</label><button type="button" class="btn" id="rp-run">Generate</button></div>
        </div>

        <div class="rp-sheet" id="rp-sheet">
            <div class="rp-letterhead">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="rp-logo" width="40px">
                <div class="rp-shop">
                    <h3>{{ $shop['shop_name'] }}</h3>
                    <p>{{ $shop['shop_address'] }}</p>
                    <p>Tel: {{ $shop['shop_phone'] }} {{ $shop['shop_email'] ? '· ' . $shop['shop_email'] : '' }}</p>
                </div>
            </div>
            <div class="rp-title">
                <h2 id="rp-title">—</h2>
                <p id="rp-period"></p>
            </div>
            <div class="rp-summary" id="rp-summary"></div>
            <p class="rp-note" id="rp-note" hidden></p>
            <div class="rp-table-wrap">
                <table class="rp-table">
                    <thead id="rp-head"></thead>
                    <tbody id="rp-body">
                        <tr>
                            <td class="empty">Loading…</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rp-toast" id="rp-toast" hidden></div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/reports.js') }}"></script>
@endpush