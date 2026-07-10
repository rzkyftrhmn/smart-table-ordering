@extends('layouts.admin')
@section('content')
<div class="side-app">
    <div class="row">
        <div class="col-12 col-sm-12 mt-5">

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title mb-0">Restaurant Location Settings</h3>
                </div>
                <div class="card-body">

                    <p class="text-muted">
                        Click on the map or drag the marker to set the restaurant's location point.
                        Customers who scan the QR code outside this radius won't be able to access the menu.
                    </p>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        @if ($location->latitude == 0 && $location->longitude == 0)
                            <span class="badge bg-warning text-dark">Location not set</span>
                        @else
                            <span class="badge bg-success">Location saved</span>
                        @endif

                        <button type="button" id="btnUseCurrentLocation" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-location-arrow"></i> Use Current Location
                        </button>
                    </div>

                    <div id="map" style="height: 400px; border-radius: 12px; overflow: hidden;" class="border mb-3  mt-4"></div>

                    <form action="{{ route('admin.restaurant-location.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Latitude</label>
                                    <input
                                        type="text"
                                        id="latitude"
                                        name="latitude"
                                        class="form-control"
                                        value="{{ old('latitude', $location->latitude) }}"
                                        readonly>

                                    @error('latitude')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Longitude</label>
                                    <input
                                        type="text"
                                        id="longitude"
                                        name="longitude"
                                        class="form-control"
                                        value="{{ old('longitude', $location->longitude) }}"
                                        readonly>

                                    @error('longitude')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Allowed radius (meters)</label>
                            <input
                                type="number"
                                id="radius_meters_input"
                                name="radius_meters"
                                class="form-control"
                                value="{{ old('radius_meters', $location->radius_meters) }}"
                                min="10"
                                max="5000">

                            @error('radius_meters')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <button class="btn btn-primary">
                            Save Location
                        </button>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const initialLat = {{ $location->latitude }};
    const initialLng = {{ $location->longitude }};
    const initialRadius = {{ $location->radius_meters }};

    let map;
    let marker;
    let radiusCircle;

    function updateInputs(lat, lng) {
        document.getElementById('latitude').value = lat.toFixed(7);
        document.getElementById('longitude').value = lng.toFixed(7);
    }

    function initMap(lat, lng) {
        map = L.map('map').setView([lat, lng], 17);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);

        radiusCircle = L.circle([lat, lng], {
            radius: getRadiusValue(),
            color: '#0d6efd',
            fillColor: '#0d6efd',
            fillOpacity: 0.15,
            weight: 2,
        }).addTo(map);

        updateInputs(lat, lng);

        marker.on('dragend', function (e) {
            const pos = e.target.getLatLng();
            updateInputs(pos.lat, pos.lng);
            radiusCircle.setLatLng(pos);
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            updateInputs(e.latlng.lat, e.latlng.lng);
            radiusCircle.setLatLng(e.latlng);
        });
    }

    function getRadiusValue() {
        const val = parseInt(document.getElementById('radius_meters_input').value, 10);
        return isNaN(val) ? 100 : val;
    }

    function moveToPosition(lat, lng) {
        map.setView([lat, lng], 17);
        marker.setLatLng([lat, lng]);
        radiusCircle.setLatLng([lat, lng]);
        updateInputs(lat, lng);
    }

    // Update circle real-time saat admin ganti angka radius
    document.getElementById('radius_meters_input').addEventListener('input', function () {
        if (radiusCircle) {
            radiusCircle.setRadius(getRadiusValue());
        }
    });

    // Gunakan Lokasi Saat Ini
    document.getElementById('btnUseCurrentLocation').addEventListener('click', function () {
        if (!navigator.geolocation) {
            alert('Browser tidak mendukung Geolocation.');
            return;
        }

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Mencari lokasi...';

        navigator.geolocation.getCurrentPosition(
            function (position) {
                moveToPosition(position.coords.latitude, position.coords.longitude);
                btn.disabled = false;
                btn.innerHTML = originalText;
            },
            function (error) {
                console.log(error);
                alert('Gagal mengambil lokasi. Pastikan izin lokasi browser sudah diaktifkan.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    });

    // Inisialisasi awal map
    if (initialLat === 0 && initialLng === 0) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    initMap(position.coords.latitude, position.coords.longitude);
                },
                function (error) {
                    console.log(error);
                    initMap(-6.914744, 107.609810);
                }
            );
        } else {
            alert('Browser tidak mendukung Geolocation.');
            initMap(-6.914744, 107.609810);
        }
    } else {
        initMap(initialLat, initialLng);
    }
</script>
@endsection