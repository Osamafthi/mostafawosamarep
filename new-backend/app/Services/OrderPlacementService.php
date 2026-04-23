<?php

namespace App\Services;

use App\Exceptions\OrderPlacementException;
use App\Jobs\SendOrderConfirmationToCustomer;
use App\Jobs\SendOrderNotificationToAdmin;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\CatalogCache;
use Illuminate\Support\Facades\DB;

class OrderPlacementService
{
    /**
     * Place a new order atomically and return the persisted Order with items/customer loaded.
     *
     * @param  array{customer_name:string, customer_email:string, customer_phone:?string, shipping_address:string, items: array<int, array{product_id:int, quantity:int}>}  $payload
     */
    public function place(array $payload, ?Customer $customer = null): Order
    {
        $order = DB::transaction(function () use ($payload, $customer) {
            $subtotal = 0.0;
            $resolvedItems = [];

            foreach ($payload['items'] as $line) {
                $product = Product::query()
                    ->lockForUpdate()
                    ->find((int) $line['product_id']);

                if (! $product) {
                    throw new OrderPlacementException("Product {$line['product_id']} not found.");
                }

                if ($product->status !== 'active') {
                    throw new OrderPlacementException("Product '{$product->name}' is not available.");
                }

                $qty = max(1, (int) $line['quantity']);

                if ((int) $product->stock < $qty) {
                    throw new OrderPlacementException("Insufficient stock for '{$product->name}'.");
                }

                $unitPrice = $product->effectivePrice();
                $lineSubtotal = round($unitPrice * $qty, 2);
                $subtotal += $lineSubtotal;

                $resolvedItems[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customer?->getKey(),
                'customer_name' => $payload['customer_name'],
                'customer_email' => $payload['customer_email'],
                'customer_phone' => $payload['customer_phone'] ?? null,
                'shipping_address' => $payload['shipping_address'],
                'subtotal' => round($subtotal, 2),
                'total' => round($subtotal, 2),
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            foreach ($resolvedItems as $line) {
                /** @var Product $product */
                $product = $line['product'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['subtotal'],
                ]);

                $product->decrement('stock', $line['quantity']);
            }

            return $order->fresh(['items.product']);
        });

        // Stock changed on one or more products — invalidate cached catalog
        // reads so shoppers see accurate availability during sale rushes.
        CatalogCache::flush('products');

        SendOrderConfirmationToCustomer::dispatch($order->id);
        SendOrderNotificationToAdmin::dispatch($order->id);

        return $order;
    }

    protected function generateOrderNumber(): string
    {
        // ORD-YYYYMMDD-<6hex> — matches the legacy format.
        return 'ORD-'.date('Ymd').'-'.bin2hex(random_bytes(3));
    }
}
