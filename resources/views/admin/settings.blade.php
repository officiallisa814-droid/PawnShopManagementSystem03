@extends('layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin_css/settings.css') }}">
@endpush

@section('content')
    <div id="st-app" data-tab="{{ session('tab', 'shop') }}">
        <div class="st-head">
            <h1>Settings <small>ការកំណត់</small></h1>
        </div>

        @if (session('success'))
        <div class="st-alert ok">{{ session('success') }}</div> @endif
        @if ($errors->any())
            <div class="st-alert err">@foreach ($errors->all() as $e)
            <div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <div class="st-tabs">
            <button class="tab" data-tab="shop">Shop Info</button>
            <button class="tab" data-tab="interest">Interest</button>
            <button class="tab" data-tab="tax">Tax</button>
            <button class="tab" data-tab="currency">Currency</button>
            <button class="tab" data-tab="backup">Backup / Restore</button>
        </div>

        {{-- Shop --}}
        <form class="st-card" data-pane="shop" method="POST" action="{{ route('settings.save', 'shop') }}">
            @csrf
            <h3>Pawn Shop Information</h3>
            <div class="grid">
                <div class="f"><label>Shop name</label><input name="shop_name"
                        value="{{ old('shop_name', $s['shop_name'] ?? '') }}" required></div>
                <div class="f"><label>Phone</label><input name="shop_phone"
                        value="{{ old('shop_phone', $s['shop_phone'] ?? '') }}"></div>
                <div class="f"><label>Email</label><input type="email" name="shop_email"
                        value="{{ old('shop_email', $s['shop_email'] ?? '') }}"></div>
                <div class="f full"><label>Address</label><input name="shop_address"
                        value="{{ old('shop_address', $s['shop_address'] ?? '') }}"></div>
            </div>
            <button class="btn">Save</button>
        </form>

        {{-- Interest --}}
        <form class="st-card" data-pane="interest" method="POST" action="{{ route('settings.save', 'interest') }}" hidden>
            @csrf
            <h3>Interest Rate Configuration</h3>
            <div class="grid">
                <div class="f"><label>Default interest (% / month)</label>
                    <input type="number" step="0.01" min="0" name="default_rate"
                        value="{{ old('default_rate', $s['default_rate']) }}" required>
                </div>
                <div class="f"><label>Default term (months)</label>
                    <input type="number" min="1" name="default_term" value="{{ old('default_term', $s['default_term']) }}"
                        required>
                </div>
                <div class="f"><label>Max loan (% of market value)</label>
                    <input type="number" step="0.01" min="1" max="100" name="max_loan_percent"
                        value="{{ old('max_loan_percent', $s['max_loan_percent']) }}" required>
                </div>
            </div>
            <button class="btn">Save</button>
        </form>

        {{-- Tax --}}
        <form class="st-card" data-pane="tax" method="POST" action="{{ route('settings.save', 'tax') }}" hidden>
            @csrf
            <h3>Tax Configuration</h3>
            <div class="grid">
                <div class="f check"><label><input type="checkbox" name="tax_enabled" value="1" {{ $s['tax_enabled'] == '1' ? 'checked' : '' }}> Enable tax</label></div>
                <div class="f"><label>Tax name</label><input name="tax_name" value="{{ old('tax_name', $s['tax_name']) }}"
                        required></div>
                <div class="f"><label>Tax rate (%)</label>
                    <input type="number" step="0.01" min="0" name="tax_rate" value="{{ old('tax_rate', $s['tax_rate']) }}"
                        required>
                </div>
            </div>
            <button class="btn">Save</button>
        </form>

        {{-- Currency --}}
        <form class="st-card" data-pane="currency" method="POST" action="{{ route('settings.save', 'currency') }}" hidden>
            @csrf
            <h3>Currency Settings</h3>
            <div class="grid">
                <div class="f"><label>Symbol</label><input name="currency_symbol" maxlength="5"
                        value="{{ old('currency_symbol', $s['currency_symbol']) }}" required></div>
                <div class="f"><label>Code</label><input name="currency_code" maxlength="5"
                        value="{{ old('currency_code', $s['currency_code']) }}" required></div>
                <div class="f"><label>USD → KHR rate</label>
                    <input type="number" step="0.01" min="1" name="khr_rate" value="{{ old('khr_rate', $s['khr_rate']) }}"
                        required>
                </div>
            </div>
            <button class="btn">Save</button>
        </form>

        {{-- Backup / Restore --}}
        <div class="st-card" data-pane="backup" hidden>
            <h3>Backup Database</h3>
            <p class="hint">ទាញយកឯកសារ .sql នៃទិន្នន័យទាំងអស់។ / Download a full .sql backup.</p>
            <a class="btn" href="{{ route('settings.backup') }}"><i class="fa-solid fa-download"></i> Download backup</a>

            <hr>

            <h3>Restore Database</h3>
            <p class="hint warn">⚠ ទិន្នន័យបច្ចុប្បន្នទាំងអស់នឹងត្រូវជំនួស។ ទាញយក Backup មុន។ / All current data will be
                replaced.</p>
            <form method="POST" action="{{ route('settings.restore') }}" enctype="multipart/form-data"
                onsubmit="return confirm('Restore will REPLACE all current data. Continue?')">
                @csrf
                <div class="grid">
                    <div class="f"><label>Backup file (.sql)</label><input type="file" name="sql_file" accept=".sql,.txt"
                            required></div>
                    <div class="f"><label>Type <b>RESTORE</b> to confirm</label><input name="confirm" autocomplete="off"
                            required></div>
                </div>
                <button class="btn danger">Restore</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/settings.js') }}"></script>
@endpush