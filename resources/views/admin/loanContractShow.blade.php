@extends('layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin_css/loanContractShow.css') }}">
@endpush

@section('content')
    @php
        // ខែដំបូងដែលមិនទាន់បង់ = ខែតែមួយគត់ដែលអាចចុច Pay បាន
        $nextId = collect($schedule)->firstWhere('status', '!=', 'paid')['id'] ?? null;
    @endphp

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
                    <div class="d-row"><span>IMEI / Serial</span><span>{{ $contract->pawnItem->imei_serial_tag ?? '—' }}</span></div>
                    <div class="d-row"><span>Storage</span><span>{{ $contract->pawnItem->storage_location ?? '—' }}</span></div>
                    <div class="d-row"><span>Principal</span><span>${{ number_format($contract->loan_amount, 2) }}</span></div>
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
                    <div class="d-table-scroll">
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
                                                <span class="d-badge b-paid">Paid</span>
                                            @elseif(\Carbon\Carbon::parse($s['due'])->startOfDay()->lt(today()))
                                                <span class="d-badge b-overdue">Overdue</span>
                                            @else
                                                <span class="d-badge b-unpaid">Unpaid</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($s['status'] === 'paid')
                                                ${{ number_format($s['paid_amount'] ?? $s['amount'], 2) }}<br>
                                                <small style="color:#64748b;">
                                                    {{ $s['paid_date'] ? \Carbon\Carbon::parse($s['paid_date'])->format('d/m/Y') : '' }}
                                                    @if(!empty($s['method'])) · {{ ucfirst($s['method']) }} @endif
                                                </small>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($s['status'] === 'paid')
                                                <i class="fa-solid fa-circle-check" style="color:#10b981;"></i> Paid
                                            @elseif(!$contract->is_open)
                                                <span class="lock">Closed</span>
                                            @elseif($s['id'] === $nextId)
                                                <button type="button" class="d-btn blue js-pay"
                                                    data-id="{{ $s['id'] }}"
                                                    data-term="{{ $s['term'] }}"
                                                    data-amount="{{ $s['amount'] }}">Pay</button>
                                            @else
                                                <span class="lock"><i class="fa-solid fa-lock"></i> បង់ខែមុនសិន</span>
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
                </div>

                <div class="d-card">
                    <h3 class="d-title"><span class="khmer-os">ប្រវត្តិប្រតិបត្តិការ</span> / Transaction History</h3>
                    <div class="d-table-scroll">
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
    </div>

    {{-- ===== Flash messages ===== --}}
    @if (session('success')) <div class="flash ok" id="flashMsg">{{ session('success') }}</div> @endif
    @if (session('error'))   <div class="flash err" id="flashMsg">{{ session('error') }}</div> @endif
    @if ($errors->any())     <div class="flash err" id="flashMsg">{{ $errors->first() }}</div> @endif

    {{-- ===== Pay modal ===== --}}
    <div class="pm-overlay" id="payModal">
        <div class="pm-box">
            <h3><span class="khmer-os">កត់ត្រាការបង់ប្រាក់</span></h3>
            <p id="payTitle"></p>
            <form id="payForm" method="POST">
                @csrf
                <label>ចំនួនទទួល ($)</label>
                <input type="number" step="0.01" min="0" name="amount" id="payAmount" required>
                <label>វិធីបង់</label>
                <select name="method">
                    <option value="cash">Cash</option>
                    <option value="aba">ABA</option>
                    <option value="wing">Wing</option>
                    <option value="bank">Bank</option>
                    <option value="other">Other</option>
                </select>
                <div class="pm-actions">
                    <button type="button" id="payCancel">បោះបង់</button>
                    <button type="submit" class="ok" id="paySubmit">បញ្ជាក់</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const modal = document.getElementById('payModal');
    const form = document.getElementById('payForm');
    const close = () => modal.classList.remove('show');

    document.querySelectorAll('.js-pay').forEach(b => {
        b.addEventListener('click', () => {
            form.action = '{{ url('payments') }}/' + b.dataset.id + '/pay';
            document.getElementById('payAmount').value = b.dataset.amount;
            document.getElementById('payTitle').textContent = 'Month ' + b.dataset.term + ' · $' + b.dataset.amount;
            modal.classList.add('show');
        });
    });
    document.getElementById('payCancel').onclick = close;
    modal.addEventListener('mousedown', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
    form.addEventListener('submit', () => { document.getElementById('paySubmit').disabled = true; });

    const f = document.getElementById('flashMsg');
    if (f) setTimeout(() => f.remove(), 4000);
})();
</script>
@endpush