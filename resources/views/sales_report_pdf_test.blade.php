<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Report</title>
    <style>
        body {
            font-family: 'noto sans lao', sans-serif;
            font-size: 12pt;
            color: #333;
        }
    </style>
</head>
<body>
    <h1>Test Report PDF</h1>
    <p>Start Date: {{ $startDate }}</p>
    <p>End Date: {{ $endDate }}</p>
        <p>Total Sales: {{ \number_format($summary['total_sales'] ?? 0, 2) }}</p>
    <p>If you see this text and the dates/sales, then data is being passed and rendered.</p>
</body>
</html>
