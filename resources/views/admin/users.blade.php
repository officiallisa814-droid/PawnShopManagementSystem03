@extends('layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin_css/users.css') }}">
@endpush

@section('content')
    @php
        $roles = config('roles.roles');
        $perms = config('roles.permissions');
        $me = auth()->id();
    @endphp

    <div id="us-app" data-store="{{ route('users.store') }}" data-update="{{ url('users') }}"
        data-old="{{ json_encode(old()) }}">

        @if (session('success'))
            <div class="us-toast ok" id="us-toast" role="status">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="us-toast bad" id="us-toast" role="alert">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> {{ session('error') }}
            </div>
        @endif

        <a href="{{ route('settings') }}" class="us-back">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> ត្រឡប់ទៅ Settings
        </a>

        {{-- ================= Header ================= --}}
        <div class="us-head">
            <div class="us-title">
                <span class="us-ic"><i class="fa-solid fa-users-gear" aria-hidden="true"></i></span>
                <div>
                    <h1>គ្រប់គ្រងអ្នកប្រើ <small>Users & Roles</small></h1>
                    <p>បង្កើតគណនី កំណត់ Role និងសិទ្ធិប្រើប្រាស់</p>
                </div>
            </div>
            <button type="button" class="us-btn" id="us-add">
                <i class="fa-solid fa-user-plus" aria-hidden="true"></i> បន្ថែមអ្នកប្រើ
            </button>
        </div>

        {{-- ================= Role summary ================= --}}
        <div class="us-roles">
            @foreach ($roles as $key => $r)
                <div class="us-role">
                    <span class="us-ic {{ $r['color'] }}"><i class="fa-solid {{ $r['icon'] }}" aria-hidden="true"></i></span>
                    <div class="us-role-txt">
                        <b>{{ $r['label'] }}</b>
                        <small>{{ $r['km'] }}</small>
                    </div>
                    <em>{{ $users->filter(fn($u) => $u->roleKey() === $key)->count() }}</em>
                </div>
            @endforeach
        </div>

        {{-- ================= Pending signups ================= --}}
        @if ($pending->isNotEmpty())
            <section class="us-pending">
                <h2><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i> សំណើរង់ចាំអនុម័ត <em>{{ $pending->count() }}</em></h2>
                <p>អ្នកទាំងនេះបានចុះឈ្មោះ ប៉ុន្តែមិនទាន់អាចចូលប្រព័ន្ធបានទេ រហូតដល់អ្នកកំណត់ Role។</p>
                @foreach ($pending as $p)
                    <div class="us-pend-row">
                        <span class="us-av us-av-l">{{ mb_strtoupper(mb_substr($p->name, 0, 1)) }}</span>
                        <div class="us-pend-info">
                            <b>{{ $p->name }}</b>
                            <small>{{ $p->email }}@if ($p->phone) · {{ $p->phone }}@endif</small>
                            <small>ចុះឈ្មោះ {{ optional($p->created_at)->format('d/m/Y H:i') }}</small>
                        </div>
                        <div class="us-pend-act">
                            <button type="button" class="us-btn js-approve" data-id="{{ $p->id }}" data-name="{{ $p->name }}">
                                <i class="fa-solid fa-user-check" aria-hidden="true"></i> អនុម័ត
                            </button>
                            <form method="POST" action="{{ route('users.destroy', $p) }}"
                                onsubmit="return confirm('បដិសេធសំណើរបស់ {{ e($p->name) }} មែនទេ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="us-btn ghost danger">
                                    <i class="fa-solid fa-user-xmark" aria-hidden="true"></i> បដិសេធ
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </section>
        @endif

        {{-- ================= Users table ================= --}}
        <div class="us-card">
            <table class="us-table">
                <thead>
                    <tr>
                        <th>អ្នកប្រើ</th>
                        <th>Role</th>
                        <th>ស្ថានភាព</th>
                        <th>ចូលរួម</th>
                        <th class="us-th-act">សកម្មភាព</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        @php
                            $rc = $u->roleConfig();
                            $self = $u->id === $me;
                        @endphp
                        <tr data-id="{{ $u->id }}" data-name="{{ $u->name }}" data-email="{{ $u->email }}"
                            data-phone="{{ $u->phone }}" data-role="{{ $u->roleKey() }}"
                            data-active="{{ $u->isActive() ? 1 : 0 }}" data-self="{{ $self ? 1 : 0 }}">
                            <td data-label="អ្នកប្រើ">
                                <div class="us-user">
                                    @if ($u->avatar)
                                        <img src="{{ asset('storage/' . $u->avatar) }}" alt="" class="us-av">
                                    @else
                                        <span class="us-av us-av-l">{{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                                    @endif
                                    <div>
                                        <b>{{ $u->name }} @if ($self)<em class="us-you">អ្នក</em>@endif</b>
                                        <small>{{ $u->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Role">
                                <span class="us-badge {{ $rc['color'] }}">
                                    <i class="fa-solid {{ $rc['icon'] }}" aria-hidden="true"></i> {{ $rc['label'] }}
                                </span>
                            </td>
                            <td data-label="ស្ថានភាព">
                                @if ($u->isActive())
                                    <span class="us-state on"><i class="fa-solid fa-circle" aria-hidden="true"></i> សកម្ម</span>
                                @else
                                    <span class="us-state off"><i class="fa-solid fa-circle" aria-hidden="true"></i> ផ្អាក</span>
                                @endif
                            </td>
                            <td data-label="ចូលរួម">{{ optional($u->created_at)->format('d/m/Y') ?? '—' }}</td>
                            <td class="us-act">
                                <button type="button" class="us-icon-btn js-edit" title="កែ" aria-label="កែ {{ $u->name }}">
                                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                </button>
                                @unless ($self)
                                    <form method="POST" action="{{ route('users.destroy', $u) }}"
                                        onsubmit="return confirm('លុបអ្នកប្រើ {{ e($u->name) }} មែនទេ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="us-icon-btn danger" title="លុប"
                                            aria-label="លុប {{ $u->name }}">
                                            <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ================= Permission matrix ================= --}}
        <details class="us-card us-matrix">
            <summary>
                <i class="fa-solid fa-table-list" aria-hidden="true"></i> មើលតារាងសិទ្ធិរបស់ Role នីមួយៗ
            </summary>
            <div class="us-scroll">
                <table class="us-mx">
                    <thead>
                        <tr>
                            <th>សិទ្ធិ</th>
                            @foreach ($roles as $r)
                                <th><span class="us-badge {{ $r['color'] }}">{{ $r['label'] }}</span></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($perms as $key => $label)
                            <tr>
                                <td>{{ $label }}</td>
                                @foreach ($roles as $r)
                                    @php $ok = in_array('*', $r['permissions']) || in_array($key, $r['permissions']); @endphp
                                    <td class="{{ $ok ? 'yes' : 'no' }}">
                                        <i class="fa-solid {{ $ok ? 'fa-circle-check' : 'fa-circle-xmark' }}"
                                            aria-label="{{ $ok ? 'មាន' : 'គ្មាន' }}"></i>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>

        {{-- ================= Add / Edit dialog ================= --}}
        <dialog id="us-dialog" class="us-dialog">
            <form id="us-form" method="POST" novalidate>
                @csrf
                <input type="hidden" name="_method" value="PUT" disabled>
                <input type="hidden" name="_mode" value="create">
                <input type="hidden" name="_id" value="">

                <header>
                    <h2 id="us-dlg-title"></h2>
                    <button type="button" class="us-x" data-close aria-label="បិទ">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </header>

                <div class="us-body">
                    @if ($errors->any())
                        <div class="us-alert">
                            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                            <ul>
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="us-grid">
                        <div class="us-field full">
                            <label for="u-name">ឈ្មោះ <b>*</b></label>
                            <div class="us-input">
                                <i class="fa-solid fa-user us-fi" aria-hidden="true"></i>
                                <input id="u-name" name="name" type="text" autocomplete="off">
                            </div>
                            <span class="us-err"></span>
                        </div>

                        <div class="us-field">
                            <label for="u-email">អ៊ីមែល <b>*</b></label>
                            <div class="us-input">
                                <i class="fa-solid fa-envelope us-fi" aria-hidden="true"></i>
                                <input id="u-email" name="email" type="email" inputmode="email" autocomplete="off">
                            </div>
                            <span class="us-err"></span>
                        </div>

                        <div class="us-field">
                            <label for="u-phone">ទូរស័ព្ទ</label>
                            <div class="us-input">
                                <i class="fa-solid fa-phone us-fi" aria-hidden="true"></i>
                                <input id="u-phone" name="phone" type="tel" inputmode="tel" autocomplete="off">
                            </div>
                            <span class="us-err"></span>
                        </div>

                        <div class="us-field">
                            <label for="u-role">Role <b>*</b></label>
                            <div class="us-input">
                                <i class="fa-solid fa-id-badge us-fi" aria-hidden="true"></i>
                                <select id="u-role" name="role">
                                    @foreach ($roles as $key => $r)
                                        <option value="{{ $key }}">{{ $r['label'] }} — {{ $r['km'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <span class="us-err"></span>
                        </div>

                        <div class="us-field">
                            <label for="u-pass"><span id="u-pass-lbl">ពាក្យសម្ងាត់</span> <b id="u-pass-req">*</b></label>
                            <div class="us-input">
                                <i class="fa-solid fa-key us-fi" aria-hidden="true"></i>
                                <input id="u-pass" name="password" type="password" autocomplete="new-password">
                            </div>
                            <small class="us-hint" id="u-pass-hint">យ៉ាងតិច ៨ តួអក្សរ មានអក្សរ និងលេខ</small>
                            <span class="us-err"></span>
                        </div>

                        <label class="us-switch full" id="u-active-wrap">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <span class="us-track"></span>
                            <span class="us-switch-txt">
                                <b>គណនីសកម្ម</b>
                                <small>បិទ ដើម្បីផ្អាកមិនឱ្យអ្នកប្រើនេះចូលប្រព័ន្ធ</small>
                            </span>
                        </label>

                        <p class="us-self full" id="u-self-note" hidden>
                            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                            នេះជាគណនីរបស់អ្នក — មិនអាចប្តូរ Role ឬផ្អាកខ្លួនឯងបានទេ។
                        </p>
                    </div>
                </div>

                <footer>
                    <button type="button" class="us-btn ghost" data-close>
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i> បោះបង់
                    </button>
                    <button type="submit" class="us-btn">
                        <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> រក្សាទុក
                    </button>
                </footer>
            </form>
        </dialog>

        {{-- ================= Approve dialog ================= --}}
        <dialog id="us-approve" class="us-dialog small">
            <form id="us-approve-form" method="POST" novalidate>
                @csrf
                <header>
                    <h2><i class="fa-solid fa-user-check" aria-hidden="true"></i> អនុម័តគណនី</h2>
                    <button type="button" class="us-x" data-close aria-label="បិទ">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </header>
                <div class="us-body">
                    <p class="us-ap-who" id="us-ap-name"></p>
                    <div class="us-field">
                        <label for="ap-role">ជ្រើសរើស Role <b>*</b></label>
                        <div class="us-input">
                            <i class="fa-solid fa-id-badge us-fi" aria-hidden="true"></i>
                            <select id="ap-role" name="role">
                                <option value="" disabled selected>— ជ្រើសរើស Role —</option>
                                @foreach ($roles as $key => $r)
                                    <option value="{{ $key }}">{{ $r['label'] }} — {{ $r['km'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <span class="us-err" id="ap-err"></span>
                    </div>
                </div>
                <footer>
                    <button type="button" class="us-btn ghost" data-close>បោះបង់</button>
                    <button type="submit" class="us-btn">
                        <i class="fa-solid fa-check" aria-hidden="true"></i> អនុម័ត
                    </button>
                </footer>
            </form>
        </dialog>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/users.js') }}"></script>
@endpush