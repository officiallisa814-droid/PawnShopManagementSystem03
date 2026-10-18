<!--Sidebar & Header -->
@extends('layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin_css/pawnItem.css') }}">
@endpush

@section('content')
    <div class="pawn-container">

        <!-- ============ HEADER + ADD BUTTON ============ -->
        <div class="pawn-page-header">
            <h2 class="pawn-card-title" style="margin:0;">
                <span class="khmer-os">ទំនិញបញ្ចាំ</span> / Pawn Items
            </h2>
            <button type="button" class="submit-btn" id="openPawnModalBtn">
                <i class="fa-solid fa-plus"></i>
                <span class="khmer-os">បន្ថែមទំនិញបញ្ចាំ</span> / Add Pawn Item
            </button>
        </div>

        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <!-- ============ MODAL FORM ============ -->
        <div class="pawn-modal-overlay" id="pawnModal">
            <div class="pawn-modal-card">
                <div class="pawn-modal-header">
                    <h3><span class="khmer-os">បន្ថែមទំនិញបញ្ចាំថ្មី</span> / Add New Pawn Item</h3>
                    <button type="button" class="pawn-modal-close" id="closePawnModalBtn">&times;</button>
                </div>

                <form id="pawnForm" novalidate action="{{ route('pawnItems.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="pawn-modal-body">
                        @if ($errors->any())
                            <div class="alert-error">
                                <strong class="khmer-os">សូមពិនិត្យព័ត៌មានខាងក្រោម៖</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Section 1: Item identification -->
                        <div class="pawn-section-title">
                            <span class="khmer-os">អត្តសញ្ញាណទំនិញ</span> / Item Identification
                        </div>
                        <div class="pawn-form-grid">
                            <div class="form-group dropdown-anchor" style="position: relative;">
                                <label>
                                    ឈ្មោះអតិថិជន ឬ លេខសម្គាល់ / Customer Name / User ID
                                    <span class="required" style="color:red;">*</span>
                                </label>
                                <input type="text" id="customer_search" autocomplete="off"
                                    placeholder="វាយឈ្មោះ ឬ ID អតិថិជន... / Type name or ID..." class="form-control">
                                <input type="hidden" name="customer_id" id="selected_customer_id"
                                    value="{{ old('customer_id') }}">
                                <div id="search_results_dropdown" class="search-results-dropdown" style="display: none;">
                                </div>
                                @error('customer_id')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>
                                    <span class="khmer-os">ប្រភេទជម្រើស</span> / Category
                                    <span class="required">*</span>
                                </label>
                                <select name="category" required class="@error('category') input-error @enderror">
                                    <option value="" disabled {{ old('category') ? '' : 'selected' }}>
                                        Select item category
                                    </option>
                                    <option value="electronics" {{ old('category') == 'electronics' ? 'selected' : '' }}>
                                        Electronics / គ្រឿងអេឡិចត្រូនិច
                                    </option>
                                    <option value="jewelry" {{ old('category') == 'jewelry' ? 'selected' : '' }}>
                                        Jewelry / គ្រឿងអលង្ការ
                                    </option>
                                    <option value="vehicle" {{ old('category') == 'vehicle' ? 'selected' : '' }}>
                                        Vehicle / យានយន្ត
                                    </option>
                                </select>
                                @error('category')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>
                                    <span class="khmer-os">ឈ្មោះទំនិញ</span> / Item Name
                                    <span class="required">*</span>
                                </label>
                                <input type="text" name="item_name" value="{{ old('item_name') }}"
                                    class="@error('item_name') input-error @enderror"
                                    placeholder="Enter item name (e.g. iPhone 15 Pro Max)" required>
                                @error('item_name')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>
                                    <span class="khmer-os">លេខសៀរៀល ឬ លេខកូដ</span> / IMEI / Serial / Tag
                                    <span class="required">*</span>
                                </label>
                                <input type="text" name="serial_number" value="{{ old('serial_number') }}"
                                    class="@error('serial_number') input-error @enderror"
                                    placeholder="Enter IMEI, Serial Number, or Asset Tag" required>
                                @error('serial_number')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Section 2: Appraisal -->
                        <div class="pawn-section-title">
                            <span class="khmer-os">ការវាយតម្លៃ និងរក្សាទុក</span> / Appraisal & Storage
                        </div>
                        <div class="pawn-form-grid">
                            <div class="form-group">
                                <label>
                                    <span class="khmer-os">តម្លៃទីផ្សារប៉ាន់ស្មាន ($)</span> / Estimated Market Value ($)
                                    <span class="required">*</span>
                                </label>
                                <input type="number" step="0.01" min="0" name="market_value"
                                    value="{{ old('market_value') }}" class="@error('market_value') input-error @enderror"
                                    placeholder="Enter estimated market value in USD" required>
                                @error('market_value')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>
                                    <span class="khmer-os">គ្រឿងបន្លាស់រួមបញ្ចូល</span> / Included Accessories
                                    <span class="required">*</span>
                                </label>
                                <input type="text" name="accessories" value="{{ old('accessories') }}"
                                    class="@error('accessories') input-error @enderror"
                                    placeholder="បញ្ចូលគ្រឿងបន្លាស់ (ឧ. ប្រអប់, ដុំសាក, វិក្កយបត្រ)" required>
                                @error('accessories')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>
                                    <span class="khmer-os">ទីតាំងរក្សាទុក</span> / Storage / Safe Location
                                    <span class="required">*</span>
                                </label>
                                <input type="text" name="storage_location" value="{{ old('storage_location') }}"
                                    class="@error('storage_location') input-error @enderror"
                                    placeholder="e.g. Safe A - Box 5" required>
                                @error('storage_location')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Section 3: Photo & condition -->
                        <div class="pawn-section-title">
                            <span class="khmer-os">រូបថត និងស្ថានភាព</span> / Photo & Condition
                        </div>
                        <div class="pawn-form-grid">
                            <div class="form-group">
                                <label><span class="khmer-os">រូបថតទំនិញ</span> / Item Photo Upload</label>
                                <input type="file" name="item_photo" class="file-input" accept="image/*">
                                @error('item_photo')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group full-width">
                                <label>
                                    <span class="khmer-os">ព័ត៌មានលម្អិតអំពីស្ថានភាពទំនិញ</span> /
                                    Item Condition & Wear Details
                                </label>
                                <textarea name="condition_details" rows="3"
                                    placeholder="e.g., 95% condition, slight scratches on screen, battery health 90%">{{ old('condition_details') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="pawn-modal-actions">
                        <button type="button" class="btn-cancel" id="cancelPawnModalBtn">
                            <span class="khmer-os">បោះបង់</span> / Cancel
                        </button>
                        <button type="submit" class="submit-btn">
                            <span class="khmer-os">រក្សាទុក</span> / Save Pawn Item
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============ INVENTORY TABLE ============ -->
        <div class="pawn-card table-card">
            <div class="inventory-header">
                <h2 class="pawn-card-title">
                    <span class="khmer-os">បញ្ជីទំនិញបញ្ចាំ</span> / Pawned Items Inventory
                </h2>

                <div class="inventory-filters">
                    <select id="category_filter">
                        <option value="all">All Categories</option>
                        <option value="electronics">Electronics</option>
                        <option value="jewelry">Jewelry</option>
                        <option value="vehicle">Vehicle</option>
                    </select>

                    <select id="item_status_filter">
                        <option value="all">All Status</option>
                        <option value="active">In Storage</option>
                        <option value="redeemed">Redeemed</option>
                        <option value="auctioned">Auctioned</option>
                    </select>

                    <div class="search-wrapper">
                        <input type="search" id="table_search_input" placeholder="Search Item..." class="cool-search-input">
                        <span class="search-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                    </div>
                </div>
            </div>

            <div class="responsive-table-wrapper">
                <table class="inventory-table" id="inventory_data_table">
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Photo</th>
                            <th>Customer</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>IMEI / Serial</th>
                            <th>Storage</th>
                            <th>Market Value</th>
                            <th>Status</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pawnItems as $item)
                            @php
                                $status = strtolower($item->item_status ?? ($item->status ?? 'active'));
                                $statusLabels = [
                                    'active' => ['In Storage', '#def7ec', '#03543f'],
                                    'redeemed' => ['Redeemed', '#e5e7eb', '#374151'],
                                    'auctioned' => ['Auctioned', '#fef3c7', '#92400e'],
                                    'sold' => ['Sold', '#dbeafe', '#1e40af'],
                                ];
                                [$label, $bg, $fg] = $statusLabels[$status] ?? [
                                    ucfirst($status),
                                    '#e5e7eb',
                                    '#374151',
                                ];
                            @endphp

                            <tr onclick="window.location='{{ route('pawnItem.show', $item->id) }}'" style="cursor: pointer;"
                                data-category="{{ strtolower($item->category) }}" data-status="{{ $status }}">
                                <td>#{{ $item->id }}</td>

                                <td>
                                    @if ($item->item_photo)
                                        <img src="{{ asset('storage/' . $item->item_photo) }}" alt=""
                                            style="width: 40px; height: 40px; border-radius: 4px; object-fit: cover;">
                                    @else
                                        <span style="color: #94a3b8; font-size: 12px;">No Image</span>
                                    @endif
                                </td>

                                <td onclick="event.stopPropagation();">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        @if ($item->customer && $item->customer->customer_avatar)
                                            <img src="{{ asset('storage/' . $item->customer->customer_avatar) }}" alt="Avatar"
                                                style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; border: 1px solid #cbd5e1;">
                                        @else
                                            <div
                                                style="width: 30px; height: 30px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #64748b;">
                                                <i class="fa-solid fa-user"></i>
                                            </div>
                                        @endif
                                        <span class="khmer-os customer-name-td" style="font-weight: 600;">
                                            {{ $item->customer->customer_name ?? 'N/A' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="item-name-td" style="color: #2563eb; font-weight: 600;">
                                    {{ $item->item_name }}
                                </td>
                                <td>{{ ucfirst($item->category) }}</td>
                                <td>{{ $item->imei_serial_tag ?? $item->serial_number }}</td>
                                <td>{{ $item->storage_location }}</td>
                                <td style="font-weight: 600;">
                                    ${{ number_format($item->estimated_market_value ?? 0, 2) }}
                                </td>
                                <td>
                                    <span
                                        style="padding: 4px 10px; border-radius: 12px; font-size: 12px; background-color: {{ $bg }}; color: {{ $fg }}; font-weight: bold;">
                                        {{ $label }}
                                    </span>
                                </td>
                                <td onclick="event.stopPropagation();" style="text-align:center; white-space:nowrap;">
                                    <a href="{{ route('pawnItem.show', $item->id) }}" class="row-btn row-btn-view" title="View">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @if ($status === 'active')
                                        <a href="{{ route('loanContract', ['pawn_item' => $item->id]) }}"
                                            class="row-btn row-btn-contract" title="Create Contract">
                                            <i class="fa-solid fa-file-contract"></i> Contract
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="10" style="text-align: center; color: #94a3b8; padding: 40px 20px;">
                                    <span class="khmer-os">
                                        មិនទាន់មានទិន្នន័យនៅឡើយទេ។ ចុច "បន្ថែមទំនិញបញ្ចាំ" ដើម្បីបន្ថែម។
                                    </span><br>
                                    No items added yet.
                                </td>
                            </tr>
                        @endforelse

                        <tr id="pawn-no-result" style="display:none;">
                            <td colspan="10" style="text-align:center; color:#94a3b8; padding:30px;">
                                រកមិនឃើញទំនិញ / No matching items.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/pawnitem.js') }}"></script>
    <script src="{{ asset('js/imageResize.js') }}"></script>
@endpush