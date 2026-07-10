<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Menu</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/brand/logoMeja.png') }}">
    <link rel="stylesheet" href="{{ asset('customer/assets/css/search.css') }}">

</head>

<body>
    @if (session('success'))
        <div class="customer-toast" id="customerToast" role="status">
            <div class="toast-message">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
            <a href="{{ route('customer-menu.cart', ['token' => $token]) }}" class="toast-cart-link">Lihat Keranjang</a>
        </div>
    @endif

    <header class="search-header sticky-top">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('customer-menu.index', ['token' => $token]) }}" class="back-btn" aria-label="Kembali ke menu">
                    <i class="bi bi-arrow-left"></i>
                </a>

                <form action="{{ route('customer-menu.search', ['token' => $token]) }}" method="GET" class="search-box flex-grow-1">
                    <button type="submit" class="search-submit" aria-label="Cari">
                        <i class="bi bi-search"></i>
                    </button>
                    <input type="text" name="q" value="{{ $keyword }}" class="form-control search-input" id="liveSearchInput" placeholder="What are you craving today?" autocomplete="off" autofocus>
                    <div class="suggestion-panel" id="suggestionPanel"></div>
                </form>

                <a href="{{ route('customer-menu.cart', ['token' => $token]) }}" class="cart-link position-relative" aria-label="Keranjang">
                    <i class="bi bi-bag"></i>
                    @if ($cartCount > 0)
                        <span class="badge-cart">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </header>

    <main class="menu-list-container">
        <div class="container">
            <div class="section-header">
                <h5 class="section-title">
                    @if ($keyword)
                        Hasil Pencarian
                    @else
                        Semua Menu
                    @endif
                </h5>
                <p class="section-meta">
                    @if ($keyword)
                        <span id="searchMeta">"{{ $keyword }}" - {{ $menuItems->count() }} menu ditemukan</span>
                    @else
                        <span id="searchMeta">{{ $menuItems->count() }} menu tersedia</span>
                    @endif
                </p>
            </div>

            @if ($menuItems->count() > 0)
                <div class="quick-suggestions" id="quickSuggestions">
                    @foreach ($menuItems->take(10) as $quickItem)
                        <button class="quick-chip" type="button" data-suggestion-value="{{ $quickItem->name }}">{{ $quickItem->name }}</button>
                    @endforeach
                </div>

                <div class="menu-list" id="menuList">
                    @foreach ($menuItems as $menuItem)
                        @php
                            $imageUrl = $menuItem->image_url;

                            if ($imageUrl && \Illuminate\Support\Str::startsWith($imageUrl, ['http://', 'https://'])) {
                                $imageSrc = $imageUrl;
                            } elseif ($imageUrl && \Illuminate\Support\Str::startsWith($imageUrl, 'storage/')) {
                                $imageSrc = asset($imageUrl);
                            } elseif ($imageUrl) {
                                $imageSrc = asset('storage/' . $imageUrl);
                            } else {
                                $imageSrc = null;
                            }

                            $activeDiscount = $menuItem->activeDiscount();
                            $hasDiscount = $activeDiscount !== null;
                            $discountPercentage = $hasDiscount ? (float) $activeDiscount->percentage : 0;
                            $discountAmount = $menuItem->discountAmount();
                            $finalPrice = $menuItem->finalPrice();
                        @endphp

                        <article
                            class="menu-list-item"
                            role="button"
                            tabindex="0"
                            data-menu-card-trigger
                            data-bs-toggle="modal"
                            data-bs-target="#menuModal-{{ $menuItem->id }}"
                            aria-label="Lihat detail {{ $menuItem->name }}"
                            data-menu-item
                            data-name="{{ \Illuminate\Support\Str::lower($menuItem->name) }}"
                            data-category="{{ \Illuminate\Support\Str::lower($menuItem->category->name ?? 'menu') }}"
                            data-description="{{ \Illuminate\Support\Str::lower($menuItem->description ?? '') }}"
                            data-display-name="{{ $menuItem->name }}"
                            data-display-category="{{ $menuItem->category->name ?? 'Menu' }}"
                            data-display-price="Rp{{ number_format($hasDiscount ? $finalPrice : $menuItem->price, 0, ',', '.') }}"
                        >
                            <div class="menu-thumb">
                                @if ($hasDiscount)
                                    <span class="discount-badge">{{ rtrim(rtrim(number_format($discountPercentage, 2, ',', '.'), '0'), ',') }}%</span>
                                @endif

                                @if ($imageSrc)
                                    <img src="{{ $imageSrc }}" alt="{{ $menuItem->name }}">
                                @else
                                    <div class="placeholder-visual">
                                        <i class="bi bi-cup-hot"></i>
                                        <span>{{ $menuItem->name }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="menu-info">
                                <h6 class="menu-name">{{ $menuItem->name }}</h6>
                                <div class="menu-category">{{ $menuItem->category->name ?? 'Menu' }}</div>
                                <p class="menu-desc">{{ $menuItem->description ?: 'Menu favorit coffee shop kami.' }}</p>
                                <div class="menu-price-row">
                                    <div class="price-wrapper">
                                        @if ($hasDiscount)
                                            <span class="original-price">Rp{{ number_format($menuItem->price, 0, ',', '.') }}</span>
                                            <span class="discounted-price">Rp{{ number_format($finalPrice, 0, ',', '.') }}</span>
                                        @else
                                            <span class="discounted-price">Rp{{ number_format($menuItem->price, 0, ',', '.') }}</span>
                                        @endif
                                    </div>

                                    <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#menuModal-{{ $menuItem->id }}">
                                        Detail
                                    </button>
                                </div>
                            </div>
                        </article>

                        <div class="modal fade" id="menuModal-{{ $menuItem->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0">
                                    <form action="{{ route('customer-menu.cart.add', ['token' => $token]) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="menu_item_id" value="{{ $menuItem->id }}">
                                        <input type="hidden" name="quantity" id="quantityInput-{{ $menuItem->id }}" value="1" data-unit-price="{{ $finalPrice }}">

                                        <div class="modal-body p-0">
                                            <div class="modal-image-wrapper position-relative">
                                                @if ($imageSrc)
                                                    <img src="{{ $imageSrc }}" class="img-fluid w-100" alt="{{ $menuItem->name }}">
                                                @else
                                                    <div class="placeholder-visual">
                                                        <i class="bi bi-cup-hot"></i>
                                                        <span>{{ $menuItem->name }}</span>
                                                    </div>
                                                @endif

                                                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Tutup">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>

                                            <div class="p-4">
                                                <h5 class="fw-bold mb-1">{{ $menuItem->name }}</h5>
                                                <p class="text-muted small mb-1">{{ $menuItem->category->name ?? 'Menu' }}</p>
                                                @if ($hasDiscount)
                                                    <div class="modal-discount-row mb-1">
                                                        <span class="original-price">Rp{{ number_format($menuItem->price, 0, ',', '.') }}</span>
                                                        <span class="fw-bold" style="color: #d4a574;">Rp{{ number_format($finalPrice, 0, ',', '.') }}</span>
                                                        <span class="badge">{{ rtrim(rtrim(number_format($discountPercentage, 2, ',', '.'), '0'), ',') }}% OFF</span>
                                                    </div>
                                                    <p class="text-muted small mb-2">Hemat Rp{{ number_format($discountAmount, 0, ',', '.') }} per item</p>
                                                @else
                                                    <p class="fw-bold mb-2" style="color: #d4a574;">Rp{{ number_format($menuItem->price, 0, ',', '.') }}</p>
                                                @endif
                                                <p class="text-muted small mb-3">{{ $menuItem->description ?: 'Menu favorit coffee shop kami.' }}</p>

                                                <div class="mb-3">
                                                    <label for="notes-{{ $menuItem->id }}" class="form-label small fw-bold text-uppercase">Catatan</label>
                                                    <textarea id="notes-{{ $menuItem->id }}" name="notes" class="form-control" rows="2" placeholder="Opsional, contoh: less sugar"></textarea>
                                                </div>

                                                <hr>

                                                <div class="d-flex justify-content-between align-items-center mb-4">
                                                    <div>
                                                        <label class="fw-bold mb-0">Total Order</label>
                                                        <div class="modal-total-price" id="modalTotal-{{ $menuItem->id }}">Rp{{ number_format($finalPrice, 0, ',', '.') }}</div>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <button class="btn btn-outline-secondary btn-sm qty-btn" type="button" data-qty-action="minus" data-target-id="{{ $menuItem->id }}" aria-label="Kurangi">
                                                            <i class="bi bi-dash"></i>
                                                        </button>
                                                        <span class="fw-bold" id="qtyValue-{{ $menuItem->id }}">1</span>
                                                        <button class="btn btn-outline-secondary btn-sm qty-btn" type="button" data-qty-action="plus" data-target-id="{{ $menuItem->id }}" data-max="{{ $menuItem->stock }}" aria-label="Tambah">
                                                            <i class="bi bi-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="d-grid gap-2">
                                                    <button class="btn btn-dark btn-lg title-button" type="submit">
                                                        <span>Tambah</span>
                                                        <span id="submitTotal-{{ $menuItem->id }}">Rp{{ number_format($finalPrice, 0, ',', '.') }}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state" id="emptyState">
                    <i class="bi bi-search"></i>
                    <h5 class="mb-2">Menu tidak ditemukan</h5>
                    <p class="mb-4">Coba kata kunci lain atau lihat semua menu dari meja {{ $table->table_number }}.</p>
                    <a href="{{ route('customer-menu.index', ['token' => $token]) }}" class="btn-browse">
                        <i class="bi bi-grid"></i> Lihat Menu
                    </a>
                </div>
            @endif

            @if ($menuItems->count() > 0)
                <div class="empty-state d-none" id="liveEmptyState">
                    <i class="bi bi-search"></i>
                    <h5 class="mb-2">Menu tidak ditemukan</h5>
                    <p class="mb-4">Coba kata kunci lain atau pilih suggestion yang tersedia.</p>
                    <button type="button" class="btn-browse border-0" id="clearSearchButton">
                        <i class="bi bi-x-circle"></i> Bersihkan Pencarian
                    </button>
                </div>
            @endif
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const customerToast = document.getElementById('customerToast');
        if (customerToast) {
            window.setTimeout(() => {
                customerToast.remove();
            }, 5200);
        }

        function formatRupiah(value) {
            return 'Rp' + new Intl.NumberFormat('id-ID').format(Math.max(0, Math.round(value)));
        }

        function updateModalTotal(menuId) {
            const qtyInput = document.getElementById(`quantityInput-${menuId}`);
            const totalLabel = document.getElementById(`modalTotal-${menuId}`);
            const submitTotal = document.getElementById(`submitTotal-${menuId}`);

            if (!qtyInput) {
                return;
            }

            const qty = Number(qtyInput.value || 1);
            const unitPrice = Number(qtyInput.dataset.unitPrice || 0);
            const total = formatRupiah(qty * unitPrice);

            if (totalLabel) {
                totalLabel.textContent = total;
            }

            if (submitTotal) {
                submitTotal.textContent = total;
            }
        }

        document.querySelectorAll('[data-qty-action]').forEach((button) => {
            button.addEventListener('click', () => {
                const menuId = button.dataset.targetId;
                const action = button.dataset.qtyAction;
                const max = Number(button.dataset.max || 999);
                const qtyValue = document.getElementById(`qtyValue-${menuId}`);
                const qtyInput = document.getElementById(`quantityInput-${menuId}`);

                if (!qtyInput || !qtyValue) {
                    return;
                }

                let qty = Number(qtyInput.value || 1);

                if (action === 'minus' && qty > 1) {
                    qty -= 1;
                }

                if (action === 'plus' && qty < max) {
                    qty += 1;
                }

                qtyInput.value = qty;
                qtyValue.textContent = qty;
                updateModalTotal(menuId);
            });
        });

        document.querySelectorAll('.modal').forEach((modal) => {
            modal.addEventListener('show.bs.modal', () => {
                const qtyInput = modal.querySelector('input[name="quantity"]');
                const qtyValue = modal.querySelector('[id^="qtyValue-"]');

                if (!qtyInput) {
                    return;
                }

                const menuId = qtyInput.id.replace('quantityInput-', '');
                qtyInput.value = 1;

                if (qtyValue) {
                    qtyValue.textContent = '1';
                }

                updateModalTotal(menuId);
            });
        });

        document.querySelectorAll('[data-menu-card-trigger]').forEach((card) => {
            card.addEventListener('keydown', (event) => {
                if (!['Enter', ' '].includes(event.key)) {
                    return;
                }

                event.preventDefault();
                const modalSelector = card.dataset.bsTarget;
                const modalElement = modalSelector ? document.querySelector(modalSelector) : null;

                if (modalElement) {
                    bootstrap.Modal.getOrCreateInstance(modalElement).show();
                }
            });
        });

        const liveSearchInput = document.getElementById('liveSearchInput');
        const suggestionPanel = document.getElementById('suggestionPanel');
        const searchMeta = document.getElementById('searchMeta');
        const menuItems = Array.from(document.querySelectorAll('[data-menu-item]'));
        const liveEmptyState = document.getElementById('liveEmptyState');
        const menuList = document.getElementById('menuList');
        const quickSuggestions = document.getElementById('quickSuggestions');
        const clearSearchButton = document.getElementById('clearSearchButton');
        const totalMenus = menuItems.length;

        function normalize(value) {
            return String(value || '').toLowerCase().trim();
        }

        function searchableText(item) {
            return [
                item.dataset.name,
                item.dataset.category,
                item.dataset.description
            ].join(' ');
        }

        function setMeta(query, count) {
            if (!searchMeta) {
                return;
            }

            searchMeta.textContent = query
                ? `"${query}" - ${count} menu ditemukan`
                : `${totalMenus} menu tersedia`;
        }

        function renderSuggestions(query, matches) {
            if (!suggestionPanel) {
                return;
            }

            if (!query || matches.length === 0) {
                suggestionPanel.classList.remove('is-visible');
                suggestionPanel.innerHTML = '';
                return;
            }

            suggestionPanel.innerHTML = matches.slice(0, 7).map((item) => `
                <button class="suggestion-item" type="button" data-suggestion-value="${item.dataset.displayName}">
                    <span>
                        <span class="suggestion-name">${item.dataset.displayName}</span>
                        <span class="suggestion-category">${item.dataset.displayCategory}</span>
                    </span>
                    <span class="suggestion-price">${item.dataset.displayPrice}</span>
                </button>
            `).join('');

            suggestionPanel.classList.add('is-visible');
        }

        function applySearch(value) {
            const query = normalize(value);
            const matches = [];

            menuItems.forEach((item) => {
                const isMatch = !query || searchableText(item).includes(query);
                item.classList.toggle('is-hidden', !isMatch);

                if (isMatch) {
                    matches.push(item);
                }
            });

            setMeta(query, matches.length);
            renderSuggestions(query, matches);

            if (liveEmptyState && menuList) {
                liveEmptyState.classList.toggle('d-none', matches.length > 0);
                menuList.classList.toggle('d-none', matches.length === 0);
            }

            if (quickSuggestions) {
                quickSuggestions.classList.toggle('d-none', Boolean(query));
            }
        }

        if (liveSearchInput) {
            liveSearchInput.closest('form')?.addEventListener('submit', (event) => {
                event.preventDefault();
                applySearch(liveSearchInput.value);
                suggestionPanel?.classList.remove('is-visible');
                liveSearchInput.blur();
            });

            liveSearchInput.addEventListener('input', () => {
                applySearch(liveSearchInput.value);
            });

            liveSearchInput.addEventListener('focus', () => {
                applySearch(liveSearchInput.value);
            });

            applySearch(liveSearchInput.value);
        }

        document.addEventListener('click', (event) => {
            const suggestionButton = event.target.closest('[data-suggestion-value]');

            if (suggestionButton && liveSearchInput) {
                liveSearchInput.value = suggestionButton.dataset.suggestionValue;
                applySearch(liveSearchInput.value);
                suggestionPanel?.classList.remove('is-visible');
                return;
            }

            if (!event.target.closest('.search-box')) {
                suggestionPanel?.classList.remove('is-visible');
            }
        });

        if (clearSearchButton && liveSearchInput) {
            clearSearchButton.addEventListener('click', () => {
                liveSearchInput.value = '';
                applySearch('');
                liveSearchInput.focus();
            });
        }
    </script>
</body>

</html>
