@extends('layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin_css/loanContractShow.css') }}">
@endpush

@section('content')
    <div class="pawn-container">
        <div class="d-top">
            <a href="{{ route('payments') }}" class="d-btn">← <span class="khmer-os">ត្រឡប់ក្រោយ</span> / Back</a>
            <a href="{{ route('loan-contracts.print', $contract->id) }}" target="_blank" class="d-btn blue">
                Print Agreement
            </a>
        </div>

        <div class="d-wrap">
            {{-- ========== LEFT: contract info ========== --}}
            <div>
                <div class="d-card">
                    <h3 class="d-title">{{ $contract->contract_no }}</h3>

                    <div class="d-row"><span>Status</span><span>{{ ucfirst($contract->status) }}</span></div>
                    <div class="d-row"><span>Customer</span>
                        <span class="khmer-os">{{ $contract->customer->customer_name ?? 'N/A' }}</span>
                    </div>
                    <div class="d-row"><span>Phone</span><span>{{ $contract->customer->phone_number ?? '—' }}</span></div>
                    <div class="d-row"><span>Item</span><span>{{ $contract->item_name }}</span></div>
                    <div class="d-row"><span>IMEI /
                            Serial</span><span>{{ $contract->pawnItem->imei_serial_tag ?? '—' }}</span></div>
                    <div class="d-row"><span>Storage</span><span>{{ $contract->pawnItem->storage_location ?? '—' }}</span>
                    </div>
                    <div class="d-row"><span>Principal</span><span>${{ number_format($contract->loan_amount, 2) }}</span>
                    </div>
                    <div class="d-row"><span>Interest rate</span><span>{{ $contract->interest_rate }}% / month</span></div>
                    <div class="d-row"><span>Monthly interest</span><span>${{ number_format($monthly, 2) }}</span></div>
                    <div class="d-row"><span>Loan date</span><span>{{ $contract->loan_date->format('d/m/Y') }}</span></div>
                    <div class="d-row"><span>Due date</span><span>{{ $contract->due_date->format('d/m/Y') }}</span></div>
                    @if($contract->days_overdue)
                        <div class="d-row"><span>Overdue</span>
                            <span style="color:#b91c1c;">{{ $contract->days_overdue }} days</span>
                        </div>
                    @endif

                    @if($contract->is_open)
                        <div class="d-total">
                            <div><span>Principal</span><span>${{ number_format($contract->loan_amount, 2) }}</span></div>
                            <div><span>Interest ({{ $contract->months_elapsed }} month)</span>
                                <span>${{ number_format($contract->accrued_interest, 2) }}</span>
                            </div>
                            <div class="big"><span>Total to redeem today</span>
                                <span>${{ number_format($contract->total_due, 2) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ========== RIGHT ========== --}}
            <div>
                <div class="d-card">
                    <h3 class="d-title"><span class="khmer-os">កាលវិភាគការប្រាក់ប្រចាំខែ</span> / Interest Schedule</h3>
                    <table class="d-table">
                        <thead>
                            <tr>
                                <th>Term</th>
                                <th>Due date</th>
                                <th>Interest</th>
                                <th>Status</th>
                                <th>Paid</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedule as $s)
                                <tr>
                                    <td>Month {{ $s['term'] }}</td>

                                    <td>{{ \Carbon\Carbon::parse($s['due'])->format('d/m/Y') }}</td>

                                    <td>${{ number_format($s['amount'], 2) }}</td>
                                    <td>
                                        @if($s['status'] === 'paid')
                                            <span class="d-badge b-passed">Paid</span>

                                        @elseif(\Carbon\Carbon::parse($s['due'])->isPast())
                                            <span class="d-badge b-closed">Overdue</span>
                                        @else
                                            <span class="d-badge b-upcoming">Unpaid</span>
                                        @endif
                                    </td>
                                    <td>{{ $s['paid_date'] ? \Carbon\Carbon::parse($s['paid_date'])->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>
                                        @if($s['status'] === 'paid')
                                            <i class="fa-solid fa-circle-check" style="color: #10b981;"></i> Paid
                                        @else
                                            <button type="button" class="d-btn blue js-pay" data-id="{{ $s['id'] }}"
                                                data-amount="{{ $s['amount'] }}">Pay</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center; color:#94a3b8;">No schedule yet.</td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                <div class="d-card">
                    <h3 class="d-title"><span class="khmer-os">ប្រវត្តិប្រតិបត្តិការ</span> / Transaction History</h3>
                    <table class="d-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contract->transactions as $t)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($t->date)->format('d/m/Y') }}</td>
                                    <td>{{ ucfirst($t->type) }}</td>
                                    <td style="color:{{ $t->amount < 0 ? '#b91c1c' : '#15803d' }};font-weight:600;">
                                        {{ $t->amount < 0 ? '-' : '+' }}${{ number_format(abs($t->amount), 2) }}
                                    </td>
                                    <td>{{ $t->note }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align:center;color:#94a3b8;">No transactions.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @if (session('success'))
        <div style="position:fixed;bottom:20px;right:20px;background:#15803d;color:#fff;padding:10px 16px;border-radius:6px;">
            {{ session('success') }}
        </div>
    @endif

    <form id="payForm" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="amount" id="payAmount">
        <input type="hidden" name="method" id="payMethod">
    </form>
@endsection
@push('scripts')
    <script src="{{ asset('js/loanContract.js') }}"></script>
    <script>
        document.querySelectorAll('.js-pay').forEach(function (b) {
            b.addEventListener('click', function () {
                var amount = prompt('ចំនួនទទួល ($):', b.dataset.amount);
                if (amount === null) return;
                var method = prompt('វិធីបង់ (cash, aba, wing, bank, other):', 'cash');
                if (!method) return;
                document.getElementById('payAmount').value = amount;
                document.getElementById('payMethod').value = method;
                var f = document.getElementById('payForm');
                f.action = '{{ url('payments') }}/' + b.dataset.id + '/pay';
                f.submit();
            });
        });
    </script>
@endpush