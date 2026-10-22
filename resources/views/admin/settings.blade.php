@extends('layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin_css/settings.css') }}">
@endpush

@section('content')
    <div id="st-app" data-tab="{{ session('tab', 'shop') }}">
        <div class="st-head">
            <h1>Settings <small class="khmer-os">ការកំណត់</small></h1>
        </div>

        @if (session('success'))
        <div class="st-alert ok">{{ session('success') }}</div> @endif
        @if ($errors->any())
            <div class="st-alert err">@foreach ($errors->all() as $e)
            <div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <div class="st-layout">
            <div class="st-tabs">
                <button type="button" class="tab" data-tab="shop"><i class="fa-solid fa-store"></i> Shop Info</button>
                <button type="button" class="tab" data-tab="interest"><i class="fa-solid fa-percent"></i> Interest</button>
                <button type="button" class="tab" data-tab="tax"><i class="fa-solid fa-receipt"></i> Tax</button>
                <button type="button" class="tab" data-tab="currency"><i class="fa-solid fa-coins"></i> Currency</button>
                <button type="button" class="tab" data-tab="backup"><i class="fa-solid fa-database"></i> Backup /
                    Restore</button>
            </div>

            <div>
                {{-- ===== Shop ===== --}}
                <form class="st-card js-save" data-pane="shop" method="POST" action="{{ route('settings.save', 'shop') }}">
                    @csrf
                    <div class="st-card-head">
                        <h3><span class="khmer-os">ព័ត៌មានហាង</span> </h3>
                        <p>បង្ហាញក្នុងក្រដាសកិច្ចសន្យា និងរបាយការណ៍</p>
                    </div>
                    <div class="st-card-body">
                        <div class="grid">
                            <div class="f"><label>ឈ្មោះហាង <span class="km">Shop name *</span></label>
                                <input name="shop_name" value="{{ old('shop_name', $s['shop_name'] ?? '') }}" required>
                            </div>
                            <div class="f"><label>ទូរស័ព្ទ <span class="km">Phone</span></label>
                                <input name="shop_phone" value="{{ old('shop_phone', $s['shop_phone'] ?? '') }}"
                                    placeholder="012 345 678">
                            </div>
                            <div class="f"><label>អ៊ីមែល <span class="km">Email</span></label>
                                <input type="email" name="shop_email"
                                    value="{{ old('shop_email', $s['shop_email'] ?? '') }}" placeholder="shop@example.com">
                            </div>
                            <div class="f full"><label>អាសយដ្ឋាន <span class="km">Address</span></label>
                                <input name="shop_address" value="{{ old('shop_address', $s['shop_address'] ?? '') }}"
                                    placeholder="ផ្ទះលេខ, ផ្លូវ, ខណ្ឌ, ខេត្ត">
                            </div>
                        </div>
                    </div>
                    <div class="st-card-foot"><button class="btn"><i class="fa-solid fa-floppy-disk"></i> រក្សាទុក /
                            Save</button></div>
                </form>

                {{-- ===== Interest ===== --}}
                <form class="st-card js-save" data-pane="interest" method="POST"
                    action="{{ route('settings.save', 'interest') }}" hidden>
                    @csrf
                    <div class="st-card-head">
                        <h3><span class="khmer-os">អត្រាការប្រាក់</span> / Interest Configuration</h3>
                        <p>តម្លៃលំនាំដើមសម្រាប់ Loan Contract ថ្មី / Defaults for new contracts</p>
                    </div>
                    <div class="st-card-body">
                        <div class="grid">
                            <div class="f"><label>ការប្រាក់ <span class="km">Default interest</span></label>
                                <div class="unit"><input type="number" step="0.01" min="0" max="100" name="default_rate"
                                        id="in_rate" value="{{ old('default_rate', $s['default_rate']) }}" required><span>%
                                        / ខែ</span></div>
                            </div>
                            <div class="f"><label>រយៈពេល <span class="km">Default term</span></label>
                                <div class="unit"><input type="number" min="1" max="60" name="default_term"
                                        value="{{ old('default_term', $s['default_term']) }}" required><span>ខែ</span></div>
                            </div>
                            <div class="f full"><label>ប្រាក់កម្ចីអតិបរមា <span class="km">Max loan</span></label>
                                <div class="unit"><input type="number" step="0.01" min="1" max="100" name="max_loan_percent"
                                        id="in_max" value="{{ old('max_loan_percent', $s['max_loan_percent']) }}"
                                        required><span>% តម្លៃទីផ្សារ</span></div>
                                <span class="hint-s">ឧ. 70% មានន័យថា ទំនិញតម្លៃ $1,000 ខ្ចីបានអតិបរមា $700</span>
                            </div>
                        </div>
                        <div class="example" id="in_example"></div>
                    </div>
                    <div class="st-card-foot"><button class="btn"><i class="fa-solid fa-floppy-disk"></i> រក្សាទុក /
                            Save</button></div>
                </form>

                {{-- ===== Tax ===== --}}
                <form class="st-card js-save" data-pane="tax" method="POST" action="{{ route('settings.save', 'tax') }}"
                    hidden>
                    @csrf
                    <div class="st-card-head">
                        <h3><span class="khmer-os">ពន្ធ</span> / Tax Configuration</h3>
                        <p>បើមិនប្រើពន្ធ សូមបិទ / Turn off if you do not charge tax</p>
                    </div>
                    <div class="st-card-body">
                        <div class="grid">
                            <div class="switch-row">
                                <div class="t"><b>បើកប្រើពន្ធ / Enable tax</b><span>ពេលបិទ ពន្ធមិនត្រូវបានគណនា</span></div>
                                <label class="switch"><input type="checkbox" name="tax_enabled" value="1" {{ ($s['tax_enabled'] ?? '0') == '1' ? 'checked' : '' }}><span class="sl"></span></label>
                            </div>
                            <div class="f"><label>ឈ្មោះពន្ធ <span class="km">Tax name</span></label>
                                <input name="tax_name" value="{{ old('tax_name', $s['tax_name']) }}" placeholder="VAT"
                                    required>
                            </div>
                            <div class="f"><label>អត្រាពន្ធ <span class="km">Tax rate</span></label>
                                <div class="unit"><input type="number" step="0.01" min="0" max="100" name="tax_rate"
                                        value="{{ old('tax_rate', $s['tax_rate']) }}" required><span>%</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="st-card-foot"><button class="btn"><i class="fa-solid fa-floppy-disk"></i> រក្សាទុក /
                            Save</button></div>
                </form>

                {{-- ===== Currency ===== --}}
                <form class="st-card js-save" data-pane="currency" method="POST"
                    action="{{ route('settings.save', 'currency') }}" hidden>
                    @csrf
                    <div class="st-card-head">
                        <h3><span class="khmer-os">រូបិយប័ណ្ណ</span> / Currency Settings</h3>
                        <p>អត្រាប្តូរប្រាក់ USD ទៅ រៀល / USD to Riel exchange rate</p>
                    </div>
                    <div class="st-card-body">
                        <div class="grid">
                            <div class="f"><label>និមិត្តសញ្ញា <span class="km">Symbol</span></label>
                                <input name="currency_symbol" maxlength="5"
                                    value="{{ old('currency_symbol', $s['currency_symbol']) }}" placeholder="$" required>
                            </div>
                            <div class="f"><label>កូដ <span class="km">Code</span></label>
                                <input name="currency_code" maxlength="5"
                                    value="{{ old('currency_code', $s['currency_code']) }}" placeholder="USD" required>
                            </div>
                            <div class="f full"><label>អត្រា USD → KHR</label>
                                <div class="unit"><input type="number" step="0.01" min="1" name="khr_rate" id="cu_rate"
                                        value="{{ old('khr_rate', $s['khr_rate']) }}" required><span>៛ / $1</span></div>
                            </div>
                        </div>
                        <div class="example" id="cu_example"></div>
                    </div>
                    <div class="st-card-foot"><button class="btn"><i class="fa-solid fa-floppy-disk"></i> រក្សាទុក /
                            Save</button></div>
                </form>

                {{-- ===== Backup / Restore ===== --}}
                <div class="st-card" data-pane="backup" hidden>
                    <div class="st-card-head">
                        <h3><span class="khmer-os">បម្រុងទុក និងស្ដារ</span> / Backup & Restore</h3>
                        <p>ការពារទិន្នន័យរបស់អ្នក / Protect your data</p>
                    </div>
                    <div class="st-card-body">
                        <div class="bk-box">
                            <h4><i class="fa-solid fa-download"></i> Backup Database</h4>
                            <p class="hint">ទាញយកឯកសារ .sql នៃទិន្នន័យទាំងអស់។ ត្រូវធ្វើជាប្រចាំ / Download a full .sql
                                backup.</p>
                            <a class="btn" href="{{ route('settings.backup') }}"><i class="fa-solid fa-download"></i>
                                Download backup</a>
                        </div>

                        <div class="bk-box warn">
                            <h4><i class="fa-solid fa-triangle-exclamation"></i> Restore Database</h4>
                            <p class="hint warn">⚠ ទិន្នន័យបច្ចុប្បន្នទាំងអស់នឹងត្រូវជំនួស។ ទាញយក Backup មុន។ / All current
                                data will be replaced.</p>
                            <form method="POST" action="{{ route('settings.restore') }}" enctype="multipart/form-data"
                                onsubmit="return confirm('Restore will REPLACE all current data. Continue?')">
                                @csrf
                                <div class="grid" style="margin-bottom:14px;">
                                    <div class="f"><label>ឯកសារ Backup <span class="km">(.sql)</span></label>
                                        <input type="file" name="sql_file" accept=".sql,.txt" required>
                                    </div>
                                    <div class="f"><label>វាយ <b>RESTORE</b> ដើម្បីបញ្ជាក់</label>
                                        <input name="confirm" autocomplete="off" required>
                                    </div>
                                </div>
                                <button class="btn danger"><i class="fa-solid fa-rotate-left"></i> Restore</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/settings.js') }}"></script>
@endpush