@extends('layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin_css/profile.css') }}">
@endpush

@section('content')
    @php
        $avatarUrl = $user->avatar ? asset('storage/' . $user->avatar) : null;
        $tab = session('tab', old('_tab', 'info'));
        $isAdmin = ($user->role ?? '') === 'admin';
        $initial = mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1));
    @endphp

    <div id="pf-app" data-tab="{{ $tab }}">

        @if (session('success'))
            <div class="pf-toast" id="pf-toast" role="status">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- ================= HERO ================= --}}
        <section class="pf-hero">
            <div class="pf-banner"></div>

            <div class="pf-hero-body">
                <form id="avatar-form" method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data"
                    class="pf-avatar-wrap" novalidate>
                    @csrf
                    <div class="pf-avatar">
                        <img id="pf-avatar-img" src="{{ $avatarUrl }}" alt="Profile photo" @if (!$avatarUrl) hidden @endif>
                        <span id="pf-avatar-letter" @if ($avatarUrl) hidden @endif>{{ $initial }}</span>
                    </div>
                    <label class="pf-cam" for="avatar-input" title="ប្តូររូបថត">
                        <i class="fa-solid fa-camera" aria-hidden="true"></i>
                        <span class="sr-only">ប្តូររូបថត</span>
                    </label>
                    <input type="file" id="avatar-input" name="avatar" accept="image/png,image/jpeg,image/webp" hidden>
                </form>

                <div class="pf-id">
                    <h1>{{ $user->name }}</h1>

                    <div class="pf-meta">
                        <span class="pf-badge {{ $isAdmin ? 'admin' : '' }}">
                            <i class="fa-solid {{ $isAdmin ? 'fa-crown' : 'fa-user-tie' }}" aria-hidden="true"></i>
                            {{ $isAdmin ? 'System Administrator' : 'Staff' }}
                        </span>

                        <span class="pf-meta-item">
                            <span class="pf-mi blue"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                            {{ $user->email }}
                        </span>

                        @if ($user->phone)
                            <span class="pf-meta-item">
                                <span class="pf-mi green"><i class="fa-solid fa-phone" aria-hidden="true"></i></span>
                                {{ $user->phone }}
                            </span>
                        @endif
                    </div>

                    <div class="pf-photo-actions">
                        <div id="avatar-actions" class="pf-pending" hidden>
                            <span class="pf-pending-text">
                                <i class="fa-solid fa-image" aria-hidden="true"></i>
                                រូបថតថ្មីរួចរាល់ — ចុចរក្សាទុកដើម្បីប្តូរ
                            </span>
                            <span class="pf-pending-btns">
                                <button type="submit" form="avatar-form" class="pf-btn small">
                                    <i class="fa-solid fa-check" aria-hidden="true"></i> រក្សាទុករូបថត
                                </button>
                                <button type="button" id="avatar-cancel" class="pf-btn small ghost">
                                    <i class="fa-solid fa-xmark" aria-hidden="true"></i> បោះបង់
                                </button>
                            </span>
                        </div>

                        @if ($user->avatar)
                            <form id="avatar-remove-form" method="POST" action="{{ route('profile.avatar.remove') }}"
                                onsubmit="return confirm('លុបរូបថតមែនទេ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="pf-link danger">
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i> លុបរូបថត
                                </button>
                            </form>
                        @endif
                    </div>
                    <p class="pf-err pf-avatar-err" id="avatar-err">{{ $errors->first('avatar') }}</p>
                </div>
            </div>

            <div class="pf-stats">
                <div class="pf-stat">
                    <span class="pf-ic amber"><i class="fa-solid fa-user-shield" aria-hidden="true"></i></span>
                    <div>
                        <small>តួនាទី / Role</small>
                        <b>{{ $isAdmin ? 'Administrator' : 'Staff' }}</b>
                    </div>
                </div>
                <div class="pf-stat">
                    <span class="pf-ic green"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i></span>
                    <div>
                        <small>ចូលរួមតាំងពី / Joined</small>
                        <b>{{ optional($user->created_at)->format('d/m/Y') ?? '—' }}</b>
                    </div>
                </div>
                <div class="pf-stat">
                    <span class="pf-ic violet"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i></span>
                    <div>
                        <small>កែចុងក្រោយ / Updated</small>
                        <b>{{ optional($user->updated_at)->format('d/m/Y H:i') ?? '—' }}</b>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= TABS ================= --}}
        <section class="pf-card">
            <div class="pf-tabs" role="tablist">
                <button type="button" class="pf-tab" data-tab="info" role="tab">
                    <i class="fa-solid fa-user-pen" aria-hidden="true"></i>
                    <span>ព័ត៌មានផ្ទាល់ខ្លួន</span>
                </button>
                <button type="button" class="pf-tab" data-tab="security" role="tab">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    <span>សុវត្ថិភាព</span>
                </button>
            </div>

            {{-- ---------- Personal info ---------- --}}
            <form class="pf-pane" data-pane="info" id="info-form" method="POST" action="{{ route('profile.info') }}"
                novalidate>
                @csrf
                <input type="hidden" name="_tab" value="info">

                <div class="pf-pane-head">
                    <span class="pf-ic"><i class="fa-solid fa-id-card" aria-hidden="true"></i></span>
                    <div>
                        <h2>ព័ត៌មានផ្ទាល់ខ្លួន</h2>
                        <p>កែឈ្មោះ អ៊ីមែល និងលេខទូរស័ព្ទរបស់អ្នក។</p>
                    </div>
                </div>

                <div class="pf-grid">
                    <div class="pf-field full {{ $errors->has('name') ? 'invalid' : '' }}">
                        <label for="f-name">ឈ្មោះ / Full name <b>*</b></label>
                        <div class="pf-input">
                            <i class="fa-solid fa-user pf-ico" aria-hidden="true"></i>
                            <input id="f-name" name="name" type="text" autocomplete="name"
                                value="{{ old('name', $user->name) }}" placeholder="ឈ្មោះរបស់អ្នក">
                        </div>
                        <span class="pf-err" data-err="name">{{ $errors->first('name') }}</span>
                    </div>

                    <div class="pf-field {{ $errors->has('email') ? 'invalid' : '' }}">
                        <label for="f-email">អ៊ីមែល / Email <b>*</b></label>
                        <div class="pf-input">
                            <i class="fa-solid fa-envelope pf-ico" aria-hidden="true"></i>
                            <input id="f-email" name="email" type="email" autocomplete="email" inputmode="email"
                                value="{{ old('email', $user->email) }}" placeholder="name@example.com">
                        </div>
                        <span class="pf-err" data-err="email">{{ $errors->first('email') }}</span>
                    </div>

                    <div class="pf-field {{ $errors->has('phone') ? 'invalid' : '' }}">
                        <label for="f-phone">លេខទូរស័ព្ទ / Phone</label>
                        <div class="pf-input">
                            <i class="fa-solid fa-phone pf-ico" aria-hidden="true"></i>
                            <input id="f-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel"
                                value="{{ old('phone', $user->phone) }}" placeholder="012 345 678">
                        </div>
                        <span class="pf-err" data-err="phone">{{ $errors->first('phone') }}</span>
                    </div>
                </div>

                <div class="pf-actions">
                    <button type="submit" class="pf-btn">
                        <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> រក្សាទុក
                    </button>
                </div>
            </form>

            {{-- ---------- Security ---------- --}}
            <form class="pf-pane" data-pane="security" id="pw-form" method="POST"
                action="{{ route('profile.password') }}" novalidate hidden>
                @csrf
                <input type="hidden" name="_tab" value="security">

                <div class="pf-pane-head">
                    <span class="pf-ic amber"><i class="fa-solid fa-lock" aria-hidden="true"></i></span>
                    <div>
                        <h2>ប្តូរពាក្យសម្ងាត់</h2>
                        <p>ប្រើពាក្យសម្ងាត់ខ្លាំង ដែលអ្នកមិនប្រើនៅកន្លែងផ្សេង។</p>
                    </div>
                </div>

                <div class="pf-grid">
                    <div class="pf-field full {{ $errors->has('current_password') ? 'invalid' : '' }}">
                        <label for="f-cur">ពាក្យសម្ងាត់បច្ចុប្បន្ន <b>*</b></label>
                        <div class="pf-input">
                            <i class="fa-solid fa-lock pf-ico" aria-hidden="true"></i>
                            <input id="f-cur" name="current_password" type="password" autocomplete="current-password">
                            <button type="button" class="pf-eye" data-eye aria-label="បង្ហាញ/លាក់ពាក្យសម្ងាត់">
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <span class="pf-err" data-err="current_password">{{ $errors->first('current_password') }}</span>
                    </div>

                    <div class="pf-field {{ $errors->has('password') ? 'invalid' : '' }}">
                        <label for="f-new">ពាក្យសម្ងាត់ថ្មី <b>*</b></label>
                        <div class="pf-input">
                            <i class="fa-solid fa-key pf-ico" aria-hidden="true"></i>
                            <input id="f-new" name="password" type="password" autocomplete="new-password">
                            <button type="button" class="pf-eye" data-eye aria-label="បង្ហាញ/លាក់ពាក្យសម្ងាត់">
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="pf-meter" data-s="0"><i></i><i></i><i></i><i></i></div>
                        <small class="pf-strength" id="pf-strength"></small>
                        <span class="pf-err" data-err="password">{{ $errors->first('password') }}</span>
                    </div>

                    <div class="pf-field">
                        <label for="f-conf">បញ្ជាក់ពាក្យសម្ងាត់ថ្មី <b>*</b></label>
                        <div class="pf-input">
                            <i class="fa-solid fa-check-double pf-ico" aria-hidden="true"></i>
                            <input id="f-conf" name="password_confirmation" type="password" autocomplete="new-password">
                            <button type="button" class="pf-eye" data-eye aria-label="បង្ហាញ/លាក់ពាក្យសម្ងាត់">
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <span class="pf-err" data-err="password_confirmation"></span>
                    </div>

                    <ul class="pf-rules full">
                        <li data-rule="len">
                            <i class="fa-regular fa-circle off" aria-hidden="true"></i>
                            <i class="fa-solid fa-circle-check on" aria-hidden="true"></i>
                            យ៉ាងតិច ៨ តួអក្សរ
                        </li>
                        <li data-rule="let">
                            <i class="fa-regular fa-circle off" aria-hidden="true"></i>
                            <i class="fa-solid fa-circle-check on" aria-hidden="true"></i>
                            មានអក្សរ (A-Z)
                        </li>
                        <li data-rule="num">
                            <i class="fa-regular fa-circle off" aria-hidden="true"></i>
                            <i class="fa-solid fa-circle-check on" aria-hidden="true"></i>
                            មានលេខ (0-9)
                        </li>
                    </ul>
                </div>

                <div class="pf-actions">
                    <button type="submit" class="pf-btn">
                        <i class="fa-solid fa-key" aria-hidden="true"></i> ប្តូរពាក្យសម្ងាត់
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/profile.js') }}"></script>
@endpush