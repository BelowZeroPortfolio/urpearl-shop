<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Alert</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background-color: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ec4899;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #ec4899;
            margin-bottom: 10px;
        }
        .alert-icon {
            font-size: 48px;
            color: #f59e0b;
            margin-bottom: 15px;
        }
        .title {
            color: #1f2937;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .product-info {
            background-color: #fef3f2;
            border-left: 4px solid #ef4444;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .product-name {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .stock-info {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }
        .stock-label {
            font-weight: 600;
            color: #6b7280;
        }
        .stock-value {
            font-weight: bold;
        }
        .current-stock {
            color: #ef4444;
        }
        .threshold {
            color: #f59e0b;
        }
        .action-section {
            background-color: #f0f9ff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .btn {
            display: inline-block;
            background-color: #ec4899;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 10px 0;
        }
        .btn:hover {
            background-color: #db2777;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">UrPearl SHOP</div>
            <div class="alert-icon">⚠️</div>
            <h1 class="title">Low Stock Alert</h1>
        </div>

        <p>Hello {{ $admin->name }},</p>

        <p>This is an automated notification to inform you that one of your products is running low on stock and requires immediate attention.</p>

        <div class="product-info">
            <div class="product-name">{{ $product->name }}</div>
            <div class="stock-info">
                <span class="stock-label">Current Stock:</span>
                <span class="stock-value current-stock">{{ $currentQuantity }} units</span>
            </div>
            <div class="stock-info">
                <span class="stock-label">Low Stock Threshold:</span>
                <span class="stock-value threshold">{{ $threshold }} units</span>
            </div>
            <div class="stock-info">
                @if($product->size)
                <span class="stock-label">Product Size:</span>
                <span class="stock-value">{{ $product->size }}</span>
                @endif
            </div>
        </div>

        <div class="action-section">
            <p><strong>Action Required:</strong> Please restock this product to avoid running out of inventory.</p>
            <a href="{{ url('/admin/inventory') }}" class="btn">Manage Inventory</a>
        </div>

        <p>You can manage your inventory levels and update stock quantities through the admin dashboard. It's recommended to restock before reaching zero inventory to avoid disappointing customers.</p>

        <div class="footer">
            <p>This is an automated message from UrPearl SHOP inventory management system.</p>
            <p>If you have any questions, please contact your system administrator.</p>
        </div>
    </div>
</body>
</html>