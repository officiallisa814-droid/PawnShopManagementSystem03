<!-- Sidebar & Header -->
@extends('layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin_css/customer.css') }}">
@endpush

<!-- put code  @yield('content')  -->
@section('content')

    <div class="customer-container">

        <!-- 1. Top Section: Title & Main Action Buttons -->
        <div class="directory-header">
            <h2 class="directory-title">Customer Directory</h2>
            <div class="action-buttons">
                <button type="button" class="btn btn-add"><i class="fa-solid fa-plus"></i> Add New Customer</button>
                <button class="btn btn-view"><i class="fa-solid fa-user-slash"></i> View Blacklist</button>
                <button type="button" class="btn btn-export" id="exportBtn"><i class="fa-solid fa-download"></i> Export
                    Report (.csv)</button>
            </div>
        </div>

        <!-- 2. Main Content Card -->
        <div class="directory-card">

            <!-- Filter and Search Row -->
            <div class="filter-section">
                <span class="filter-title">Customer Directory</span>
                <p class="filter-subtitle">Search Affairs/Queries, contact numbers, extra, Pledges</p>

                <div class="filter-controls">
                    <div class="input-wrapper">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="search" id="customer_search_input" placeholder="Search...">
                    </div>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-location-dot search-icon"></i>
                        <input type="text" id="province_filter_input" placeholder="Province e.g. Phnom Penh"
                            list="province_list" autocomplete="off">
                        <datalist id="province_list">
                            @foreach ($customers->pluck('address_province')->filter()->unique()->sort() as $province)
                                <option value="{{ $province }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div class="layer-filter-wrapper">
                        <button type="button" class="layer-filter-btn" id="layer_filter_btn">
                            <i class="fa-solid fa-sliders"></i> Layer Filters
                            <i class="fa-solid fa-chevron-down layer-chevron"></i>
                        </button>

                        <!-- ជម្រើសលោតចុះមកពេលចុចប៊ូតុង -->
                        <div class="layer-dropdown" id="layer_dropdown">
                            <div class="layer-field">
                                <select id="status_filter_select">
                                    <option value="all">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Customer Data Table -->
            <div class="table-title">Customer List</div>
            <div class="table-responsive">
                @if (session('success'))
                    <div class="alert-success">{{ session('success') }}</div>
                @endif
                <table class="customer-table">
                    <thead>
                        <tr style="background-color: #0b2240;">
                            <th style="width: 40px;"></th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>ID/Passport</th>
                            <th>Phone</th>
                            <th style="text-align: center;">Active Pledges</th>
                            <th style="text-align: center;">Status</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="customer-table-body">
                        @forelse($customers as $customer)
                            @php
                                $exportRow = [
                                    $customer->id,
                                    $customer->customer_name,
                                    $customer->gender == 'M' ? 'Male' : 'Female',
                                    optional($customer->dob)->format('d/m/Y'),
                                    $customer->passport_id,
                                    $customer->phone_number,
                                    $customer->email,
                                    $customer->address_house,
                                    $customer->address_street,
                                    $customer->address_village,
                                    $customer->address_district,
                                    $customer->address_province,
                                    $customer->guarantor_name,
                                    $customer->guarantor_phone,
                                    ucfirst($customer->customer_type),
                                    ucfirst($customer->customer_status),
                                    $customer->pawnItems->count(),
                                ];
                            @endphp
                            <tr onclick="window.location='{{ route('customer.show', $customer->id) }}'" style="cursor: pointer;"
                                data-province="{{ strtolower($customer->address_province ?? '') }}"
                                data-status="{{ strtolower($customer->customer_status ?? '') }}" data-index="{{ $loop->index }}"
                                data-export="{{ json_encode($exportRow, JSON_UNESCAPED_UNICODE) }}">
                                <td onclick="event.stopPropagation();"><input type="checkbox" value="{{ $customer->id }}"></td>
                                <td class="id-col">{{ $customer->id }}</td>

                                <!-- Name Column with Dynamic Profile Picture -->
                                <td class="name-col">
                                    <div style="display: inline-flex; align-items: center; gap: 10px;">
                                        @if($customer->customer_avatar)
                                            <img src="{{ asset('storage/' . $customer->customer_avatar) }}"
                                                style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 1px solid #e5e7eb;">
                                        @else
                                            <div
                                                style="width: 35px; height: 35px; border-radius: 50%; background-color: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 14px; color: #6b7280; font-weight: bold;">
                                                <i class="fa-solid fa-user"></i>
                                            </div>
                                        @endif
                                        <span style="color: #2563eb; font-weight: 600;">{{ $customer->customer_name }}</span>
                                        <!-- <span class="sub-id">{{ $customer->passport_id ?? '' }}</span> -->
                                    </div>
                                </td>



                                <td>{{ $customer->passport_id ?? 'N/A' }}</td>
                                <td>{{ $customer->phone_number }}</td>
                                <td class="pledge-count" style="text-align: center;">{{ $customer->pawnItems->count() }}</td>
                                <td class="text-center">
                                    @if(strtolower($customer->customer_status) == 'active')
                                        <span class="badge active-badge">Active</span>
                                    @else
                                        <span class="badge inactive-badge">{{ ucfirst($customer->customer_status) }}</span>
                                    @endif
                                </td>
                                <!-- <td class="action-cell" onclick="event.stopPropagation();"><i class="fa-solid fa-ellipsis"></i>
                                                                                                                                </td> -->
                                <!-- ======== Prarents icon (...)========= -->
                                <td class="action-cell" onclick="event.stopPropagation();">
                                    <!-- ប៊ូតុងមេ Ellipsis (...) នឹងប្តូរមកជាជួរប៊ូតុងបញ្ជាទន្ទឹមគ្នាភ្លាមៗពេល Hover -->
                                    <span class="action-trigger-btn"><i class="fa-solid fa-ellipsis"></i></span>

                                    <!-- ផ្ទាំងបញ្ជាដែលមានតែ Icons ពីរទន្ទឹមគ្នា ស្រស់ស្អាត និងទំនើបបំផុត -->
                                    <div class="action-popover-menu horizontal-icons">
                                        <!--  ២. ប៊ូតុងលុប (Delete Icon) -->
                                        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST"
                                            onsubmit="return confirm('⚠️ តើអ្នកពិតជាចង់លុបអតិថិជននេះមែនទេ? រាល់ទិន្នន័យបញ្ចាំរបស់គាត់នឹងត្រូវបាត់បង់!');"
                                            style="margin: 0; display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-only-btn delete-btn-icon" title="លុប / Delete">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="8" style="text-align: center; padding: 20px; color: #888;">
                                    មិនមានទិន្នន័យអតិថិជនទេ / No customers found.
                                </td>
                            </tr>
                        @endforelse

                        <!-- បង្ហាញពេលស្វែងរក/ត្រងហើយរកមិនឃើញ -->
                        <tr id="no-result-row" style="display: none;">
                            <td colspan="8" style="text-align: center; padding: 20px; color: #888;">
                                រកមិនឃើញអតិថិជន / No matching customers.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>








        <!-- =========================================== Pup up ================================================== -->
        <!-- Customer Form Popup Modal Overlay -->
        <div class="modal-overlay" id="customerModal">
            <div class="modal-card">

                <!-- Modal Head -->
                <div class="modal-header">
                    <h3><i class="fa-solid fa-user-plus"></i> <span class="khmer-os">បន្ថែមព័ត៌មានអតិថិជនថ្មី</span> / Add
                        New Customer</h3>
                    <button class="close-modal-btn" id="closeModalBtn">&times;</button>
                </div>

                <!-- Modal Form Body Inputs -->
                <!-- add enctype="multipart/form-data" for aprove Upload picture or file -->
                <form id="customerMainForm" class="modal-form" action="{{ route('customers.store') }}" method="POST"
                    enctype="multipart/form-data" novalidate>
                    @csrf
                    <!-- it know both Create new and update customer -->
                    <input type="hidden" name="_method" id="form_method_override" value="POST">

                    @if ($errors->any())
                        <div class="alert-error">
                            <strong class="khmer-os">សូមពិនិត្យព័ត៌មានខាងក្រោម៖</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li class="khmer-os">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-scroll-container">

                        <!-- Section 1 -->
                        <div class="form-section-title"><i class="fa-solid fa-address-card"></i>
                            <span class="khmer-os">ព័ត៌មានផ្ទាល់ខ្លួន</span> / Personal Information
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="customer_name"><span class="khmer-os">ឈ្មោះអតិថិជន</span> / Customer
                                    Name</label>
                                <input type="text" id="customer_name" name="customer_name" placeholder="Enter full name"
                                    value="{{ old('customer_name') }}" class="@error('customer_name') input-error @enderror"
                                    required>
                                @error('customer_name')<span class="error-message khmer-os">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="gender"><span class="khmer-os">ភេទ</span> / Gender</label>
                                <select id="gender" name="gender" class="khmer-os @error('gender') input-error @enderror"
                                    required>
                                    <option value="">ជ្រើសរើស / Select</option>
                                    <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>ប្រុស / Male</option>
                                    <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>ស្រី / Female</option>
                                </select>
                                @error('gender')<span class="error-message khmer-os">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="dob"><span class="khmer-os">ថ្ងៃខែឆ្នាំកំណើត</span> / Date of Birth</label>
                                <input type="date" id="dob" name="dob" value="{{ old('dob') }}"
                                    class="@error('dob') input-error @enderror" required>
                                @error('dob')<span class="error-message khmer-os">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="passport_id"><span class="khmer-os">អត្តសញ្ញាណប័ណ្ណ ឬ លិខិតឆ្លងដែន</span> / ID
                                    or Passport</label>
                                <input type="text" id="passport_id" name="passport_id" placeholder="e.g. CUST004"
                                    value="{{ old('passport_id') }}" class="@error('passport_id') input-error @enderror"
                                    required>
                                @error('passport_id')<span class="error-message khmer-os">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="phone_number"><span class="khmer-os">លេខទូរស័ព្ទ</span> / Phone Number</label>
                                <input type="text" id="phone_number" name="phone_number" placeholder="e.g. (097) 1234567"
                                    value="{{ old('phone_number') }}" class="@error('phone_number') input-error @enderror"
                                    required>
                                @error('phone_number')<span class="error-message khmer-os">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="email"><span class="khmer-os">អ៊ីមែល</span> / Email Address</label>
                                <input type="email" id="email" name="email" placeholder="e.g. client@example.com"
                                    value="{{ old('email') }}" class="@error('email') input-error @enderror">
                                @error('email')<span class="error-message khmer-os">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <!-- Section 2 -->
                        <div class="form-section-title"><i class="fa-solid fa-map-location-dot"></i>
                            <span class="khmer-os">អាសយដ្ឋានបច្ចុប្បន្ន</span> / Current Address
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="address_house"><span class="khmer-os">ផ្ទះលេខ</span> / House No.</label>
                                <input type="text" id="address_house" name="address_house" placeholder="e.g. St 12A"
                                    value="{{ old('address_house') }}">
                            </div>

                            <div class="form-group">
                                <label for="address_street"><span class="khmer-os">ផ្លូវ</span> / Street No.</label>
                                <input type="text" id="address_street" name="address_street" placeholder="e.g. 271"
                                    value="{{ old('address_street') }}">
                            </div>

                            <div class="form-group">
                                <label for="address_village"><span class="khmer-os">ភូមិ-ឃុំ-សង្កាត់</span> /
                                    Village-Commune</label>
                                <input type="text" id="address_village" name="address_village"
                                    placeholder="e.g. Boeung Keng Kang" value="{{ old('address_village') }}"
                                    class="@error('address_village') input-error @enderror" required>
                                @error('address_village')<span class="error-message khmer-os">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="address_district"><span class="khmer-os">ក្រុង-ស្រុក-ខណ្ឌ</span> /
                                    District</label>
                                <input type="text" id="address_district" name="address_district"
                                    placeholder="e.g. Chamkar Mon" value="{{ old('address_district') }}"
                                    class="@error('address_district') input-error @enderror" required>
                                @error('address_district')<span
                                class="error-message khmer-os">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group full-width">
                                <label for="address_province"><span class="khmer-os">ខេត្ត-រាជធានី</span> /
                                    Province-Capital</label>
                                <input type="text" id="address_province" name="address_province"
                                    placeholder="e.g. Phnom Penh" value="{{ old('address_province') }}"
                                    class="@error('address_province') input-error @enderror" required>
                                @error('address_province')<span
                                class="error-message khmer-os">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <!-- Section 3 -->
                        <div class="form-section-title"><i class="fa-solid fa-shield-halved"></i>
                            <span class="khmer-os">អ្នកធានា ឬ ទំនាក់ទំនងបន្ទាន់</span> / Emergency Contact
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="guarantor_name"><span class="khmer-os">ឈ្មោះអ្នកធានា</span> / Guarantor
                                    Name</label>
                                <input type="text" id="guarantor_name" name="guarantor_name"
                                    placeholder="Enter guarantor name" value="{{ old('guarantor_name') }}">
                            </div>

                            <div class="form-group">
                                <label for="guarantor_phone"><span class="khmer-os">លេខទូរស័ព្ទអ្នកធានា</span> / Guarantor
                                    Phone</label>
                                <input type="text" id="guarantor_phone" name="guarantor_phone"
                                    placeholder="e.g. 012 345 678" value="{{ old('guarantor_phone') }}">
                            </div>
                        </div>

                        <!-- Section 4 -->
                        <div class="form-section-title"><i class="fa-solid fa-sliders"></i>
                            <span class="khmer-os">ប្រព័ន្ធគ្រប់គ្រង និងឯកសារស្កែន</span> / System & Documents
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="active_pledges"><span class="khmer-os">ចំនួនបញ្ចាំសកម្ម</span> / Active
                                    Pledges</label>
                                <input type="number" id="active_pledges" name="active_pledges" placeholder="0" min="0"
                                    value="{{ old('active_pledges', 0) }}"
                                    class="@error('active_pledges') input-error @enderror">
                                @error('active_pledges')<span class="error-message khmer-os">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="customer_type"><span class="khmer-os">ចំណាត់ថ្នាក់អតិថិជន</span> / Customer
                                    Category</label>
                                <select id="customer_type" name="customer_type" class="khmer-os">
                                    <option value="regular" {{ old('customer_type', 'regular') == 'regular' ? 'selected' : '' }}>អតិថិជនទូទៅ / Regular</option>
                                    <option value="vip" {{ old('customer_type') == 'vip' ? 'selected' : '' }}>អតិថិជន VIP
                                    </option>
                                    <option value="bad" {{ old('customer_type') == 'bad' ? 'selected' : '' }}>ប្រវត្តិមិនល្អ /
                                        Blacklisted</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="customer_status"><span class="khmer-os">ស្ថានភាពគណនី</span> / Initial
                                    Status</label>
                                <select id="customer_status" name="customer_status" class="khmer-os">
                                    <option value="active" {{ old('customer_status', 'active') == 'active' ? 'selected' : '' }}>សកម្ម / Active</option>
                                    <option value="inactive" {{ old('customer_status') == 'inactive' ? 'selected' : '' }}>
                                        មិនសកម្ម / Inactive</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="id_card_photo"><span class="khmer-os">រូបថតអត្តសញ្ញាណប័ណ្ណ</span> (KYC) / ID
                                    Card Image</label>
                                <input type="file" id="id_card_photo" name="id_card_photo" accept="image/*"
                                    class="@error('id_card_photo') input-error @enderror">
                                @error('id_card_photo')<span class="error-message khmer-os">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group full-width">
                                <label for="customer_avatar"><span class="khmer-os">រូបថតផ្ទាល់មុខអតិថិជន</span> / Customer
                                    Photo</label>
                                <input type="file" id="customer_avatar" name="customer_avatar" accept="image/*"
                                    class="@error('customer_avatar') input-error @enderror">
                                @error('customer_avatar')<span class="error-message khmer-os">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group full-width">
                                <label for="notes"><span class="khmer-os">កំណត់ចំណាំបន្ថែម</span> / Notes & Remarks</label>
                                <textarea id="notes" name="notes" rows="3"
                                    placeholder="Enter special remarks or history notes...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" id="cancelModalBtn" onclick="resetFormToCreateMode()"><span
                                class="khmer-os">បោះបង់</span> /
                            Cancel</button>
                        <button type="submit" class="btn-submit"><span class="khmer-os">រក្សាទុក</span> / Save
                            Customer</button>
                    </div>
                </form>

            </div>
        </div>

        <!-- ============================= detail update information customer =============== -->
        <div id="my_hidden_edit_box"
            style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(15, 23, 42, 0.4); display: none; align-items: center; justify-content: center; z-index: 9999999;">
            <div class="separate-modal-card">
                <div class="separate-modal-header">
                    <h3 style="margin: 0; font-size: 15px; color: #0f172a; font-weight: 700;">
                        <i class="fa-solid fa-user-pen" style="color:#2563eb; margin-right:6px;"></i>
                        កែប្រែព័ត៌មានលម្អិតអតិថិជន / Edit Customer Details
                    </h3>
                    <span style="cursor: pointer; color: #94a3b8; font-size: 22px; font-weight: bold; line-height: 1;"
                        onclick="closeMyHiddenEditBox()">&times;</span>
                </div>

                <form id="advancedUpdateForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT') <!-- បញ្ជាបង្ខំឱ្យលុបជាន់ (Override) ទិន្នន័យចាស់ -->

                    <div class="separate-modal-body">
                        <div class="form-scroll-container">

                            <!-- Section 1: ព័ត៌មានផ្ទាល់ខ្លួន -->
                            <div class="sep-section-title"><i class="fa-solid fa-address-card"></i> <span
                                    class="khmer-os">ព័ត៌មានផ្ទាល់ខ្លួន</span> / Personal Information</div>
                            <div class="sep-grid">
                                <div class="sep-group">
                                    <label><span class="khmer-os">ឈ្មោះអតិថិជន</span> / Customer Name</label>
                                    <input type="text" name="customer_name" id="sep_customer_name" required>
                                </div>
                                <div class="sep-group">
                                    <label><span class="khmer-os">ភេទ</span> / Gender</label>
                                    <select name="gender" id="sep_gender" required>
                                        <option value="">ជ្រើសរើស / Select</option>
                                        <option value="M">ប្រុស / Male</option>
                                        <option value="F">ស្រី / Female</option>
                                    </select>
                                </div>
                                <div class="sep-group">
                                    <label><span class="khmer-os">ថ្ងៃខែឆ្នាំកំណើត</span> / Date of Birth</label>
                                    <input type="date" name="dob" id="sep_dob" required>
                                </div>
                                <div class="sep-group">
                                    <label><span class="khmer-os">អត្តសញ្ញាណប័ណ្ណ ឬ លិខិតឆ្លងដែន</span> / ID or
                                        Passport</label>
                                    <input type="text" name="passport_id" id="sep_passport_id" required>
                                </div>
                                <div class="sep-group">
                                    <label><span class="khmer-os">លេខទូរស័ព្ទ</span> / Phone Number</label>
                                    <input type="text" name="phone_number" id="sep_phone_number" required>
                                </div>
                                <div class="sep-group">
                                    <label><span class="khmer-os">អ៊ីមែល</span> / Email Address</label>
                                    <input type="email" name="email" id="sep_email">
                                </div>
                            </div>

                            <!-- Section 2: អាសយដ្ឋានបច្ចុប្បន្ន -->
                            <div class="sep-section-title"><i class="fa-solid fa-map-location-dot"></i> <span
                                    class="khmer-os">អាសយដ្ឋានបច្ចុប្បន្ន</span> / Current Address</div>
                            <div class="sep-grid">
                                <div class="sep-group">
                                    <label><span class="khmer-os">ផ្ទះលេខ</span> / House No.</label>
                                    <input type="text" name="address_house" id="sep_address_house">
                                </div>
                                <div class="sep-group">
                                    <label><span class="khmer-os">ផ្លូវ</span> / Street No.</label>
                                    <input type="text" name="address_street" id="sep_address_street">
                                </div>
                                <div class="sep-group">
                                    <label><span class="khmer-os">ភូមិ-ឃុំ-សង្កាត់</span> / Village-Commune</label>
                                    <input type="text" name="address_village" id="sep_address_village" required>
                                </div>
                                <div class="sep-group">
                                    <label><span class="khmer-os">ក្រុង-ស្រុក-ខណ្ឌ</span> / District</label>
                                    <input type="text" name="address_district" id="sep_address_district" required>
                                </div>
                                <div class="sep-group full-width">
                                    <label><span class="khmer-os">ខេត្ត-រាជធានី</span> / Province-Capital</label>
                                    <input type="text" name="address_province" id="sep_address_province" required>
                                </div>
                            </div>

                            <!-- Section 3: អ្នកធានា ឬ ទំនាក់ទំនងបន្ទាន់ -->
                            <div class="sep-section-title"><i class="fa-solid fa-shield-halved"></i> <span
                                    class="khmer-os">អ្នកធានា ឬ ទំនាក់ទំនងបន្ទាន់</span> / Emergency Contact</div>
                            <div class="sep-grid">
                                <div class="sep-group">
                                    <label><span class="khmer-os">ឈ្មោះអ្នកធានា</span> / Guarantor Name</label>
                                    <input type="text" name="guarantor_name" id="sep_guarantor_name">
                                </div>
                                <div class="sep-group">
                                    <label><span class="khmer-os">លេខទូរស័ព្ទអ្នកធានា</span> / Guarantor Phone</label>
                                    <input type="text" name="guarantor_phone" id="sep_guarantor_phone">
                                </div>
                            </div>

                            <!-- Section 4: ប្រព័ន្ធគ្រប់គ្រង និងឯកសារស្កែន -->
                            <div class="sep-section-title"><i class="fa-solid fa-sliders"></i> <span
                                    class="khmer-os">ប្រព័ន្ធគ្រប់គ្រង និងឯកសារស្កែន</span> / System & Documents</div>
                            <div class="sep-grid">
                                <div class="sep-group">
                                    <label><span class="khmer-os">ចំនួនបញ្ចាំសកម្ម</span> / Active Pledges</label>
                                    <input type="number" name="active_pledges" id="sep_active_pledges" min="0">
                                </div>
                                <div class="sep-group">
                                    <label><span class="khmer-os">ចំណាត់ថ្នាក់អតិថិជន</span> / Customer Category</label>
                                    <select name="customer_type" id="sep_customer_type">
                                        <option value="regular">អតិថិជនទូទៅ / Regular</option>
                                        <option value="vip">អតិថិជន VIP</option>
                                        <option value="bad">ប្រវត្តិមិនល្អ / Blacklisted</option>
                                    </select>
                                </div>
                                <div class="sep-group full-width">
                                    <label><span class="khmer-os">ស្ថានភាពគណនី</span> / Status</label>
                                    <select name="customer_status" id="sep_customer_status">
                                        <option value="active">សកម្ម / Active</option>
                                        <option value="inactive">មិនសកម្ម / Inactive</option>
                                    </select>
                                </div>
                                <div class="sep-group">
                                    <label><span class="khmer-os">រូបថតអត្តសញ្ញាណប័ណ្ណ</span> (KYC)</label>
                                    <input type="file" name="id_card_photo" accept="image/*">
                                </div>
                                <div class="sep-group">
                                    <label><span class="khmer-os">រូបថតផ្ទាល់មុខអតិថិជន</span></label>
                                    <input type="file" name="customer_avatar" accept="image/*">
                                </div>
                            </div>

                            <!-- ផ្នែកកំណត់ចំណាំបន្ថែម -->
                            <div class="sep-section-title"><i class="fa-solid fa-file-lines"></i> <span
                                    class="khmer-os">កំណត់ចំណាំបន្ថែម</span> / Notes & Remarks</div>
                            <div class="sep-group full-width">
                                <textarea name="notes" id="sep_notes" rows="3"
                                    placeholder="Enter special remarks..."></textarea>
                            </div>

                        </div>
                    </div>

                    <div class="separate-modal-footer">
                        <button type="button" class="sep-btn sep-btn-cancel" onclick="closeMyHiddenEditBox()">បោះបង់ /
                            Cancel</button>
                        <button type="submit" class="sep-btn sep-btn-save"><i class="fa-solid fa-floppy-disk"></i>
                            រក្សាទុកការកែប្រែ / Save Changes</button>
                    </div>
                </form>
            </div>
        </div>




    </div>


@endsection
@push('scripts')
    <script src="{{ asset('js/customer.js') }}"></script>
@endpush