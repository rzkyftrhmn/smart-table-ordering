<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/brand/logoMeja.png') }}">
    <link rel="stylesheet" href="{{ asset('customer/assets/css/summary.css') }}">
    
</head>

<body>
    @php
        $statusPayload = $statusPayload ?? [
            'stage' => $order->payment_status === 'paid' ? 'paid' : 'waiting_payment',
            'label' => $order->payment_status === 'paid' ? 'Pembayaran Diterima' : 'Menunggu Pembayaran',
            'message' => $order->payment_status === 'paid'
                ? 'Pembayaran sudah dikonfirmasi. Pesanan akan segera diproses.'
                : 'Tunjukkan kode order ini ke kasir. Pesanan akan diproses setelah pembayaran dikonfirmasi.',
            'steps' => [
                'created' => 'done',
                'paid' => $order->payment_status === 'paid' ? 'done' : 'current',
                'preparing' => 'pending',
                'ready' => $order->status === 'ready' ? 'done' : 'pending',
            ],
        ];
        $stepClass = fn ($step) => in_array($statusPayload['steps'][$step] ?? 'pending', ['done', 'current'], true)
            ? $statusPayload['steps'][$step]
            : '';
    @endphp
    <main class="page">
        <header class="topbar">
            <h1>Order Status</h1>
        </header>

        <div class="content">
            <div class="order-type">
                <span>Order Type</span>
                <strong>
                    Dine In
                    <span class="check"><i class="bi bi-check-lg"></i></span>
                </strong>
            </div>

            <section class="status-card" aria-live="polite">
                <div class="status-top">
                    <div>
                        <div class="status-kicker">Status Pesanan</div>
                        <h2 class="status-title" id="orderStatusLabel">{{ $statusPayload['label'] }}</h2>
                    </div>
                    <div class="status-icon" id="orderStatusIcon"><i class="bi bi-receipt-cutoff"></i></div>
                </div>
                <p class="status-message" id="orderStatusMessage">{{ $statusPayload['message'] }}</p>
                <div class="status-code">
                    <span>Kode order</span>
                    <strong id="orderCodeText">{{ $order->order_code }}</strong>
                    <button class="copy-code-btn" id="copyOrderCode" type="button" aria-label="Copy order code">
                        <i class="bi bi-copy"></i>
                    </button>
                </div>
            </section>

            <section class="timeline" aria-label="Order progress">
                <div class="timeline-step {{ $stepClass('created') }}" data-step="created">
                    <span class="timeline-dot"><i class="bi bi-check-lg"></i></span>
                    <div class="timeline-label">Order Dibuat</div>
                </div>
                <div class="timeline-step {{ $stepClass('paid') }}" data-step="paid">
                    <span class="timeline-dot"><i class="bi bi-check-lg"></i></span>
                    <div class="timeline-label">Pembayaran</div>
                </div>
                <div class="timeline-step {{ $stepClass('preparing') }}" data-step="preparing">
                    <span class="timeline-dot"><i class="bi bi-check-lg"></i></span>
                    <div class="timeline-label">Dapur Proses</div>
                </div>
                <div class="timeline-step {{ $stepClass('ready') }}" data-step="ready">
                    <span class="timeline-dot"><i class="bi bi-check-lg"></i></span>
                    <div class="timeline-label">Siap</div>
                </div>
            </section>

            <div class="meta-grid">
                <div>
                    <div class="meta-label">Date</div>
                    <div class="meta-value">{{ $order->created_at->format('d M Y, H:i') }}</div>
                </div>

                <div class="text-end">
                    <div class="meta-label">Order Number</div>
                    <div class="meta-value order-code">
                        <i class="bi bi-files"></i>
                        {{ $order->order_code }}
                    </div>
                </div>
            </div>

            <div class="location-row">
                <i class="bi bi-shop"></i>
                <div>
                    <div class="meta-label">Outlet Location</div>
                    <div class="meta-value">Meja Terakhir Coffee</div>
                </div>
            </div>

            <div class="location-row">
                <i class="bi bi-cup-hot"></i>
                <div>
                    <div class="meta-label">Table Number</div>
                    <div class="meta-value">{{ $table->table_number ?? $table->name ?? '-' }}</div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3" id="countdown-card" style="display: none;">
                <div class="card-body text-center">
                    <small class="text-muted">Estimasi Pesanan Siap</small>
                    <h2 id="countdown" class="fw-bold text-warning mb-0 mt-2">--:--</h2>
                </div>
            </div>

            <section class="section">
                <h2 class="section-title">Ordered Items</h2>

                @foreach ($order->orderItems as $item)
                    <div class="item">
                        <div>
                            <div class="item-name">
                                <b>{{ $item->quantity }}x</b> {{ $item->menuItem->name ?? 'Menu' }}
                            </div>

                            @if (!empty($item->notes))
                                <div class="item-note">{{ $item->notes }}</div>
                            @endif
                        </div>

                        <div class="item-price">
                            Rp{{ number_format($item->subtotal, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </section>

            <section class="total-box">
                <div class="total-row">
                    <span>Subtotal <span class="muted">({{ $order->orderItems->sum('quantity') }} item)</span></span>
                    <span>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>

                <div class="total-row">
                    <span>Diskon</span>
                    <span>-Rp{{ number_format($order->discount_total, 0, ',', '.') }}</span>
                </div>

                <div class="total-row">
                    <span>Payment Method</span>
                    <span class="payment-method">{{ strtoupper($order->payment_method ?? '-') }}</span>
                </div>

                <div class="grand-total">
                    <span>Total</span>
                    <span class="amount">Rp{{ number_format($order->grand_total, 0, ',', '.') }}</span>
                </div>
            </section>

            <div class="spacer-box"></div>
        </div>

        <div class="bottom-actions">
            <a href="{{ route('customer-menu.index', ['token' => $token]) }}" class="btn-action btn-main">
                New Order
            </a>
        </div>
    </main>
    <div class="customer-toast" id="customerStatusToast" role="status" aria-live="polite">
        <div class="customer-toast-icon" id="customerToastIcon"><i class="bi bi-bell"></i></div>
        <div>
            <div class="customer-toast-title" id="customerToastTitle">Status diperbarui</div>
            <div class="customer-toast-message" id="customerToastMessage">Pesanan kamu punya update baru.</div>
        </div>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const card = document.getElementById("countdown-card");
        const countdownEl = document.getElementById("countdown");
        const statusLabel = document.getElementById("orderStatusLabel");
        const statusMessage = document.getElementById("orderStatusMessage");
        const statusIcon = document.getElementById("orderStatusIcon");
        const orderCodeText = document.getElementById("orderCodeText");
        const copyOrderCode = document.getElementById("copyOrderCode");
        const toast = document.getElementById("customerStatusToast");
        const toastIcon = document.getElementById("customerToastIcon");
        const toastTitle = document.getElementById("customerToastTitle");
        const toastMessage = document.getElementById("customerToastMessage");
        let finishTime = null;
        let tickInterval = null;
        let lastStage = @json($statusPayload['stage']);
        let toastTimer = null;
        let audioContext = null;

        const statusIcons = {
            waiting_payment: 'bi-cash-coin',
            paid: 'bi-check-circle-fill',
            preparing: 'bi-cup-hot',
            ready: 'bi-bag-check',
        };

        const toastCopy = {
            paid: {
                title: 'Pembayaran diterima',
                message: 'Pesanan kamu akan segera masuk ke dapur.',
                icon: 'bi-check-circle-fill',
            },
            preparing: {
                title: 'Pesanan diproses',
                message: 'Dapur sedang menyiapkan pesanan kamu.',
                icon: 'bi-cup-hot',
            },
            ready: {
                title: 'Pesanan siap',
                message: 'Silakan ambil pesanan atau tunggu staf mengantar ke meja.',
                icon: 'bi-bag-check',
            },
        };

        function unlockSound() {
            if (audioContext) {
                if (audioContext.state === 'suspended') {
                    audioContext.resume().catch(function() {});
                }

                return;
            }

            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;

            audioContext = new AudioContext();
            if (audioContext.state === 'suspended') {
                audioContext.resume().catch(function() {});
            }
        }

        ['pointerdown', 'touchstart', 'keydown', 'click'].forEach(function(eventName) {
            document.addEventListener(eventName, unlockSound, {
                once: true,
                passive: true,
            });
        });

        function playStatusSound() {
            unlockSound();

            if (!audioContext || audioContext.state !== 'running') {
                return;
            }

            const startAt = audioContext.currentTime;
            const gain = audioContext.createGain();
            gain.gain.setValueAtTime(0.0001, startAt);
            gain.gain.exponentialRampToValueAtTime(0.08, startAt + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, startAt + 0.34);
            gain.connect(audioContext.destination);

            [660, 880].forEach(function(frequency, index) {
                const oscillator = audioContext.createOscillator();
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(frequency, startAt + (index * 0.11));
                oscillator.connect(gain);
                oscillator.start(startAt + (index * 0.11));
                oscillator.stop(startAt + 0.18 + (index * 0.11));
            });
        }

        function showCustomerToast(stage, fallbackLabel, fallbackMessage) {
            if (!toast) return;

            const copy = toastCopy[stage] || {
                title: fallbackLabel || 'Status diperbarui',
                message: fallbackMessage || 'Pesanan kamu punya update baru.',
                icon: statusIcons[stage] || 'bi-bell',
            };

            toastTitle.textContent = copy.title;
            toastMessage.textContent = copy.message;
            toastIcon.innerHTML = `<i class="bi ${copy.icon}"></i>`;
            toast.classList.add('show');

            clearTimeout(toastTimer);
            toastTimer = setTimeout(function() {
                toast.classList.remove('show');
            }, 4200);

            playStatusSound();
            if (navigator.vibrate) {
                navigator.vibrate(stage === 'ready' ? [120, 70, 120] : [120]);
            }
        }

        function updateStatusUI(data) {
            if (!data) return;

            statusLabel.textContent = data.label || 'Status Pesanan';
            statusMessage.textContent = data.message || '';
            statusIcon.innerHTML = `<i class="bi ${statusIcons[data.stage] || 'bi-receipt-cutoff'}"></i>`;

            ['created', 'paid', 'preparing', 'ready'].forEach(function(step) {
                const element = document.querySelector(`[data-step="${step}"]`);
                if (!element) return;
                element.classList.remove('done', 'current');

                const state = data.steps && data.steps[step] ? data.steps[step] : 'pending';
                if (state === 'done' || state === 'current') {
                    element.classList.add(state);
                }
            });
        }

        if (copyOrderCode && orderCodeText) {
            function showCopySuccess() {
                copyOrderCode.classList.add('copied');
                copyOrderCode.innerHTML = '<i class="bi bi-check-lg"></i>';

                setTimeout(function() {
                    copyOrderCode.classList.remove('copied');
                    copyOrderCode.innerHTML = '<i class="bi bi-copy"></i>';
                }, 1400);
            }

            function fallbackCopy(text) {
                const temp = document.createElement('textarea');
                temp.value = text;
                temp.setAttribute('readonly', '');
                temp.style.position = 'fixed';
                temp.style.left = '-9999px';
                temp.style.top = '0';
                document.body.appendChild(temp);
                temp.focus();
                temp.select();

                let copied = false;
                try {
                    copied = document.execCommand('copy');
                } catch (error) {
                    copied = false;
                }

                temp.remove();
                return copied;
            }

            copyOrderCode.addEventListener('click', async function() {
                const code = orderCodeText.textContent.trim();

                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(code);
                        showCopySuccess();
                        return;
                    }

                    if (fallbackCopy(code)) {
                        showCopySuccess();
                        return;
                    }

                    window.prompt('Copy kode order:', code);
                } catch (error) {
                    if (fallbackCopy(code)) {
                        showCopySuccess();
                        return;
                    }

                    window.prompt('Copy kode order:', code);
                }
            });
        }

        async function pollOrderStatus() {
            try {
                const res = await fetch("{{ route('customer-menu.order-status', ['token' => $token, 'order' => $order->order_code]) }}", {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!res.ok) return;

                const data = await res.json();
                updateStatusUI(data);

                if (data.stage && data.stage !== lastStage) {
                    showCustomerToast(data.stage, data.label, data.message);
                    lastStage = data.stage;
                }
            } catch (e) {
                console.error("gagal ambil status pesanan", e);
            }
        }

        function tick() {
            if (!finishTime) return;
            const distance = finishTime - Date.now();

            if (distance <= 0) {
                countdownEl.innerHTML = "Pesanan Hampir Siap";
                clearInterval(tickInterval);
                tickInterval = null;
                return;
            }

            const minutes = Math.floor(distance / 60000);
            const seconds = Math.floor((distance % 60000) / 1000);
            countdownEl.innerHTML = String(minutes).padStart(2,'0') + ":" + String(seconds).padStart(2,'0');
        }

        async function pollStatus() {
            try {
                const res = await fetch("{{ route('customer.order.countdown', ['token' => $token, 'order' => $order->id]) }}");
                const data = await res.json();

                if (data.has_queue && data.countdown_end) {
                    card.style.display = "block";
                    finishTime = new Date(data.countdown_end).getTime();

                    if (!tickInterval) {
                        tick();
                        tickInterval = setInterval(tick, 1000);
                    }
                } else {
                    card.style.display = "none";
                }
            } catch (e) {
                console.error("gagal ambil status countdown", e);
            }
        }

        pollStatus();
        pollOrderStatus();
        setInterval(pollStatus, 5000); // cek ke server tiap 5 detik
        setInterval(pollOrderStatus, 3000);
    });
    </script>
</body>
</html>
