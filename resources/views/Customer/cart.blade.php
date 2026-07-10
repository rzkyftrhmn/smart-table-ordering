<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Saya</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/brand/logoMeja.png') }}">
    <link rel="stylesheet" href="{{ asset('customer/assets/css/cart.css') }}">
</head>

<body @if (empty($cart)) class="cart-empty" @endif>
    <header class="cart-header sticky-top">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold">Keranjang Saya</h5>
                <a href="{{ route('customer-menu.index', ['token' => $token]) }}" class="back-link">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </header>

    <main class="cart-content">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success alert-soft">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-soft">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-soft">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if (!empty($cart))
                <div class="cart-items">
                    @foreach ($cart as $item)
                        @php
                            $imageUrl = $item['image_url'] ?? null;

                            if ($imageUrl && \Illuminate\Support\Str::startsWith($imageUrl, ['http://', 'https://'])) {
                                $imageSrc = $imageUrl;
                            } elseif ($imageUrl && \Illuminate\Support\Str::startsWith($imageUrl, 'storage/')) {
                                $imageSrc = asset($imageUrl);
                            } elseif ($imageUrl) {
                                $imageSrc = asset('storage/' . $imageUrl);
                            } else {
                                $imageSrc = null;
                            }

                            $quantity = (int) ($item['quantity'] ?? 1);
                            $originalPrice = (float) ($item['original_price'] ?? $item['price'] ?? 0);
                            $finalPrice = (float) ($item['price'] ?? $originalPrice);
                            $discountPercentage = (float) ($item['discount_percentage'] ?? 0);
                            $discountAmount = (float) ($item['discount_amount'] ?? 0);
                            $hasDiscount = $discountPercentage > 0 && $discountAmount > 0;
                        @endphp

                        <article class="cart-item">
                            <div class="item-image">
                                @if ($imageSrc)
                                    <img src="{{ $imageSrc }}" alt="{{ $item['name'] }}">
                                @else
                                    <div class="placeholder-visual">
                                        <i class="bi bi-cup-hot"></i>
                                        <span>{{ $item['name'] }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="item-details">
                                <h6 class="item-name">{{ $item['name'] }}</h6>
                                @if ($hasDiscount)
                                    <span class="discount-chip">{{ rtrim(rtrim(number_format($discountPercentage, 2, ',', '.'), '0'), ',') }}% OFF</span>
                                @endif
                                <p class="item-note">
                                    @if (!empty($item['notes']))
                                        {{ $item['notes'] }}
                                    @else
                                        Tanpa catatan
                                    @endif
                                </p>
                                <div class="item-price">
                                    Harga satuan:
                                    @if ($hasDiscount)
                                        <span class="original-price">Rp{{ number_format($originalPrice, 0, ',', '.') }}</span>
                                        <span class="discounted-price">Rp{{ number_format($finalPrice, 0, ',', '.') }}</span>
                                        <div class="item-discount">Hemat Rp{{ number_format($discountAmount * $quantity, 0, ',', '.') }}</div>
                                    @else
                                        Rp{{ number_format($finalPrice, 0, ',', '.') }}
                                    @endif
                                </div>
                                <div class="item-subtotal">Subtotal: Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</div>
                            </div>

                            <div class="item-actions">
                                <div class="quantity-control">
                                    <form action="{{ route('customer-menu.cart.update', ['token' => $token]) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="menu_item_id" value="{{ $item['menu_item_id'] }}">
                                        <input type="hidden" name="quantity" value="{{ max(1, $quantity - 1) }}">
                                        <button class="qty-btn" type="submit" @disabled($quantity <= 1) aria-label="Kurangi {{ $item['name'] }}">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                    </form>

                                    <span class="qty-value">{{ $quantity }}</span>

                                    <form action="{{ route('customer-menu.cart.update', ['token' => $token]) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="menu_item_id" value="{{ $item['menu_item_id'] }}">
                                        <input type="hidden" name="quantity" value="{{ $quantity + 1 }}">
                                        <button class="qty-btn" type="submit" aria-label="Tambah {{ $item['name'] }}">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </form>
                                </div>

                                <form action="{{ route('customer-menu.cart.remove', ['token' => $token]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="menu_item_id" value="{{ $item['menu_item_id'] }}">
                                    <button class="remove-btn" type="submit" aria-label="Hapus {{ $item['name'] }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="empty-cart py-5">
                    <div class="empty-icon mb-3">
                        <i class="bi bi-cart-x display-1"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Keranjang Kosong</h5>
                    <p class="text-muted mb-4">Yuk, tambahin menu favorit kamu dari meja {{ $table->table_number }}.</p>
                    <a href="{{ route('customer-menu.index', ['token' => $token]) }}" class="btn-browse">
                        <i class="bi bi-search"></i> Lihat Menu
                    </a>
                </div>
            @endif
        </div>
    </main>

    @if (!empty($cart))
        <div class="cart-footer">
            <div class="container">
                <form id="checkoutForm" action="{{ route('customer-menu.checkout', ['token' => $token]) }}" method="POST">
                    @csrf

                    <div class="cart-summary">
                        <div class="summary-row">
                            <span>Subtotal sebelum diskon (<span>{{ $cartCount }}</span> item)</span>
                            <span class="summary-price">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Total Diskon</span>
                            <span class="summary-price">-Rp{{ number_format($discountTotal ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Biaya Layanan</span>
                            <span class="summary-price">Rp0</span>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span class="total-price">Rp{{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>


                    <input type="hidden" name="payment_choice" id="paymentChoice">
                    <textarea name="notes" class="form-control order-notes" rows="2" placeholder="Catatan pesanan, contoh: antar kalau semua sudah siap"></textarea>

                    <button class="btn-checkout" type="submit" id="checkoutButton">
                        <span id="checkoutButtonText">Order Now</span>
                    </button>
                </form>
            </div>
        </div>
    @endif

    <div class="modal fade" id="paymentChoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Choose Payment Method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-0">
                    <p class="text-muted small mb-3">Select how you would like to complete your order.</p>

                    <button type="button" class="btn btn-dark w-100 mb-2" id="payCashButton">
                        Pay at Cashier
                    </button>

                    <button type="button" class="btn w-100" style="background-color: #d4a574; color: white;" id="payCashlessButton">
                        Pay Online
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cashlessDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-0">
                    <p class="text-muted small mb-3">Enter your contact details to receive the payment receipt.</p>

                    <input type="text" name="customer_name" form="checkoutForm" class="form-control mb-2" placeholder="Full Name">
                    <input type="tel" name="customer_phone" form="checkoutForm" class="form-control mb-2" placeholder="Phone Number">
                    <input type="email" name="customer_email" form="checkoutForm" class="form-control mb-3" placeholder="Email Address">

                    <button type="button" class="btn btn w-100" style="background-color: #d4a574; color: white;" id="continueCashlessButton">
                        Continue to Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
    <script>
        const checkoutForm = document.getElementById('checkoutForm');

        if (checkoutForm) {
            const button = document.getElementById('checkoutButton');
            const buttonText = document.getElementById('checkoutButtonText');
            const paymentChoice = document.getElementById('paymentChoice');

            const paymentChoiceModal = new bootstrap.Modal(document.getElementById('paymentChoiceModal'));
            const cashlessDetailModal = new bootstrap.Modal(document.getElementById('cashlessDetailModal'));

            checkoutForm.addEventListener('submit', function (event) {
                event.preventDefault();
                paymentChoiceModal.show();
            });

            document.getElementById('payCashButton').addEventListener('click', function () {
                paymentChoice.value = 'cash';
                paymentChoiceModal.hide();
                submitCheckout();
            });

            document.getElementById('payCashlessButton').addEventListener('click', function () {
                paymentChoice.value = 'cashless';
                paymentChoiceModal.hide();
                cashlessDetailModal.show();
            });

            document.getElementById('continueCashlessButton').addEventListener('click', function () {
                submitCheckout();
            });

            async function submitCheckout() {
                button.disabled = true;
                buttonText.textContent = 'Processing...';

                const formData = new FormData(checkoutForm);

                try {
                    const response = await fetch(checkoutForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        alert(data.message || 'Checkout failed. Please try again.');
                        button.disabled = false;
                        buttonText.textContent = 'Order Now';
                        return;
                    }

                    if (data.payment_choice === 'cash') {
                        window.location.href = data.summary_url;
                        return;
                    }

                    snap.pay(data.snap_token, {
                        onSuccess: function () {
                            window.location.href = data.summary_url;
                        },
                        onPending: function () {
                            alert('Your payment is still pending. Please complete the payment first.');
                            button.disabled = false;
                            buttonText.textContent = 'Order Now';
                        },
                        onError: function () {
                            alert('Payment failed. Please try again.');
                            button.disabled = false;
                            buttonText.textContent = 'Order Now';
                        },
                        onClose: function () {
                            button.disabled = false;
                            buttonText.textContent = 'Order Now';
                        }
                    });
                } catch (error) {
                    alert('Connection error. Please try again.');
                    button.disabled = false;
                    buttonText.textContent = 'Order Now';
                }
            }
        }
    </script>
</body>

</html>
