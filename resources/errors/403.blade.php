<!DOCTYPE html>
<html lang="km">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 | គ្មានសិទ្ធិចូលប្រើ</title>
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
            display: grid;
            place-items: center;
            padding: 20px;
            background:
                radial-gradient(circle at 15% 20%, rgba(30, 116, 255, .18) 0, transparent 45%),
                radial-gradient(circle at 85% 85%, rgba(11, 34, 64, .15) 0, transparent 45%),
                #eef2f7;
            font-family: "Noto Sans Khmer", "Inter", "Segoe UI", sans-serif;
            color: #0f172a;
        }

        .card {
            width: 100%;
            max-width: 460px;
            text-align: center;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            padding: 38px 28px 30px;
            box-shadow: 0 20px 45px rgba(11, 34, 64, .12);
        }

        .ic {
            width: 88px;
            height: 88px;
            margin: 0 auto 18px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            font-size: 36px;
            color: #dc2626;
            background: #fef2f2;
            border: 6px solid #fee2e2;
        }

        .code {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 2px;
            color: #dc2626;
        }

        h1 {
            font-size: 24px;
            margin: 6px 0 8px;
            color: #0b2240;
        }

        p {
            color: #64748b;
            font-size: 15px;
            line-height: 1.7;
        }

        .role {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
            padding: 6px 14px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 26px;
        }

        .btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 46px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #475569;
            cursor: pointer;
        }

        .btn:hover {
            background: #f1f5f9;
        }

        .btn.primary {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .btn.primary:hover {
            background: #1d4ed8;
        }

        @media (max-width: 480px) {
            .card {
                padding: 30px 20px 24px;
            }

            h1 {
                font-size: 21px;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                height: 50px;
            }
        }
    </style>
</head>

<body>
    <main class="card">
        <div class="ic"><i class="fa-solid fa-lock" aria-hidden="true"></i></div>
        <div class="code">ERROR 403</div>
        <h1>អ្នកមិនមានសិទ្ធិចូលទំព័រនេះទេ</h1>
        <p>គណនីរបស់អ្នកមិនត្រូវបានអនុញ្ញាតឱ្យប្រើមុខងារនេះឡើយ។ សូមទាក់ទង Admin ប្រសិនបើអ្នកគិតថានេះជាកំហុស។</p>

        @auth
            <span class="role">
                <i class="fa-solid {{ auth()->user()->roleConfig()['icon'] }}" aria-hidden="true"></i>
                {{ auth()->user()->roleConfig()['label'] }}
            </span>
        @endauth

        <div class="actions">
            <a href="javascript:history.back()" class="btn">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> ត្រឡប់ក្រោយ
            </a>
            @auth
                <a href="{{ route('dashboard') }}" class="btn primary">
                    <i class="fa-solid fa-house" aria-hidden="true"></i> ទៅ Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="btn primary">
                    <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> ចូលប្រើ
                </a>
            @endauth
        </div>
    </main>
</body>

</html>