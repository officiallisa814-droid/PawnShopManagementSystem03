@extends('layouts.master')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin_css/customer-show.css') }}">
@endpush

@section('content')
@php
    $openContracts = $customer->loanContracts->whereIn('status', \App\Models\LoanContract::OPEN);
    $isActive = strtolower($customer->customer_status) === 'active';
@endphp

<div class="pawn-container">

    {{-- ===== Top bar ===== --}}
    <div class="cs-top">
        <a href="{{ route('customer') }}" class="cs-btn">← <span class="khmer-os">ត្រឡប់ក្រោយ</span> / Back</a>
        <button type="button" class="cs-btn" id="csOpenEdit"><i class="fa-solid fa-pen"></i> Edit customer</button>
    </div>

    @if (session('success')) <div class="cs-alert ok">{{ session('success') }}</div> @endif
    @if (session('error'))   <div class="cs-alert err">{{ session('error') }}</div> @endif

    <div class="cs-grid">

        {{-- ================= LEFT ================= --}}
        <div>
            <div class="cs-card">
                <div class="cs-photo">
                    @if($customer->customer_avatar)
                        <img src="{{ asset('storage/' . $customer->customer_avatar) }}" alt="Avatar">
                    @else
                        <div class="cs-noimg"><i class="fa-solid fa-user-tie"></i></div>
                    @endif
                </div>
                <div class="cs-name khmer-os">{{ $customer->customer_name }}</div>
                <div class="cs-id">ID: #{{ $customer->id }}</div>
                <span class="cs-pill {{ $isActive ? 'p-active' : 'p-inactive' }}">{{ strtoupper($customer->customer_status) }}</span>
            </div>

            <div class="cs-card">
                <h3 class="cs-title"><span class="khmer-os">សង្ខេប</span> / Summary</h3>
                <div class="cs-row"><span class="khmer-os">ប្រភេទ</span><span class="strong">{{ ucfirst($customer->customer_type) }}</span></div>
                <div class="cs-row"><span class="khmer-os">ទំនិញសរុប</span><span class="strong">{{ $customer->pawnItems->count() }}</span></div>
                <div class="cs-row"><span class="khmer-os">កិច្ចសន្យាកំពុងបើក</span><span class="strong">{{ $openContracts->count() }}</span></div>
                <div class="cs-row"><span class="khmer-os">ប្រាក់ដើមកំពុងជំពាក់</span><span class="strong" style="color:#b45309;">${{ number_format($openContracts->sum('loan_amount'), 2) }}</span></div>
            </div>

            <div class="cs-card">
                <h3 class="cs-title"><span class="khmer-os">ឯកសារអត្តសញ្ញាណ</span> / ID Document</h3>
                @if($customer->id_card_photo)
                    <img src="{{ asset('storage/' . $customer->id_card_photo) }}" alt="ID" style="width:100%;border-radius:8px;border:1px solid #e5e7eb;">
                @else
                    <div style="text-align:center;color:#94a3b8;padding:20px;font-size:14px;" class="khmer-os">មិនមានឯកសារ / No document</div>
                @endif
            </div>
        </div>

        {{-- ================= RIGHT ================= --}}
        <div>
            <div class="cs-card">
                <h3 class="cs-title"><span class="khmer-os">ព័ត៌មានផ្ទាល់ខ្លួន</span> / Personal Information</h3>
                <div class="cs-row"><span>Gender</span><span class="khmer-os">{{ $customer->gender == 'M' ? 'ប្រុស (Male)' : 'ស្រី (Female)' }}</span></div>
                <div class="cs-row"><span>Date of birth</span><span>{{ $customer->dob?->format('d/m/Y') ?? '—' }}</span></div>
                <div class="cs-row"><span>ID / Passport</span><span>{{ $customer->passport_id ?? '—' }}</span></div>
                <div class="cs-row"><span>Phone</span><span>{{ $customer->phone_number ?? '—' }}</span></div>
                <div class="cs-row"><span>Email</span><span>{{ $customer->email ?? '—' }}</span></div>
            </div>

            <div class="cs-card">
                <h3 class="cs-title"><span class="khmer-os">អាសយដ្ឋាន</span> / Address</h3>
                <div class="cs-row"><span class="khmer-os">ផ្ទះលេខ / ផ្លូវ</span><span>{{ $customer->address_house ?? '—' }} / {{ $customer->address_street ?? '—' }}</span></div>
                <div class="cs-row"><span class="khmer-os">ភូមិ-ឃុំ-សង្កាត់</span><span>{{ $customer->address_village ?? '—' }}</span></div>
                <div class="cs-row"><span class="khmer-os">ក្រុង-ស្រុក-ខណ្ឌ</span><span>{{ $customer->address_district ?? '—' }}</span></div>
                <div class="cs-row"><span class="khmer-os">ខេត្ត-រាជធានី</span><span>{{ $customer->address_province ?? '—' }}</span></div>
            </div>

            <div class="cs-card">
                <h3 class="cs-title"><span class="khmer-os">អ្នកធានា</span> / Guarantor</h3>
                <div class="cs-row"><span>Name</span><span>{{ $customer->guarantor_name ?? '—' }}</span></div>
                <div class="cs-row"><span>Phone</span><span>{{ $customer->guarantor_phone ?? '—' }}</span></div>
            </div>

            <div class="cs-card">
                <h3 class="cs-title"><span class="khmer-os">ទំនិញបញ្ចាំ</span> / Pawn Items</h3>
                @if($customer->pawnItems->isEmpty())
                    <div style="color:#94a3b8;text-align:center;padding:14px;">No items.</div>
                @else
                    <div class="cs-items">
                        @foreach($customer->pawnItems as $it)
                            <a class="cs-item" href="{{ route('pawnItem.show', $it->id) }}">
                                @if($it->item_photo)
                                    <img src="{{ asset('storage/' . $it->item_photo) }}" alt="">
                                @else
                                    <div class="ni">No Image</div>
                                @endif
                                <div class="t">{{ $it->item_name }}</div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="cs-card">
                <h3 class="cs-title"><span class="khmer-os">កិច្ចសន្យា</span> / Loan Contracts</h3>
                <div class="cs-scroll">
                    <table class="cs-table">
                        <thead><tr><th>Contract</th><th>Item</th><th>Loan</th><th>Rate</th><th>Due</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($customer->loanContracts as $c)
                            <tr>
                                <td><a href="{{ route('loanContract.show', $c->id) }}" style="color:#2563eb;font-weight:700;text-decoration:none;">{{ $c->contract_no }}</a></td>
                                <td>{{ $c->item_name }}</td>
                                <td>${{ number_format($c->loan_amount, 2) }}</td>
                                <td>{{ $c->interest_rate }}% / mo</td>
                                <td>{{ $c->due_date->format('d/m/Y') }}</td>
                                <td><span class="cs-pill s-{{ $c->status }}">{{ ucfirst($c->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align:center;color:#94a3b8;">No contracts.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="cs-card">
                <h3 class="cs-title"><span class="khmer-os">កំណត់ចំណាំ</span> / Notes</h3>
                <div class="cs-note">{{ $customer->notes ?: 'No notes recorded.' }}</div>
            </div>

            <div class="cs-foot khmer-os">
                <span>ចុះឈ្មោះ: {{ $customer->created_at->format('d/m/Y H:i') }}</span>
                <span>កែចុងក្រោយ: {{ $customer->updated_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ================= EDIT MODAL ================= --}}
<div class="cs-overlay {{ $errors->any() ? 'show' : '' }}" id="csModal">
    <div class="cs-modal">
        <div class="cs-mh">
            <h3><span class="khmer-os">កែប្រែព័ត៌មានអតិថិជន</span> / Edit Customer</h3>
            <button type="button" class="cs-mx" id="csClose">&times;</button>
        </div>

        <form action="{{ route('customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="cs-mb">
                @if ($errors->any())
                    <div class="cs-alert err">
                        <strong>សូមពិនិត្យព័ត៌មានខាងក្រោម៖</strong>
                        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="cs-fg">
                    <div class="cs-sec">Personal</div>
                    <div><label>Name *</label><input name="customer_name" value="{{ old('customer_name', $customer->customer_name) }}" required></div>
                    <div><label>Gender *</label>
                        <select name="gender">
                            <option value="M" @selected(old('gender', $customer->gender) == 'M')>ប្រុស / Male</option>
                            <option value="F" @selected(old('gender', $customer->gender) == 'F')>ស្រី / Female</option>
                        </select></div>
                    <div><label>Date of birth *</label><input type="date" name="dob" value="{{ old('dob', $customer->dob?->format('Y-m-d')) }}" required></div>
                    <div><label>ID / Passport *</label><input name="passport_id" value="{{ old('passport_id', $customer->passport_id) }}" required></div>
                    <div><label>Type</label>
                        <select name="customer_type">
                            @foreach(['regular','vip','bad'] as $t)
                                <option value="{{ $t }}" @selected(old('customer_type', $customer->customer_type) == $t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select></div>
                    <div><label>Status</label>
                        <select name="customer_status">
                            @foreach(['active','inactive'] as $s)
                                <option value="{{ $s }}" @selected(old('customer_status', $customer->customer_status) == $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select></div>

                    <div class="cs-sec">Contact & Address</div>
                    <div><label>Phone *</label><input name="phone_number" value="{{ old('phone_number', $customer->phone_number) }}" required></div>
                    <div><label>Email</label><input type="email" name="email" value="{{ old('email', $customer->email) }}"></div>
                    <div><label>House No.</label><input name="address_house" value="{{ old('address_house', $customer->address_house) }}"></div>
                    <div><label>Street</label><input name="address_street" value="{{ old('address_street', $customer->address_street) }}"></div>
                    <div><label>Village-Commune *</label><input name="address_village" value="{{ old('address_village', $customer->address_village) }}" required></div>
                    <div><label>District *</label><input name="address_district" value="{{ old('address_district', $customer->address_district) }}" required></div>
                    <div class="full"><label>Province *</label><input name="address_province" value="{{ old('address_province', $customer->address_province) }}" required></div>

                    <div class="cs-sec">Guarantor</div>
                    <div><label>Guarantor name</label><input name="guarantor_name" value="{{ old('guarantor_name', $customer->guarantor_name) }}"></div>
                    <div><label>Guarantor phone</label><input name="guarantor_phone" value="{{ old('guarantor_phone', $customer->guarantor_phone) }}"></div>

                    <div class="cs-sec">Photos & Notes</div>
                    <div><label>Avatar (ទុកទទេ បើមិនប្តូរ)</label><input type="file" name="customer_avatar" accept="image/*"></div>
                    <div><label>ID card photo (ទុកទទេ បើមិនប្តូរ)</label><input type="file" name="id_card_photo" accept="image/*"></div>
                    <div class="full"><label>Notes</label><textarea name="notes" rows="3">{{ old('notes', $customer->notes) }}</textarea></div>
                </div>
            </div>

            <div class="cs-mf">
                <button type="button" class="cs-cancel" id="csCancel">បោះបង់ / Cancel</button>
                <button type="submit" class="cs-save">រក្សាទុក / Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const m = document.getElementById('csModal');
        const open = () => m.classList.add('show');
        const close = () => m.classList.remove('show');
        document.getElementById('csOpenEdit').onclick = open;
        document.getElementById('csClose').onclick = close;
        document.getElementById('csCancel').onclick = close;
        m.addEventListener('mousedown', e => { if (e.target === m) close(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
    })();
</script>
@endpush