<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan - KasirKu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 5px 0;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #6366f1;
            color: white;
        }

        .total {
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #999;
            font-size: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>LAPORAN PENJUALAN</h1>
        <p>Periode: {{ $period }}</p>
        <p>Dicetak: {{ $generated_at }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Invoice</th>
                <th>Tanggal</th>
                <th>Item</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($transactions as $index => $tx)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $tx->invoice_number }}</td>
                    <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @foreach($tx->items as $item)
                            {{ $item->product->name ?? 'N/A' }} ({{ $item->quantity }})<br>
                        @endforeach
                    </td>
                    <td>Rp {{ number_format($tx->total, 0, ',', '.') }}</td>
                </tr>
                @php $grandTotal += $tx->total; @endphp
            @endforeach
            <tr class="total">
                <td colspan="4" style="text-align: right;">GRAND TOTAL:</td>
                <td>Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak oleh KasirKu POS System</p>
    </div>
</body>

</html>