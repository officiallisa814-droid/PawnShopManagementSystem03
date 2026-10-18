<!DOCTYPE html>
<html lang="km">

<head>
    <meta charset="utf-8">
    <title>កិច្ចសន្យាបញ្ចាំ {{ $c->contract_no }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Khmer OS Battambang", "Noto Sans Khmer", serif;
            font-size: 14px;
            line-height: 1.7;
            color: #000;
            background: #cfd8e3;
            margin: 0;
        }

        /* ប៊ូតុង */
        .toolbar {
            position: fixed;
            top: 15px;
            right: 30px;
            z-index: 10;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .btn-print {
            background: #2563eb;
            color: #fff;
        }

        .btn-print:hover {
            background: #1d4ed8;
        }

        .btn-back {
            background: #fff;
            color: #0b2240;
        }

        .btn-back:hover {
            background: #e2e8f0;
        }

        /* ក្រដាស់ A4 */
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 15mm;
            background: #fff;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header img {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }

        .header h2 {
            margin: 5px 0 0;
            font-size: 18px;
        }

        .header p {
            margin: 0;
            font-size: 13px;
        }

        h1 {
            text-align: center;
            font-size: 22px;
            margin: 10px 0 0;
        }

        .no {
            text-align: center;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        td {
            border: 1px solid #000;
            padding: 6px 10px;
        }

        td:first-child {
            width: 38%;
            background: #eeeeee;
        }

        .note {
            text-align: justify;
        }

        .sign {
            display: flex;
            justify-content: space-between;
            margin-top: 80px;
            text-align: center;
        }

        .sign div {
            width: 40%;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        @media print {
            body {
                background: #fff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .toolbar {
                display: none;
            }

            .page {
                margin: 0;
                box-shadow: none;
                width: 100%;
                min-height: auto;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <a href="{{ route('loanContract.show', $c->id) }}" class="btn btn-back">← ត្រឡប់ក្រោយ</a>
        <button class="btn btn-print" onclick="window.print()">🖨 បោះពុម្ព / រក្សាទុកជា PDF</button>
    </div>

    <div class="page">
        <div class="header">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
            <h2>ហាងបញ្ចាំ</h2>
            <p>ទូរស័ព្ទ៖ ............................ អាសយដ្ឋាន៖ ............................</p>
        </div>

        <h1>កិច្ចសន្យាបញ្ចាំ</h1>
        <div class="no">លេខ៖ {{ $c->contract_no }}</div>

        <table>
            <tr>
                <td>ឈ្មោះអតិថិជន</td>
                <td>{{ $c->customer->customer_name }}</td>
            </tr>
            <tr>
                <td>លេខទូរស័ព្ទ</td>
                <td>{{ $c->customer->phone_number }}</td>
            </tr>
            <tr>
                <td>លេខអត្តសញ្ញាណប័ណ្ណ</td>
                <td>{{ $c->customer->passport_id }}</td>
            </tr>
            <tr>
                <td>ទំនិញបញ្ចាំ</td>
                <td>{{ $c->item_name }} {{ $c->item_description ? '— ' . $c->item_description : '' }}</td>
            </tr>
            <tr>
                <td>តម្លៃប៉ាន់ស្មាន</td>
                <td>${{ number_format($c->item_value, 2) }}</td>
            </tr>
            <tr>
                <td>ចំនួនទឹកប្រាក់កម្ចី</td>
                <td>${{ number_format($c->loan_amount, 2) }}</td>
            </tr>
            <tr>
                <td>អត្រាការប្រាក់</td>
                <td>{{ $c->interest_rate }}% ក្នុងមួយខែ</td>
            </tr>
            <tr>
                <td>រយៈពេលខ្ចី</td>
                <td>{{ $c->term_months }} ខែ</td>
            </tr>
            <tr>
                <td>ថ្ងៃខ្ចី</td>
                <td>{{ $c->loan_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>ថ្ងៃកំណត់</td>
                <td>{{ $c->due_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>ការប្រាក់សរុប</td>
                <td>${{ number_format($c->loan_amount * $c->interest_rate / 100 * $c->term_months, 2) }}</td>
            </tr>
            <tr>
                <td>ចំនួនត្រូវបង់ពេលលោះ</td>
                <td><strong>${{ number_format($c->loan_amount * (1 + $c->interest_rate / 100 * $c->term_months), 2) }}</strong></td>
            </tr>
        </table>

        <p class="note">
            អតិថិជនបញ្ជាក់ថាទំនិញខាងលើជាកម្មសិទ្ធិផ្ទាល់ខ្លួន
            ហើយយល់ព្រមថា ប្រសិនបើមិនបង់ប្រាក់កម្ចី និងការប្រាក់ត្រឹមថ្ងៃកំណត់
            ហាងមានសិទ្ធិលក់ ឬដេញថ្លៃទំនិញនេះ ដើម្បីទូទាត់បំណុល។
        </p>

        <div class="sign">
            <div>អតិថិជន</div>
            <div>ហាងបញ្ចាំ</div>
        </div>
    </div>
</body>

</html>