<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Menu - Coffee Shop</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/brand/logoMeja.png') }}">
    <link rel="stylesheet" href="{{ asset('customer/assets/css/style.css') }}">
</head>

<body>
    <header class="cafe-header">
        <div class="img" aria-hidden="true"></div>

        <div class="container-fluid px-0 py-0 d-flex align-items-center justify-content-between">
            <div></div>

            <div class="d-flex gap-2 align-items-center icon-container">
                <a href="{{ route('customer-menu.search', ['token' => $token]) }}" class="btn btn-icon" aria-label="Cari menu">
                    <i class="bi bi-search"></i>
                </a>
                <a href="{{ route('customer-menu.cart', ['token' => $token]) }}" class="btn btn-icon position-relative" aria-label="Keranjang">
                    <i class="bi bi-bag"></i>
                    @if ($cartCount > 0)
                        <span class="badge-cart">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>

        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card-tempat">
                    <div class="card-info">
                        <h4>Meja Terakhir</h4>
                        <p>Open Today, <span>8:00 AM - 10:00 PM</span></p>
                        <div class="store-meta-row">
                            <span class="store-chip">
                                <i class="bi bi-cup-hot"></i>
                                Fresh brew
                            </span>
                            <span class="store-chip">
                                <i class="bi bi-grid-3x3-gap"></i>
                                Meja {{ $table->table_number }}
                            </span>
                        </div>
                    </div>

                    <div class="card-arrow">
                        <i class="bi bi-arrow-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="tabelSentinel"></div>
    <div class="sticky-card-tabel">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card-tabel" id="cardTabel">
                        <h4 class="text-center">Nomor Meja: <span>{{ $table->table_number }}</span></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="customer-toast" id="customerToast" role="status">
            <div class="toast-message">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
            <div class="toast-actions">
                <a href="{{ route('customer-menu.cart', ['token' => $token]) }}" class="toast-cart-link">Lihat Keranjang</a>
                <button class="toast-close" type="button" data-toast-close aria-label="Tutup">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="customer-toast" id="customerToast" role="alert">
            <div class="toast-message">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ session('error') }}</span>
            </div>
            <div class="toast-actions">
                <button class="toast-close" type="button" data-toast-close aria-label="Tutup">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>
    @endif

    <div class="category-wrapper">
        <div class="container">
            <div class="category-list">
                @if (($discountedMenuItems ?? collect())->count() > 0)
                    <a href="#discount-menu" class="cat-btn">Menu Diskon</a>
                @endif
                <a href="#all-menu" class="cat-btn active">Semua Menu</a>
                @foreach ($categories as $category)
                    @if ($category->menuItems->count() > 0)
                        <a href="#category-{{ $category->id }}" class="cat-btn">{{ $category->name }}</a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    @if (($discountedMenuItems ?? collect())->count() > 0)
        <section class="discount-section" id="discount-menu">
            <div class="container">
                <div class="category-header-section">
                    <h3 class="category-name">Menu Sedang Diskon</h3>
                    <div class="category-line"></div>
                </div>

                <div class="discount-slider-wrap">
                    <button class="discount-slider-btn prev" type="button" data-slider-target="discountSlider" data-slider-direction="-1" aria-label="Promo sebelumnya">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <div class="discount-slider-fade left"></div>
                    <div class="discount-strip" id="discountSlider">
                        @foreach ($discountedMenuItems as $promoItem)
                            @php
                                $imageUrl = $promoItem->image_url;

                                if ($imageUrl && \Illuminate\Support\Str::startsWith($imageUrl, ['http://', 'https://'])) {
                                    $imageSrc = $imageUrl;
                                } elseif ($imageUrl && \Illuminate\Support\Str::startsWith($imageUrl, 'storage/')) {
                                    $imageSrc = asset($imageUrl);
                                } elseif ($imageUrl) {
                                    $imageSrc = asset('storage/' . $imageUrl);
                                } else {
                                    $imageSrc = null;
                                }

                                $activeDiscount = $promoItem->activeDiscount();
                                $discountPercentage = (float) $activeDiscount->percentage;
                                $discountAmount = $promoItem->discountAmount();
                                $finalPrice = $promoItem->finalPrice();
                            @endphp

                            <article class="discount-card" role="button" tabindex="0" data-menu-card-trigger data-bs-toggle="modal" data-bs-target="#menuModal-{{ $promoItem->id }}" aria-label="Lihat detail {{ $promoItem->name }}">
                                <div class="discount-thumb">
                                    <span class="discount-badge">{{ rtrim(rtrim(number_format($discountPercentage, 2, ',', '.'), '0'), ',') }}% OFF</span>
                                    @if ($imageSrc)
                                        <img src="{{ $imageSrc }}" alt="{{ $promoItem->name }}">
                                    @else
                                        <div class="placeholder-visual">
                                            <i class="bi bi-cup-hot"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="discount-info">
                                    <div class="discount-title">{{ $promoItem->name }}</div>
                                    <div class="discount-meta">Hemat Rp{{ number_format($discountAmount, 0, ',', '.') }} per item</div>
                                    <div class="discount-price-row">
                                        <span class="original-price">Rp{{ number_format($promoItem->price, 0, ',', '.') }}</span><br>
                                        <span class="discounted-price">Rp{{ number_format($finalPrice, 0, ',', '.') }}</span>
                                    </div>
                                    <button class="discount-add" type="button" data-bs-toggle="modal" data-bs-target="#menuModal-{{ $promoItem->id }}">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="discount-slider-fade right"></div>
                    <button class="discount-slider-btn next" type="button" data-slider-target="discountSlider" data-slider-direction="1" aria-label="Promo berikutnya">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </section>
    @endif

    <main class="menu-section" id="all-menu">
        <div class="container py-4">
            @php
                $hasMenu = false;
            @endphp

            @foreach ($categories as $category)
                @if ($category->menuItems->count() > 0)
                    @php
                        $hasMenu = true;
                    @endphp

                    <section id="category-{{ $category->id }}" class="mb-5">
                        <div class="category-header-section">
                            <h3 class="category-name">{{ $category->name }}</h3>
                            <div class="category-line"></div>
                        </div>

                        <div class="row g-3">
                            @foreach ($category->menuItems as $menuItem)
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

                                <div class="col-6 col-md-4 col-lg-3">
                                    <article class="menu-card" role="button" tabindex="0" data-menu-card-trigger data-bs-toggle="modal" data-bs-target="#menuModal-{{ $menuItem->id }}" aria-label="Lihat detail {{ $menuItem->name }}">
                                        <div class="menu-img">
                                            @if ($hasDiscount)
                                                <span class="discount-badge">{{ rtrim(rtrim(number_format($discountPercentage, 2, ',', '.'), '0'), ',') }}% OFF</span>
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

                                        <div class="menu-body">
                                            <h6 class="menu-title">{{ $menuItem->name }}</h6>
                                            <p class="menu-desc">{{ $menuItem->description ?: 'Menu favorit coffee shop kami.' }}</p>
                                            @if ($hasDiscount)
                                                <div class="menu-price-wrapper">
                                                    <span class="original-price">Rp{{ number_format($menuItem->price, 0, ',', '.') }}</span>
                                                    <span class="discounted-price">Rp{{ number_format($finalPrice, 0, ',', '.') }}</span>
                                                </div>
                                            @else
                                                <p class="menu-price">Rp{{ number_format($menuItem->price, 0, ',', '.') }}</p>
                                            @endif
                                            <button class="btn-tambah" type="button" data-bs-toggle="modal" data-bs-target="#menuModal-{{ $menuItem->id }}">
                                                <i class="bi bi-eye"></i> Detail
                                            </button>
                                        </div>
                                    </article>
                                </div>

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
                                                        <p class="text-muted small mb-1">{{ $category->name }}</p>
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
                    </section>
                @endif
            @endforeach

            @if (!$hasMenu)
                <div class="empty-state">
                    <i class="bi bi-cup-hot"></i>
                    <h5 class="fw-bold mb-2">Belum ada menu tersedia</h5>
                    <p class="mb-0">Silakan panggil staf untuk pilihan menu hari ini.</p>
                </div>
            @endif
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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

        document.querySelectorAll('[data-toast-close]').forEach((button) => {
            button.addEventListener('click', () => {
                button.closest('.customer-toast')?.remove();
            });
        });

        const customerToast = document.getElementById('customerToast');
        if (customerToast) {
            window.setTimeout(() => {
                customerToast.remove();
            }, 5200);
        }

        const sentinel = document.getElementById('tabelSentinel');
        const cardTabel = document.getElementById('cardTabel');

        if (sentinel && cardTabel) {
            const observer = new IntersectionObserver(([entry]) => {
                cardTabel.classList.toggle('is-stuck', !entry.isIntersecting);
            }, { threshold: 0 });

            observer.observe(sentinel);
        }

        const categoryButtons = document.querySelectorAll('.cat-btn');
        const categoryList = document.querySelector('.category-list');
        let categoryDragStarted = false;
        let manualScrollLock = false;

        if (categoryList) {
            let isCategoryDragging = false;
            let dragStartX = 0;
            let dragStartScrollLeft = 0;
            let dragDistance = 0;

            categoryList.addEventListener('pointerdown', (event) => {
                if (event.pointerType === 'mouse' || (event.button !== undefined && event.button !== 0)) {
                    return;
                }

                isCategoryDragging = true;
                categoryDragStarted = false;
                dragStartX = event.clientX;
                dragDistance = 0;
                dragStartScrollLeft = categoryList.scrollLeft;
                categoryList.classList.add('is-dragging');
                categoryList.setPointerCapture?.(event.pointerId);
            });

            categoryList.addEventListener('pointermove', (event) => {
                if (!isCategoryDragging) {
                    return;
                }

                dragDistance = event.clientX - dragStartX;

                if (Math.abs(dragDistance) > 4) {
                    categoryDragStarted = true;
                    categoryList.scrollLeft = dragStartScrollLeft - dragDistance;
                    event.preventDefault();
                }
            });

            function stopCategoryDrag(event) {
                if (!isCategoryDragging) {
                    return;
                }

                isCategoryDragging = false;
                categoryList.classList.remove('is-dragging');
                categoryList.releasePointerCapture?.(event.pointerId);

                if (categoryDragStarted) {
                    window.setTimeout(() => {
                        categoryDragStarted = false;
                    }, 0);
                }
            }

            categoryList.addEventListener('pointerup', stopCategoryDrag);
            categoryList.addEventListener('pointercancel', stopCategoryDrag);
            categoryList.addEventListener('pointerleave', stopCategoryDrag);
        }

        function setActiveCategory(targetId) {
            categoryButtons.forEach((item) => {
                const isActive = item.getAttribute('href') === targetId;
                item.classList.toggle('active', isActive);

                if (isActive) {
                    item.scrollIntoView({
                        behavior: 'smooth',
                        inline: 'center',
                        block: 'nearest'
                    });
                }
            });
        }

        function stickyOffset() {
            const stickyTable = document.querySelector('.sticky-card-tabel');
            const categoryWrapper = document.querySelector('.category-wrapper');

            return (stickyTable?.offsetHeight || 0) + (categoryWrapper?.offsetHeight || 0) + 6;
        }

        categoryButtons.forEach((button) => {
            button.addEventListener('click', (event) => {
                if (categoryDragStarted) {
                    event.preventDefault();
                    return;
                }

                const targetId = button.getAttribute('href');
                const target = targetId ? document.querySelector(targetId) : null;

                if (target) {
                    event.preventDefault();

                    manualScrollLock = true;
                    const targetY = target.getBoundingClientRect().top + window.pageYOffset - stickyOffset();

                    window.scrollTo({
                        top: Math.max(targetY, 0),
                        behavior: 'smooth'
                    });

                    history.replaceState(null, '', targetId);
                    setTimeout(() => {
                        manualScrollLock = false;
                    }, 700);
                }

                setActiveCategory(targetId);
            });
        });

        const scrollSections = Array.from(categoryButtons)
            .map((button) => {
                const targetId = button.getAttribute('href');
                const target = targetId ? document.querySelector(targetId) : null;

                return target ? { id: targetId, element: target } : null;
            })
            .filter(Boolean);

        function syncActiveCategoryOnScroll() {
            if (manualScrollLock || scrollSections.length === 0) {
                return;
            }

            const probeY = window.pageYOffset + stickyOffset() + 18;
            let active = scrollSections[0];

            scrollSections.forEach((section) => {
                if (section.element.offsetTop <= probeY) {
                    active = section;
                }
            });

            setActiveCategory(active.id);
        }

        window.addEventListener('scroll', () => {
            window.requestAnimationFrame(syncActiveCategoryOnScroll);
        }, { passive: true });

        syncActiveCategoryOnScroll();

        document.querySelectorAll('[data-slider-target]').forEach((button) => {
            button.addEventListener('click', () => {
                const slider = document.getElementById(button.dataset.sliderTarget);

                if (!slider) {
                    return;
                }

                const direction = Number(button.dataset.sliderDirection || 1);
                const firstCard = slider.querySelector('.discount-card');
                const cardWidth = firstCard ? firstCard.getBoundingClientRect().width : 220;
                const gap = 16;

                slider.scrollBy({
                    left: direction * (cardWidth + gap) * 2,
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>

</html>
