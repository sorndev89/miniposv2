<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ລາຍງານການຂາຍ</title>
    <style>
        @font-face {
            font-family: 'PHETSARATHOT';
            src: url("{{ storage_path('fonts/PHETSARATHOT.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'PHETSARATHOT';
            font-size: 8pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>ລາຍງານການຂາຍ</h1>
    <p>ວັນທີ: {{ $startDate }} - {{ $endDate }}</p>

    <h2>ສະຫຼຸບລາຍງານ</h2>
    <table>
        <tr>
            <th>ຍອດຂາຍລວມ:</th>
            <td class="text-right">₭{{ \number_format($summary['total_sales'], 2) }}</td>
        </tr>
        <tr>
            <th>ຈຳນວນຄຳສັ່ງຊື້:</th>
            <td class="text-right">{{ $summary['total_orders'] }}</td>
        </tr>
        <tr>
            <th>ກຳໄລລວມ:</th>
            <td class="text-right">₭{{ \number_format($summary['total_profit'], 2) }}</td>
        </tr>
    </table>

    <h2>ລາຍລະອຽດການຂາຍ</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>ລະຫັດຄໍາສັ່ງ</th>
                <th>ຜູ້ຂາຍ</th>
                <th>ລາຍການສິນຄ້າ</th>
                <th>ຈໍານວນທັງໝົດ</th>
                <th>ເງິນທີ່ໄດ້ຮັບ</th>
                <th>ເງິນທອນ</th>
                <th>ກຳໄລ</th>
                <th>ວັນທີຂາຍ</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $index => $report)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $report['order_id'] }}</td>
                    <td>{{ $report['seller'] }}</td>
                    <td>
                        @foreach ($report['items'] as $item)
                            {{ $item['name'] }} (x{{ $item['quantity'] }}) - ₭{{ \number_format($item['price'], 2) }}<br>
                        @endforeach
                    </td>
                    <td class="text-right">₭{{ \number_format($report['total_amount'], 2) }}</td>
                    <td class="text-right">₭{{ \number_format($report['amount_received'], 2) }}</td>
                    <td class="text-right">₭{{ \number_format($report['change_amount'], 2) }}</td>
                    <td class="text-right">₭{{ \number_format($report['profit'] ?? 0, 2) }}</td>
                    <td>{{ $report['sale_date'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">ບໍ່ມີຂໍ້ມູນລາຍງານໃນໄລຍະເວລານີ້.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
