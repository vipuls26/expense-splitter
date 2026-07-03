<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Wallet Transactions</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 13px;
            color: #374151;
            /* gray-700 */
            background-color: #f9fafb;
            /* gray-50 */
            margin: 0;
            padding: 20px;
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .header {
            margin-bottom: 30px;
            border-bottom: 1px solid #e5e7eb;
            /* gray-200 */
            padding-bottom: 16px;
        }

        .header h2 {
            margin: 0;
            font-size: 24px;
            color: #111827;
            /* gray-900 */
            font-weight: 700;
        }

        .header p {
            margin: 4px 0 0 0;
            color: #6b7280;
            /* gray-500 */
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #f3f4f6;
            /* gray-100 */
            color: #4b5563;
            /* gray-600 */
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
            /* gray-200 */
            color: #1f2937;
            /* gray-800 */
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .amount-green {
            color: #059669;
            /* emerald-600 */
            font-weight: 600;
        }

        .amount-red {
            color: #dc2626;
            /* red-600 */
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-deposit {
            background-color: #d1fae5;
            color: #065f46;
        }

        /* emerald */
        .badge-settlement_received {
            background-color: #dbeafe;
            color: #1e40af;
        }

        /* blue */
        .badge-settlement_sent {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* red */
        .badge-default {
            background-color: #f3f4f6;
            color: #374151;
        }

        /* gray */
        .text-sm {
            font-size: 12px;
            color: #6b7280;
        }

        .footer {
            margin-top: 32px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <h2>Wallet Statement</h2>
            <p>Account Holder: <strong>{{ $user->name }}</strong> ({{ $user->email }})</p>
            <p>Generated on: {{ now()->format('M d, Y h:i A') }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th class="text-right">Balance Before</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Balance After</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td style="white-space: nowrap;">
                            <div>{{ $transaction->created_at->format('M d, Y') }}</div>
                            <div class="text-sm">{{ $transaction->created_at->format('h:i A') }}</div>
                        </td>
                        <td>
                            @php
                                $badgeClass = match ($transaction->type) {
                                    'deposit' => 'badge-deposit',
                                    'settlement_received' => 'badge-settlement_received',
                                    'settlement_sent' => 'badge-settlement_sent',
                                    default => 'badge-default'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                {{ str_replace('_', ' ', $transaction->type) }}
                            </span>
                        </td>
                        <td class="text-right text-sm">₹{{ number_format($transaction->balance_before, 2) }}</td>
                        <td
                            class="text-right {{ in_array($transaction->type, ['deposit', 'settlement_received']) ? 'amount-green' : 'amount-red' }}">
                            {{ in_array($transaction->type, ['deposit', 'settlement_received']) ? '+' : '-' }}<span style="font-weight: normal;">₹</span>{{ number_format($transaction->amount, 2) }}
                        </td>
                        <td class="text-right" style="font-weight: 600;">
                            <span style="font-weight: normal;">₹</span>{{ number_format($transaction->balance_after, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 32px; color: #6b7280;">
                            No transactions found for the selected period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            &copy; {{ date('Y') }} Expense Splitter. All rights reserved.
        </div>
    </div>

</body>

</html>