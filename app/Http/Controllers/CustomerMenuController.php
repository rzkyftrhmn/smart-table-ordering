<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DiningTable;
use App\Models\KitchenQueue;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockLog;
use App\Services\MidtransService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\RestaurantLocation;

class CustomerMenuController extends Controller
{
    /**
     * Cari meja berdasarkan token QR.
     * Token ini berasal dari URL: /customer-menu/{token}
     */
    private function getTableByToken(string $token): DiningTable
    {
        return DiningTable::where('qr_token', $token)->firstOrFail();
    }

    // helper session key
    private function locationSessionKey(): string
    {
        return 'location_verified';
    }

    /**
     * Halaman yang minta izin lokasi ke guest sebelum bisa akses menu.
     */
    public function checkLocation(string $token)
    {
        $this->getTableByToken($token); 

        // kalau udah pernah verified di session ini, langsung lempar ke menu
        if (session()->get($this->locationSessionKey())) {
            return redirect()->route('customer-menu.index', $token);
        }

        return view('Customer.location-check', compact('token'));
    }

    /**
     * Endpoint yang dipanggil JS setelah browser dapat lat/long guest.
     */
    public function verifyLocation(Request $request, string $token)
    {
        $this->getTableByToken($token);

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $restaurantLocation = RestaurantLocation::current();

        $isWithin = $restaurantLocation->isWithinRadius(
            $validated['latitude'],
            $validated['longitude']
        );

        if (! $isWithin) {
            return response()->json([
                'success' => false,
            ]);
        }

        session()->put($this->locationSessionKey(), true);

        return response()->json([
            'success' => true,
            'redirect' => route('customer-menu.index', $token),
        ]);
    }

    /**
     * Membuat nama key session cart berdasarkan table_id.
     * Jadi cart Table 01 tidak campur dengan Table 02.
     */
    private function cartKey(DiningTable $table): string
    {
        return 'customer_cart_table_'.$table->id;
    }

    private function menuPricing(MenuItem $menuItem): array
    {
        $discount = $menuItem->activeDiscount();
        $originalPrice = (float) $menuItem->price;
        $discountPercentage = $discount ? (float) $discount->percentage : 0;
        $discountAmount = $discount ? round(($originalPrice * $discountPercentage) / 100, 2) : 0;
        $finalPrice = max(0, round($originalPrice - $discountAmount, 2));

        return [
            'discount_id' => $discount?->id,
            'discount_name' => $discount?->name,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'original_price' => $originalPrice,
            'final_price' => $finalPrice,
        ];
    }

    private function cartSubtotalBeforeDiscount(array $item): float
    {
        return (float) ($item['subtotal_before_discount'] ?? (($item['original_price'] ?? $item['price'] ?? 0) * ($item['quantity'] ?? 0)));
    }

    private function cartDiscountTotal(array $item): float
    {
        return (float) ($item['discount_total'] ?? (($item['discount_amount'] ?? 0) * ($item['quantity'] ?? 0)));
    }

    private function cartFinalSubtotal(array $item): float
    {
        return (float) ($item['subtotal'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 0)));
    }

    /**
     * Halaman utama customer menu.
     * Menampilkan kategori dan menu yang aktif, tersedia, dan stock > 0.
     */
    public function index(string $token)
    {

        if (!session()->get($this->locationSessionKey())) {
            return redirect()->route('customer-menu.check', $token);
        }
        $table = $this->getTableByToken($token);

        $categories = Category::with(['menuItems' => function ($query) {
            $query->where('is_active', true)
                ->where('is_available', true)
                ->where('stock', '>', 0)
                ->with('menuDiscounts.discount')
                ->orderBy('name');
        }])
            ->orderBy('name')
            ->get();

        $cart = session()->get($this->cartKey($table), []);
        $cartCount = collect($cart)->sum('quantity');
        $discountedMenuItems = $categories
            ->flatMap(fn ($category) => $category->menuItems)
            ->filter(fn (MenuItem $menuItem) => $menuItem->hasActiveDiscount())
            ->sortByDesc(fn (MenuItem $menuItem) => $menuItem->discountPercentage())
            ->values();

        return view('Customer.menu', compact(
            'table',
            'categories',
            'token',
            'cartCount',
            'discountedMenuItems'
        ));
    }

    /**
     * Halaman search customer.
     * Mencari menu berdasarkan nama atau deskripsi.
     */
    public function search(Request $request, string $token)
    {
        $table = $this->getTableByToken($token);
        $keyword = $request->query('q');

        $menuItems = MenuItem::with(['category', 'menuDiscounts.discount'])
            ->where('is_active', true)
            ->where('is_available', true)
            ->where('stock', '>', 0)
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('name')
            ->get();

        $cart = session()->get($this->cartKey($table), []);
        $cartCount = collect($cart)->sum('quantity');

        return view('Customer.search', compact(
            'table',
            'menuItems',
            'keyword',
            'token',
            'cartCount'
        ));
    }

    /**
     * Halaman cart.
     * Data cart diambil dari session.
     */
    public function cart(string $token)
    {
        $table = $this->getTableByToken($token);

        $cart = session()->get($this->cartKey($table), []);

        $subtotal = collect($cart)->sum(fn ($item) => $this->cartSubtotalBeforeDiscount($item));
        $discountTotal = collect($cart)->sum(fn ($item) => $this->cartDiscountTotal($item));
        $grandTotal = collect($cart)->sum(fn ($item) => $this->cartFinalSubtotal($item));
        $cartCount = collect($cart)->sum('quantity');
        $clientKey = config('midtrans.client_key');

        return view('Customer.cart', compact(
            'table',
            'cart',
            'subtotal',
            'discountTotal',
            'grandTotal',
            'token',
            'cartCount',
            'clientKey'
        ));
    }

    /**
     * Tambah menu ke cart.
     * Kalau menu sudah ada di cart, quantity ditambah.
     */
    public function addToCart(Request $request, string $token)
    {
        $table = $this->getTableByToken($token);

        $validated = $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $menuItem = MenuItem::with('menuDiscounts.discount')
            ->where('id', $validated['menu_item_id'])
            ->where('is_active', true)
            ->where('is_available', true)
            ->where('stock', '>', 0)
            ->firstOrFail();

        if ($validated['quantity'] > $menuItem->stock) {
            return back()->with('error', 'Stock menu tidak cukup.');
        }

        $cartKey = $this->cartKey($table);
        $cart = session()->get($cartKey, []);

        $menuItemId = $menuItem->id;
        $pricing = $this->menuPricing($menuItem);

        if (isset($cart[$menuItemId])) {
            $newQuantity = $cart[$menuItemId]['quantity'] + $validated['quantity'];

            if ($newQuantity > $menuItem->stock) {
                return back()->with('error', 'Jumlah melebihi stock yang tersedia.');
            }

            $cart[$menuItemId]['quantity'] = $newQuantity;
            $cart[$menuItemId]['notes'] = filled($validated['notes'] ?? null)
                ? $validated['notes']
                : ($cart[$menuItemId]['notes'] ?? null);
            $cart[$menuItemId]['price'] = $pricing['final_price'];
            $cart[$menuItemId]['original_price'] = $pricing['original_price'];
            $cart[$menuItemId]['discount_id'] = $pricing['discount_id'];
            $cart[$menuItemId]['discount_name'] = $pricing['discount_name'];
            $cart[$menuItemId]['discount_percentage'] = $pricing['discount_percentage'];
            $cart[$menuItemId]['discount_amount'] = $pricing['discount_amount'];
            $cart[$menuItemId]['subtotal_before_discount'] = $pricing['original_price'] * $newQuantity;
            $cart[$menuItemId]['discount_total'] = $pricing['discount_amount'] * $newQuantity;
            $cart[$menuItemId]['subtotal'] = $pricing['final_price'] * $newQuantity;
        } else {
            $cart[$menuItemId] = [
                'menu_item_id' => $menuItem->id,
                'name' => $menuItem->name,
                'description' => $menuItem->description,
                'price' => $pricing['final_price'],
                'original_price' => $pricing['original_price'],
                'quantity' => $validated['quantity'],
                'notes' => $validated['notes'] ?? null,
                'image_url' => $menuItem->image_url,
                'discount_id' => $pricing['discount_id'],
                'discount_name' => $pricing['discount_name'],
                'discount_percentage' => $pricing['discount_percentage'],
                'discount_amount' => $pricing['discount_amount'],
                'subtotal_before_discount' => $pricing['original_price'] * $validated['quantity'],
                'discount_total' => $pricing['discount_amount'] * $validated['quantity'],
                'subtotal' => $pricing['final_price'] * $validated['quantity'],
            ];
        }

        session()->put($cartKey, $cart);

        return back()->with('success', 'Menu berhasil ditambahkan ke keranjang.');
    }

    /**
     * Update quantity menu di cart.
     */
    public function updateCart(Request $request, string $token)
    {
        $table = $this->getTableByToken($token);

        $validated = $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cartKey = $this->cartKey($table);
        $cart = session()->get($cartKey, []);

        $menuItemId = $validated['menu_item_id'];

        if (! isset($cart[$menuItemId])) {
            return back()->with('error', 'Menu tidak ada di cart.');
        }

        $menuItem = MenuItem::with('menuDiscounts.discount')->findOrFail($menuItemId);

        if ($validated['quantity'] > $menuItem->stock) {
            return back()->with('error', 'Jumlah melebihi stock yang tersedia.');
        }

        $pricing = $this->menuPricing($menuItem);
        $quantity = $validated['quantity'];

        $cart[$menuItemId]['quantity'] = $quantity;
        $cart[$menuItemId]['price'] = $pricing['final_price'];
        $cart[$menuItemId]['original_price'] = $pricing['original_price'];
        $cart[$menuItemId]['discount_id'] = $pricing['discount_id'];
        $cart[$menuItemId]['discount_name'] = $pricing['discount_name'];
        $cart[$menuItemId]['discount_percentage'] = $pricing['discount_percentage'];
        $cart[$menuItemId]['discount_amount'] = $pricing['discount_amount'];
        $cart[$menuItemId]['subtotal_before_discount'] = $pricing['original_price'] * $quantity;
        $cart[$menuItemId]['discount_total'] = $pricing['discount_amount'] * $quantity;
        $cart[$menuItemId]['subtotal'] = $pricing['final_price'] * $quantity;

        session()->put($cartKey, $cart);

        return back()->with('success', 'Cart berhasil diupdate.');
    }

    /**
     * Hapus menu dari cart.
     */
    public function removeFromCart(Request $request, string $token)
    {
        $table = $this->getTableByToken($token);

        $validated = $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],
        ]);

        $cartKey = $this->cartKey($table);
        $cart = session()->get($cartKey, []);

        unset($cart[$validated['menu_item_id']]);

        session()->put($cartKey, $cart);

        return back()->with('success', 'Menu berhasil dihapus dari cart.');
    }

    /**
     * Checkout cart menjadi order.
     */
    public function checkout(Request $request, string $token)
    {
        $table = $this->getTableByToken($token);

        $validated = $request->validate([
            'payment_choice' => ['required', 'in:cash,cashless'],
            'customer_name' => ['required_if:payment_choice,cashless', 'nullable', 'string', 'max:100'],
            'customer_phone' => ['required_if:payment_choice,cashless', 'nullable', 'string', 'max:30'],
            'customer_email' => ['required_if:payment_choice,cashless', 'nullable', 'email', 'max:150'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $cartKey = $this->cartKey($table);
        $cart = session()->get($cartKey, []);

        if (empty($cart)) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Cart masih kosong.'], 422);
            }

            return back()->with('error', 'Cart masih kosong.');
        }

        $order = null;

        try {
            $order = DB::transaction(function () use ($cart, $table, $validated, $cartKey) {
                $preparedItems = [];
                $subtotal = 0;
                $discountTotal = 0;
                $grandTotal = 0;

                foreach ($cart as $item) {
                    $menuItem = MenuItem::with('menuDiscounts.discount')->lockForUpdate()->findOrFail($item['menu_item_id']);
                    $quantity = $item['quantity'];

                    if ($quantity > $menuItem->stock) {
                        throw ValidationException::withMessages([
                            'cart' => 'Stock '.$menuItem->name.' tidak cukup.',
                        ]);
                    }

                    $pricing = $this->menuPricing($menuItem);
                    $subtotalBeforeDiscount = $pricing['original_price'] * $quantity;
                    $itemDiscountTotal = $pricing['discount_amount'] * $quantity;
                    $itemFinalSubtotal = $pricing['final_price'] * $quantity;

                    $subtotal += $subtotalBeforeDiscount;
                    $discountTotal += $itemDiscountTotal;
                    $grandTotal += $itemFinalSubtotal;

                    $preparedItems[] = [
                        'menu_item' => $menuItem,
                        'quantity' => $quantity,
                        'notes' => $item['notes'] ?? null,
                        'pricing' => $pricing,
                        'discount_total' => $itemDiscountTotal,
                        'subtotal' => $itemFinalSubtotal,
                    ];
                }

                $order = Order::create([
                    'order_code' => 'ORD-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
                    'table_id' => $table->id,
                    'session_token' => session()->getId(),
                    'customer_name' => $validated['customer_name'] ?? null,
                    'customer_phone' => $validated['customer_phone'] ?? null,
                    'customer_email' => $validated['customer_email'] ?? null,
                    'status' => 'pending_payment',
                    'payment_method' => $validated['payment_choice'] === 'cash' ? 'cash' : null,
                    'payment_status' => 'unpaid',
                    'subtotal' => $subtotal,
                    'discount_total' => $discountTotal,
                    'grand_total' => $grandTotal,
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($preparedItems as $item) {
                    $menuItem = $item['menu_item'];
                    $pricing = $item['pricing'];

                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'menu_item_id' => $menuItem->id,
                        'discount_id' => $pricing['discount_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $pricing['original_price'],
                        'discount_amount' => $item['discount_total'],
                        'subtotal' => $item['subtotal'],
                        'notes' => $item['notes'] ?? null,
                        'status' => 'pending',
                    ]);

                    KitchenQueue::create([
                        'order_item_id' => $orderItem->id,
                        'status' => 'queued',
                        'quantity' => $item['quantity'],
                        'queued_at' => now(),
                    ]);

                    $stockBefore = $menuItem->stock;
                    $menuItem->decrement('stock', $item['quantity']);
                    $menuItem->refresh();

                    StockLog::create([
                        'menu_item_id' => $menuItem->id,
                        'changed_by' => null,
                        'change_type' => 'out',
                        'quantity' => $item['quantity'],
                        'stock_before' => $stockBefore,
                        'stock_after' => $menuItem->stock,
                        'reference_id' => $order->id,
                    ]);

                    $menuItem->update([
                        'is_available' => $menuItem->stock > 0,
                    ]);
                }

                session()->forget($cartKey);

                return $order;
            });
        } catch (ValidationException $exception) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }
            throw $exception;
        } catch (\Throwable $exception) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Checkout gagal: '.$exception->getMessage()], 500);
            }

            return back()->with('error', 'Checkout gagal: '.$exception->getMessage());
        }

        // Jika pembayaran tunai, langsung kembalikan response JSON dengan order_code dan summary_url
        if ($validated['payment_choice'] === 'cash') {
            app(NotificationService::class)->notifyRole(
                'kasir',
                'cash_order_created',
                $order->id,
                "Order {$order->order_code} dari Meja {$table->table_number} menunggu pembayaran cash."
            );

            return response()->json([
                'payment_choice' => 'cash',
                'order_code' => $order->order_code,
                'status_url' => route('customer-menu.order-status', [
                    'token' => $token,
                    'order' => $order->order_code,
                ]),
                'summary_url' => route('customer-menu.order-summary', [
                    'token' => $token,
                    'order' => $order->order_code,
                ]),
            ]);
        }

        try {
            $order->load('orderItems.menuItem');
            $snapToken = (new MidtransService)->getSnapToken($order);
        } catch (\Throwable $exception) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Gagal membuat pembayaran: '.$exception->getMessage()], 500);
            }

            return redirect()
                ->route('customer-menu.index', ['token' => $token])
                ->with('error', 'Gagal membuat pembayaran: '.$exception->getMessage());
        }

        return response()->json([
            'snap_token' => $snapToken,
            'order_code' => $order->order_code,
            'summary_url' => route('customer-menu.order-summary', [
                'token' => $token,
                'order' => $order->order_code,
            ]),
        ]);
    }

    public function orderSummary(string $token, Order $order)
    {
        $table = $this->getTableByToken($token);

        abort_if($order->table_id !== $table->id, 404);

        $order->load('orderItems.menuItem', 'table');

        // Ambil semua Order Item milik order ini
        $itemIds = $order->orderItems->pluck('id');

        // Cari queue yang sedang diproses (hanya order ini)
        $preparingQueue = KitchenQueue::whereIn('order_item_id', $itemIds)
            ->where('queue_type', 'new_order')
            ->where('status', 'preparing')
            ->orderBy('updated_at')
            ->first();

        $countdownEnd = null;
        $estimatedMinutes = 0;

        if ($preparingQueue) {

            // Ambil estimasi menu paling lama
            $estimatedMinutes = $order->orderItems->max(function ($item) {
                return $item->menuItem->estimated_minutes ?? 0;
            });

            // Hitung waktu selesai
            $countdownEnd = $preparingQueue->updated_at
                ->copy()
                ->addMinutes($estimatedMinutes);
        }

        $statusPayload = $this->customerOrderStatus($order);

        return view(
            'Customer.order-summary',
            compact(
                'order',
                'table',
                'token',
                'countdownEnd',
                'estimatedMinutes',
                'statusPayload'
            )
        );
    }

    public function orderStatus(string $token, Order $order)
    {
        $table = $this->getTableByToken($token);

        abort_if($order->table_id !== $table->id, 404);

        return response()->json($this->customerOrderStatus($order));
    }

    public function countdownStatus(string $token, Order $order)
    {
        $table = $this->getTableByToken($token);
        abort_if($order->table_id !== $table->id, 404);

        $itemIds = $order->orderItems->pluck('id');

        $preparingQueue = KitchenQueue::whereIn('order_item_id', $itemIds)
            ->where('queue_type', 'new_order')
            ->where('status', 'preparing')
            ->orderBy('updated_at')
            ->first();

        $countdownEnd = null;

        if ($preparingQueue) {
            $estimatedMinutes = $order->orderItems->max(function ($item) {
                return $item->menuItem->estimated_minutes ?? 0;
            });

            $countdownEnd = $preparingQueue->updated_at
                ->copy()
                ->addMinutes($estimatedMinutes);
        }

        return response()->json([
            'has_queue' => (bool) $preparingQueue,
            'countdown_end' => $countdownEnd?->toIso8601String(),
        ]);
    }

    private function customerOrderStatus(Order $order): array
    {
        $order->loadMissing('orderItems');

        $itemIds = $order->orderItems->pluck('id');
        $hasActiveKitchenProcess = $order->payment_status === 'paid' && KitchenQueue::whereIn('order_item_id', $itemIds)
            ->whereIn('status', ['preparing', 'done'])
            ->exists();

        $stage = match (true) {
            $order->payment_status !== 'paid' => 'waiting_payment',
            $order->status === 'ready' => 'ready',
            $hasActiveKitchenProcess || in_array($order->status, ['processing', 'preparing'], true) => 'preparing',
            default => 'paid',
        };

        $copy = [
            'waiting_payment' => [
                'label' => 'Menunggu Pembayaran',
                'message' => 'Tunjukkan kode order ini ke kasir. Pesanan akan diproses setelah pembayaran dikonfirmasi.',
            ],
            'paid' => [
                'label' => 'Pembayaran Diterima',
                'message' => 'Pembayaran sudah dikonfirmasi. Pesanan akan segera masuk ke dapur.',
            ],
            'preparing' => [
                'label' => 'Pesanan Disiapkan',
                'message' => 'Dapur sedang menyiapkan pesanan kamu.',
            ],
            'ready' => [
                'label' => 'Pesanan Siap',
                'message' => 'Pesanan sudah siap. Silakan ambil atau tunggu staf mengantar ke meja.',
            ],
        ];

        return [
            'order_code' => $order->order_code,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'is_paid' => $order->payment_status === 'paid',
            'stage' => $stage,
            'label' => $copy[$stage]['label'],
            'message' => $copy[$stage]['message'],
            'steps' => [
                'created' => 'done',
                'paid' => $order->payment_status === 'paid' ? 'done' : 'current',
                'preparing' => match (true) {
                    $order->status === 'ready' => 'done',
                    $hasActiveKitchenProcess || in_array($order->status, ['processing', 'preparing'], true) => 'current',
                    default => 'pending',
                },
                'ready' => $order->status === 'ready' ? 'done' : 'pending',
            ],
        ];
    }
}
