<!DOCTYPE html>
<html lang="km">

<head>
    <meta charset="UTF-8">
    <!-- ធានាថាការ ዝום (Zoom) នៅលើទូរស័ព្ទដើរត្រូវទំហំល្អ -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ចុះឈ្មោះ | Pawn Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Noto+Sans+Khmer:wght@400;500;600;700&display=swap">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: #0b2240;
            font-family: "Noto Sans Khmer", "Inter", "Segoe UI", sans-serif;
            color: #0f172a;
        }

        .card {
            width: 100%;
            max-width: 460px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 28px 22px;
            box-shadow: 0 15px 35px rgba(11, 34, 64, .1);
        }

        .logo {
            width: 56px;
            height: 56px;
            margin: 0 auto 12px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            font-size: 24px;
            color: #fff;
            /* background: linear-gradient(135deg, #1e74ff, #0b2240); */
        }

        h1 {
            text-align: center;
            font-size: 20px;
            color: #0b2240;
        }

        .sub {
            text-align: center;
            color: #64748b;
            font-size: 13.5px;
            margin: 4px 0 20px;
        }

        .field {
            margin-bottom: 14px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        label b {
            color: #ef4444;
        }

        .in {
            position: relative;
        }

        .in>i.ic {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
            pointer-events: none;
        }

        .in input {
            width: 100%;
            height: 48px;
            padding: 0 44px 0 42px;
            font: inherit;
            font-size: 16px;
            /* ជៀសវាងការ Zoom ស្វ័យប្រវត្តិនៅលើ iOS */
            color: #0f172a;
            border: 1.5px solid #d5dce6;
            border-radius: 12px;
            outline: none;
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
            -webkit-appearance: none;
            /* លុបកែវយឹត/រចនាប័ទ្មខុសប្រក្រតីលើទូរស័ព្ទខ្លះ */
        }

        .in input:focus {
            border-color: #2563eb;
        }

        .field.bad .in input {
            border-color: #dc2626;
            background: #fef2f2;
        }

        .eye {
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            border: 0;
            background: none;
            color: #64748b;
            cursor: pointer;
            border-radius: 9px;
        }

        .eye:hover {
            background: #f1f5f9;
        }

        .err {
            display: block;
            margin-top: 4px;
            color: #dc2626;
            font-size: 12px;
        }

        .err:empty {
            display: none;
        }

        .btn {
            width: 100%;
            height: 48px;
            margin-top: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 12px;
            background: #2563eb;
            color: #fff;
            font: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            -webkit-tap-highlight-color: transparent;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .btn:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .note {
            display: flex;
            gap: 10px;
            margin-top: 14px;
            padding: 10px 12px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            color: #1e40af;
            font-size: 12.5px;
            line-height: 1.5;
        }

        .foot {
            text-align: center;
            margin-top: 14px;
            font-size: 13.5px;
            color: #64748b;
        }

        .foot a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }

        .done {
            text-align: center;
            padding: 6px 0 4px;
        }

        .done .ok {
            width: 76px;
            height: 76px;
            margin: 0 auto 14px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            font-size: 30px;
            color: #16a34a;
            background: #f0fdf4;
            border: 5px solid #dcfce7;
        }

        .done p {
            color: #64748b;
            line-height: 1.6;
            font-size: 14px;
            margin: 8px 0 18px;
        }

        /* កែសម្រួលបន្ថែមសម្រាប់អេក្រង់ទូរស័ព្ទតូចៗ */
        @media (max-width: 480px) {
            body {
                padding: 10px;
                align-items: flex-start;
            }

            .card {
                padding: 22px 16px;
                border-radius: 16px;
                margin-top: 10px;
                box-shadow: 0 10px 25px rgba(11, 34, 64, .08);
            }

            h1 {
                font-size: 19px;
            }

            .sub {
                font-size: 13px;
            }

            .in input {
                height: 46px;
                font-size: 16px;
            }

            .btn {
                height: 46px;
            }
        }
    </style>
</head>

<body>
    <main class="card">
        @if (session('registered'))
            {{-- ===== ជោគជ័យ ===== --}}
            <div class="done">
                <div class="ok"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i></div>
                <h1>បានទទួលសំណើរបស់អ្នកហើយ</h1>
                <p>សូមអរគុណ <b>{{ session('registered') }}</b>។ គណនីរបស់អ្នកកំពុងរង់ចាំ Admin អនុម័ត និងកំណត់ Role។
                    អ្នកនឹងអាចចូលប្រើបាន បន្ទាប់ពីត្រូវបានអនុម័ត។</p>
                <a href="{{ route('login') }}" class="btn">
                    <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> ទៅទំព័រ Login
                </a>
            </div>
        @else
            {{-- ===== Form ===== --}}
            <!-- <div class="logo"><i class="fa-solid fa-user-plus" aria-hidden="true"></i></div> -->
            <div class="logo"><img src="{{ asset('images/logo.png') }}" alt="logo" width="60px"></div>
            <h1>ចុះឈ្មោះគណនីថ្មី</h1>
            <p class="sub">បំពេញព័ត៌មានខាងក្រោម ដើម្បីស្នើសុំគណនី</p>

            <form id="su-form" method="POST" action="{{ route('signup.store') }}" novalidate>
                @csrf

                <div class="field {{ $errors->has('name') ? 'bad' : '' }}">
                    <label for="name">ឈ្មោះ <b>*</b></label>
                    <div class="in">
                        <i class="fa-solid fa-user ic" aria-hidden="true"></i>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name">
                    </div>
                    <span class="err">{{ $errors->first('name') }}</span>
                </div>

                <div class="field {{ $errors->has('email') ? 'bad' : '' }}">
                    <label for="email">អ៊ីមែល <b>*</b></label>
                    <div class="in">
                        <i class="fa-solid fa-envelope ic" aria-hidden="true"></i>
                        <input id="email" name="email" type="email" inputmode="email" value="{{ old('email') }}"
                            autocomplete="email">
                    </div>
                    <span class="err">{{ $errors->first('email') }}</span>
                </div>

                <div class="field {{ $errors->has('phone') ? 'bad' : '' }}">
                    <label for="phone">លេខទូរស័ព្ទ</label>
                    <div class="in">
                        <i class="fa-solid fa-phone ic" aria-hidden="true"></i>
                        <input id="phone" name="phone" type="tel" inputmode="tel" value="{{ old('phone') }}"
                            autocomplete="tel">
                    </div>
                    <span class="err">{{ $errors->first('phone') }}</span>
                </div>

                <div class="field {{ $errors->has('password') ? 'bad' : '' }}">
                    <label for="password">ពាក្យសម្ងាត់ <b>*</b></label>
                    <div class="in">
                        <i class="fa-solid fa-key ic" aria-hidden="true"></i>
                        <input id="password" name="password" type="password" autocomplete="new-password">
                        <button type="button" class="eye" data-eye aria-label="បង្ហាញ/លាក់"><i class="fa-regular fa-eye"
                                aria-hidden="true"></i></button>
                    </div>
                    <span class="err">{{ $errors->first('password') }}</span>
                </div>

                <div class="field">
                    <label for="password_confirmation">បញ្ជាក់ពាក្យសម្ងាត់ <b>*</b></label>
                    <div class="in">
                        <i class="fa-solid fa-check-double ic" aria-hidden="true"></i>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                            autocomplete="new-password">
                        <button type="button" class="eye" data-eye aria-label="បង្ហាញ/លាក់"><i class="fa-regular fa-eye"
                                aria-hidden="true"></i></button>
                    </div>
                    <span class="err"></span>
                </div>

                <button type="submit" class="btn">
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> ផ្ញើសំណើ
                </button>

                <div class="note">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    <span>គណនីថ្មីមិនអាចចូលប្រើបានភ្លាមទេ។ Admin ត្រូវអនុម័ត និងកំណត់ Role (Manager, Cashier ឬ Appraiser)
                        ជាមុនសិន។</span>
                </div>
            </form>

            <p class="foot">មានគណនីរួចហើយ? <a href="{{ route('login') }}">ចូលប្រើ</a></p>

            <script>
                (() => {
                    const f = document.getElementById('su-form');
                    const set = (n, m) => {
                        const el = f.elements[n], box = el.closest('.field');
                        box.classList.toggle('bad', !!m);
                        box.querySelector('.err').textContent = m || '';
                    };
                    f.addEventListener('input', e => e.target.name && set(e.target.name, ''));
                    f.querySelectorAll('[data-eye]').forEach(b => b.addEventListener('click', () => {
                        const i = b.parentElement.querySelector('input'), show = i.type === 'password';
                        i.type = show ? 'text' : 'password';
                        b.innerHTML = '<i class="fa-regular fa-eye' + (show ? '-slash' : '') + '"></i>';
                    }));
                    f.addEventListener('submit', e => {
                        const v = n => f.elements[n].value.trim(), p = f.elements.password.value;
                        const er = {
                            name: !v('name') ? 'សូមបញ្ចូលឈ្មោះ។' : v('name').length < 2 ? 'ឈ្មោះត្រូវមានយ៉ាងតិច ២ តួអក្សរ។' : '',
                            email: !v('email') ? 'សូមបញ្ចូលអ៊ីមែល។' : !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v('email')) ? 'ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវ។' : '',
                            phone: v('phone') && !/^[0-9+\-\s()]{6,20}$/.test(v('phone')) ? 'លេខទូរស័ព្ទមិនត្រឹមត្រូវ។' : '',
                            password: !p ? 'សូមបញ្ចូលពាក្យសម្ងាត់។' : !(p.length >= 8 && /[A-Za-z]/.test(p) && /\d/.test(p)) ? 'ត្រូវមាន ៨ តួអក្សរ មានអក្សរ និងលេខ។' : '',
                            password_confirmation: f.elements.password_confirmation.value !== p ? 'ការបញ្ជាក់ពាក្យសម្ងាត់មិនត្រូវគ្នា។' : '',
                        };
                        let first = null;
                        Object.keys(er).forEach(n => { set(n, er[n]); if (er[n] && !first) first = f.elements[n]; });
                        if (first) { e.preventDefault(); first.focus(); }
                        else f.querySelector('.btn').disabled = true;
                    });
                })();
            </script>
        @endif
    </main>
</body>

</html>