<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi Lokasi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/brand/logoMeja.png') }}">
    <link rel="stylesheet" href="{{ asset('customer/assets/css/location.css') }}">
    
</head>
<body>
    <div class="card">
        <div id="defaultState">
            <div id="promptText">
                <div class="icon-circle">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <h1>Izinkan Akses Lokasi</h1>
                <p>Kami perlu memastikan kamu sedang berada di area coffee shop sebelum bisa melihat menu.</p>
            </div>

            <button id="btnLocate" style="display:none;">Aktifkan Lokasi</button>
            <div class="spinner" id="spinner" style="display:none;"></div>
            <div class="error" id="errorBox"></div>
        </div>

        <div id="outOfRangeState" style="display:none;">
            <div class="icon-circle danger">
                <i class="bi bi-geo-alt-fill"></i>
            </div>
            <h1>Di Luar Jangkauan</h1>
            <p id="outOfRangeMessage">Kamu berada di luar area coffee shop. Pastikan kamu sedang di lokasi coffee shop.</p>
            <button id="btnRetry">Coba Lagi</button>
        </div>
    </div>


    <script>
        const token = @json($token);
        const verifyUrl = @json(route('customer-menu.verify', $token));
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const defaultState = document.getElementById('defaultState');
        const outOfRangeState = document.getElementById('outOfRangeState');

        const btn = document.getElementById('btnLocate');
        const btnRetry = document.getElementById('btnRetry');
        const spinner = document.getElementById('spinner');
        const errorBox = document.getElementById('errorBox');
        const promptText = document.getElementById('promptText');

        function showButton() {
            defaultState.style.display = 'block';
            outOfRangeState.style.display = 'none';
            promptText.style.display = 'block';
            btn.disabled = false;
            btn.style.display = 'block';
            spinner.style.display = 'none';
            errorBox.style.display = 'none';
        }

        function showSpinner() {
            defaultState.style.display = 'block';
            outOfRangeState.style.display = 'none';
            promptText.style.display = 'block';
            btn.style.display = 'none';
            spinner.style.display = 'block';
            errorBox.style.display = 'none';
        }

        // Untuk error selain "di luar jangkauan" (permission ditolak, browser gak support, dll)
        function showError(msg) {
            defaultState.style.display = 'block';
            outOfRangeState.style.display = 'none';
            promptText.style.display = 'block';
            errorBox.textContent = msg;
            errorBox.style.display = 'block';
            spinner.style.display = 'none';
            btn.style.display = 'block';
            btn.disabled = false;
        }

        // Khusus kalau lokasi valid didapat tapi di luar radius resto
        function showOutOfRange() {
            defaultState.style.display = 'none';
            outOfRangeState.style.display = 'block';
        }

        function requestLocation() {
            if (!navigator.geolocation) {
                showError('Browser kamu tidak mendukung fitur lokasi.');
                return;
            }

            showSpinner();

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    try {
                        const res = await fetch(verifyUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude,
                            }),
                        });

                        const data = await res.json();

                        if (data.success) {
                            window.location.href = data.redirect;
                        } else {
                            showOutOfRange();
                        }
                    } catch (e) {
                        showError('Terjadi kesalahan, coba lagi.');
                    }
                },
                (error) => {
                    showError('Akses lokasi ditolak. Aktifkan izin lokasi di browser untuk melanjutkan.');
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        btn.addEventListener('click', requestLocation);
        btnRetry.addEventListener('click', requestLocation);

        if (navigator.permissions && navigator.permissions.query) {
            navigator.permissions.query({ name: 'geolocation' }).then((result) => {
                if (result.state === 'granted') {
                    requestLocation();
                } else {
                    showButton();
                }
            }).catch(() => {
                showButton();
            });
        } else {
            showButton();
        }
    </script>
</body>
</html>