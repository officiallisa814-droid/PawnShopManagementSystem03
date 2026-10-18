@extends('layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin_css/pawnItem-show.css') }}">
@endpush

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;

        $status = strtolower($item->item_status ?? 'active');
        $labels = [
            'active' => ['In Storage', '#def7ec', '#03543f'],
            'redeemed' => ['Redeemed', '#e5e7eb', '#374151'],
            'auctioned' => ['Auctioned', '#fef3c7', '#92400e'],
        ];
        [$label, $bg, $fg] = $labels[$status] ?? [ucfirst($status), '#e5e7eb', '#374151'];
        if ($status === 'active' && $hasOpen) {
            [$label, $bg, $fg] = ['Pledged', '#dbeafe', '#1e40af'];
        }

        // រូបមុខ: ជ្រើសរូបដំបូងដែលមានឯកសារពិត
        $mainExists = $item->item_photo && Storage::disk('public')->exists($item->item_photo);
        $candidates = array_merge([$item->item_photo], $item->photos->pluck('path')->all());
        $mainPhoto = collect($candidates)->first(fn($p) => $p && Storage::disk('public')->exists($p));
    @endphp

    <div class="pawn-container-wrapper">

        {{-- ===== Action buttons ===== --}}
        <div class="pd-actions">
            <a href="{{ route('pawnItem') }}" class="pd-btn">
                ← <span class="khmer-os">ត្រឡប់ក្រោយ</span> / Back
            </a>
            <button type="button" class="pd-btn" id="openEditItem">
                <i class="fa-solid fa-pen"></i> Edit item
            </button>
            @if ($status === 'active' && !$hasOpen)
                <a href="{{ route('loanContract', ['pawn_item' => $item->id]) }}" class="pd-btn blue">
                    <i class="fa-solid fa-file-contract"></i> Create Contract
                </a>
            @endif
        </div>

        @if (session('success'))
            <div
                style="background:#d1fae5;color:#065f46;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-weight:600;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="pd-err">
                @foreach ($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif

        <div class="pd-grid">

            {{-- ================= LEFT ================= --}}
            <div>
                <div class="pd-card">
                    {{-- រូបធំ --}}
                    @if ($mainPhoto)
                        <img id="mainPhoto" class="pd-main-photo" src="{{ asset('storage/' . $mainPhoto) }}" alt="Item">
                    @else
                        <div class="pd-main-photo"
                            style="display:flex;align-items:center;justify-content:center;color:#94a3b8;">
                            <span class="khmer-os">មិនមានរូបថតទំនិញទេ</span>
                        </div>
                    @endif

                    {{-- thumbnails --}}
                    @if ($item->photos->count() || $item->item_photo)
                        <div class="pd-thumbs">
                            {{-- រូបមុខ (លុបបាន ទោះឯកសារបាត់ក៏ដោយ) --}}
                            @if ($item->item_photo)
                                <div class="thumb-wrap">
                                    @if ($mainExists)
                                        <img src="{{ asset('storage/' . $item->item_photo) }}" alt=""
                                            onclick="document.getElementById('mainPhoto') && (document.getElementById('mainPhoto').src=this.src)">
                                    @else
                                        <div
                                            style="width:60px;height:60px;border-radius:8px;border:2px dashed #fca5a5;display:flex;align-items:center;justify-content:center;color:#dc2626;font-size:10px;text-align:center;">
                                            រូបបាត់
                                        </div>
                                    @endif
                                    <form action="{{ route('pawnItems.mainPhoto.destroy', $item->id) }}" method="POST"
                                        onsubmit="return confirm('លុបរូបមុខនេះ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="thumb-del" type="submit">×</button>
                                    </form>
                                </div>
                            @endif

                            {{-- រូបបន្ថែម --}}
                            @foreach ($item->photos as $p)
                                @if (Storage::disk('public')->exists($p->path))
                                    <div class="thumb-wrap">
                                        <img src="{{ asset('storage/' . $p->path) }}" alt=""
                                            onclick="document.getElementById('mainPhoto') && (document.getElementById('mainPhoto').src=this.src)">
                                        <form action="{{ route('pawnItems.photo.destroy', $p->id) }}" method="POST"
                                            onsubmit="return confirm('លុបរូបនេះ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="thumb-del" type="submit">×</button>
                                        </form>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <h2 style="margin:14px 0 2px;color:#0b2240;">{{ $item->item_name }}</h2>
                    <p style="color:#94a3b8;margin:0 0 8px;">ID: #P-{{ $item->id }}</p>
                    <span class="pd-badge" style="background:{{ $bg }};color:{{ $fg }};">{{ $label }}</span>
                </div>

                <div class="pd-card">
                    <h3 class="pd-title"><span class="khmer-os">ម្ចាស់ទំនិញ</span> / Owner</h3>
                    @if ($item->customer)
                        <a class="pd-owner" href="{{ route('customer.show', $item->customer->id) }}">
                            @if ($item->customer->customer_avatar)
                                <img src="{{ asset('storage/' . $item->customer->customer_avatar) }}" alt="">
                            @else
                                <div class="ph"><i class="fa-solid fa-user"></i></div>
                            @endif
                            <div>
                                <strong class="khmer-os">{{ $item->customer->customer_name }}</strong><br>
                                <small style="color:#64748b;">{{ $item->customer->phone_number }}</small>
                            </div>
                        </a>
                    @else
                        <span style="color:#94a3b8;">N/A</span>
                    @endif
                </div>
            </div>

            {{-- ================= RIGHT ================= --}}
            <div>
                <div class="pd-card">
                    <h3 class="pd-title"><span class="khmer-os">ព័ត៌មានទំនិញ</span> / Item Information</h3>
                    <div class="pd-row"><span>Category</span><span>{{ ucfirst($item->category) }}</span></div>
                    <div class="pd-row"><span>IMEI / Serial</span><span>{{ $item->imei_serial_tag ?? '—' }}</span></div>
                    <div class="pd-row"><span>Accessories</span><span>{{ $item->included_accessories ?: '—' }}</span></div>
                    <div class="pd-row"><span>Storage</span><span>{{ $item->storage_location }}</span></div>
                    <div class="pd-row">
                        <span>Received</span><span>{{ optional($item->created_at)->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <div class="pd-card">
                    <h3 class="pd-title"><span class="khmer-os">ការវាយតម្លៃ</span> / Appraisal</h3>
                    <div class="pd-row">
                        <span>Estimated market value</span>
                        <strong>${{ number_format($item->estimated_market_value ?? 0, 2) }}</strong>
                    </div>
                    <div class="pd-row">
                        <span>Max loan ({{ config('pawn.max_loan_percent', 70) }}%)</span>
                        <strong style="color:#16a34a;">${{ number_format($maxLoan, 2) }}</strong>
                    </div>
                </div>

                <div class="pd-card">
                    <h3 class="pd-title"><span class="khmer-os">ស្ថានភាពទំនិញ</span> / Condition</h3>
                    <p style="margin:0;color:#334155;">{{ $item->item_condition_details ?: '—' }}</p>
                </div>

                <div class="pd-card">
                    <h3 class="pd-title"><span class="khmer-os">កិច្ចសន្យាដែលពាក់ព័ន្ធ</span> / Loan Contracts</h3>
                    @if ($item->loanContracts->count())
                        <table class="pd-table">
                            <thead>
                                <tr>
                                    <th>Contract</th>
                                    <th>Loan</th>
                                    <th>Due</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($item->loanContracts->sortByDesc('id') as $c)
                                    <tr>
                                        <td>
                                            <a href="{{ route('loanContract.show', $c->id) }}">
                                                <strong>{{ $c->contract_no }}</strong>
                                            </a>
                                        </td>
                                        <td>${{ number_format($c->loan_amount, 2) }}</td>
                                        <td>{{ $c->due_date->format('d/m/Y') }}</td>
                                        <td>{{ ucfirst($c->status) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p style="color:#94a3b8;margin:0;">មិនទាន់មានកិច្ចសន្យា / No contracts yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ================= EDIT MODAL ================= --}}
    <div class="pd-modal" id="editItemModal">
        <div class="pd-dialog">
            <h3 class="pd-title">Edit item</h3>
            <form action="{{ route('pawnItems.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <label>Category</label>
                <select name="category" required>
                    @foreach (['electronics' => 'Electronics', 'jewelry' => 'Jewelry', 'vehicle' => 'Vehicle'] as $k => $v)
                        <option value="{{ $k }}" {{ old('category', $item->category) == $k ? 'selected' : '' }}>
                            {{ $v }}
                        </option>
                    @endforeach
                </select>

                <label>Item name</label>
                <input type="text" name="item_name" value="{{ old('item_name', $item->item_name) }}" required>

                <label>IMEI / Serial / Tag</label>
                <input type="text" name="serial_number" value="{{ old('serial_number', $item->imei_serial_tag) }}" required>

                <label>Storage location</label>
                <input type="text" name="storage_location" value="{{ old('storage_location', $item->storage_location) }}"
                    required>

                <label>Accessories</label>
                <input type="text" name="accessories" value="{{ old('accessories', $item->included_accessories) }}">

                <label>Estimated market value ($)</label>
                <input type="number" step="0.01" min="0" name="market_value"
                    value="{{ old('market_value', $item->estimated_market_value) }}" required>

                <label>Condition & wear details</label>
                <textarea name="condition_details"
                    rows="3">{{ old('condition_details', $item->item_condition_details) }}</textarea>

                <label>Main photo (ជំនួសរូបមុខ / Replace main photo)</label>
                <input type="file" name="item_photo" accept="image/*">

                <label>Add photos (ជ្រើសបានច្រើន)</label>
                <input type="file" name="photos[]" multiple accept="image/*">

                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
                    <button type="button" class="pd-btn" id="closeEditItem">Cancel</button>
                    <button type="submit" class="pd-btn blue">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var m = document.getElementById('editItemModal');
            document.getElementById('openEditItem').onclick = function () { m.classList.add('show'); };
            document.getElementById('closeEditItem').onclick = function () { m.classList.remove('show'); };
            m.addEventListener('mousedown', function (e) { if (e.target === m) m.classList.remove('show'); });
            @if ($errors->any())
                m.classList.add('show');
            @endif
            })();
    </script>
@endsection