<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Menu;
use App\Http\Requests\CreateOrderRequest;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function store(CreateOrderRequest $request): RedirectResponse
    {
        if (!$this->validateCart()) {
            return back()->withErrors('Keranjang belanja kosong!');
        }

        if (!$this->validateMinimumOrder()) {
            return back()->withErrors('Minimal pemesanan Rp ' . number_format(config('app.minimum_order')) . ' belum tercapai');
        }

        DB::beginTransaction();

        try {
            $order = $this->createOrder($request->validated());

            if (!$this->processOrderItems($order)) {
                DB::rollBack();
                return back()->withErrors('Gagal memproses item pesanan');
            }

            DB::commit();
            session()->forget('cart');

            return redirect()->route('customer.payments.create', ['order' => $order->id])
                ->with('success', 'Pesanan berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(), // Lebih singkat dan aman
                'cart' => session('cart')
            ]);

            return back()->withErrors('Terjadi kesalahan dalam pembuatan pesanan');
        }
    }

    private function validateCart(): bool
    {
        return session()->has('cart') && !empty(session('cart'));
    }

    private function validateMinimumOrder(): bool
    {
        $minimumOrder = config('app.minimum_order', 100000);
        return $this->calculateTotal(session('cart')) >= $minimumOrder;
    }

    private function createOrder(array $validated): Order
    {
        return Order::create([
            'user_id' => auth()->id(), // Lebih singkat dan aman
            'delivery_date' => $validated['delivery_date'],
            'delivery_address' => $validated['delivery_address'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'total_amount' => $this->calculateTotal(session('cart')),
        ]);
    }

    private function processOrderItems(Order $order): bool
    {
        foreach (session('cart') as $menuId => $details) {
            $menu = Menu::findOrFail($menuId); // Using findOrFail instead of find

            if (!$this->validateAndUpdateStock($menu, $details['quantity'])) {
                Log::error('Stock validation failed', [
                    'menu_id' => $menu->id,
                    'requested' => $details['quantity'],
                    'available' => $menu->stock
                ]);
                return false;
            }

            $this->createOrderItem($order, $menu, $details);
        }

        return true;
    }

    private function validateAndUpdateStock(Menu $menu, int $quantity): bool
    {
        if (!$menu || $menu->stock < $quantity) {
            Log::warning('Insufficient stock', [
                'menu_id' => $menu->id,
                'requested' => $quantity,
                'available' => $menu->stock ?? 0
            ]);
            return false;
        }

        $menu->decrement('stock', $quantity); // Using decrement instead of update
        return true;
    }

    private function createOrderItem(Order $order, Menu $menu, array $details): void
    {
        OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'quantity' => $details['quantity'],
            'price' => $menu->price, // Using menu price instead of cart price for security
        ]);
    }

    private function calculateTotal(array $cart): float
    {
        return collect($cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
    }
}
