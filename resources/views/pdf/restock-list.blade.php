<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: sans-serif; }
        .page-break { page-break-after: always; }
        .order-header { 
            background-color: #f3f4f6; 
            padding: 10px; 
            margin-bottom: 10px; 
            border-bottom: 2px solid #374151;
        }
        .order-title { font-size: 18px; font-weight: bold; }
        .order-meta { font-size: 12px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background-color: #f9fafb; }
        .img-cell { width: 60px; text-align: center; }
        .product-img { max-width: 50px; max-height: 50px; }
    </style>
</head>
<body>
    <h1>Liste de Préparation (Stock)</h1>
    <p>Généré le : {{ now()->format('d/m/Y H:i') }}</p>

    @foreach($orders as $order)
        <div class="order-container">
            <div class="order-header">
                <div class="order-title">{{ $order->name }}</div>
                <div class="order-meta">
                    Client: {{ $order->email }} | 
                    Date: {{ $order->order_date->format('d/m/Y') }}
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th class="img-cell">Image</th>
                        <th>Produit</th>
                        <th>Prix Unit.</th>
                        <th>Qté</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td class="img-cell">
                                @if(!empty($item['image_url']))
                                    <img src="{{ $item['image_url'] }}" class="product-img">
                                @else
                                    <span style="color: #ccc;">No Img</span>
                                @endif
                            </td>
                            <td>
                                {{ $item['name'] }}
                                <br>
                                <small style="color: #666;">{{ $item['sku'] ?? '' }}</small>
                            </td>
                            <td style="white-space: nowrap;">
                                {{ number_format($item['price'] ?? 0, 2) }} DA
                            </td>
                            <td style="font-weight: bold;">{{ $item['quantity'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Optional: Page break after every X orders or just continuous flow --}}
    @endforeach
</body>
</html>
