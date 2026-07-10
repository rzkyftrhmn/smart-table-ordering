@extends('layouts.admin')
@section('content')
<div class="side-app">
    <div class="row">
        <div class="col-12 col-sm-12">
            <div class="card mt-5">
                <div class="card-header">
                    <h3 class="card-title mb-0">Menu List</h3>
                    <div class="ms-auto pageheader-btn">
                        <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#importMenuModal">
                            <span>
                                <i class="fe fe-upload"></i>
                            </span> Import Menu
                        </button>

                        <a href="{{ route('menu.create') }}" class="btn btn-primary btn-icon text-white me-2">
                            <span>
                                <i class="fe fe-plus"></i>
                            </span> Add Menu
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3 d-flex justify-content-end">
                        <div class="input-group" style="max-width: 350px;">
                            <input type="text" name="search" class="form-control" 
                                placeholder="Search By Menu Name..." value="{{ $search }}">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table border text-nowrap text-md-nowrap table-bordered mg-b-0">
                            <thead class="border-top">
                            <tr>
                                <th>No</th>
                                <th>Image</th>
                                <th>Menu Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Estimate</th>
                                <th>Stock</th>
                                <th>Available</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($menuItems as $menuItem)
                                <tr>
                                    <td>{{ $menuItems->firstItem() + $loop->index }}</td>
                                    <td>
                                        @if ($menuItem->image_url)
                                            @php
                                                $imageUrl = $menuItem->image_url;

                                                if (\Illuminate\Support\Str::startsWith($imageUrl, ['http://', 'https://'])) {
                                                    $imageSrc = $imageUrl;
                                                } elseif (\Illuminate\Support\Str::startsWith($imageUrl, 'storage/')) {
                                                    $imageSrc = asset($imageUrl);
                                                } else {
                                                    $imageSrc = asset('storage/' . $imageUrl);
                                                }
                                            @endphp

                                            <img src="{{ $imageSrc }}" alt="{{ $menuItem->name }}" width="60" height="60" style="object-fit: cover; border-radius: 6px;">
                                        @else
                                            <span class="text-muted">No image</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $menuItem->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ \Illuminate\Support\Str::limit($menuItem->description, 20) }}</small>
                                    </td>
                                    <td>{{ $menuItem->category->name ?? '-' }}</td>
                                    <td>
                                        @php
                                            $activeDiscount = $menuItem->activeDiscount();
                                        @endphp

                                        @if ($activeDiscount)
                                            <div class="text-muted small">
                                                <del>Rp {{ number_format($menuItem->price, 0, ',', '.') }}</del>
                                            </div>
                                            <div class="fw-bold text-success">
                                                Rp {{ number_format($menuItem->finalPrice(), 0, ',', '.') }}
                                            </div>
                                            <span class="badge bg-warning text-dark">
                                                {{ rtrim(rtrim(number_format((float) $activeDiscount->percentage, 2, ',', '.'), '0'), ',') }}% OFF
                                            </span>
                                            <div class="text-muted small">
                                                Hemat Rp {{ number_format($menuItem->discountAmount(), 0, ',', '.') }}
                                            </div>
                                        @else
                                            Rp {{ number_format($menuItem->price, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ $menuItem->estimated_minutes ? $menuItem->estimated_minutes . ' minutes' : '-' }}
                                    </td>
                                    <td class="position-relative">
                                        {{ $menuItem->stock }}
                                        @if($menuItem->stock <= 3)
                                            <span
                                                class="position-absolute bottom-0 end-0 text-danger me-1 mb-1"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="Stok Menipis!">
                                                <i class="fe fe-alert-triangle" style="font-size: 12px;"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($menuItem->is_available)
                                            <span class="badge bg-success">Available</span>
                                        @else
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($menuItem->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button
                                                class="btn btn-primary btn-sm dropdown-toggle table-action-dropdown"
                                                type="button"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false"
                                                style="height: 37px;"
                                            >
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('menu.show', $menuItem) }}">
                                                        View Detail
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('menu.edit', $menuItem) }}">
                                                        Edit All
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('menu.quick-edit', $menuItem) }}">
                                                        Quick Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form action="{{ route('menu.destroy', $menuItem) }}" method="POST" class="delete-form" data-type="Menu Item">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($menuItems->hasPages())
                            <div class="mt-3">
                                {{ $menuItems->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- modal import menu -->
<div class="modal fade" id="importMenuModal" tabindex="-1" aria-labelledby="importMenuModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('menu.import.store') }}" method="POST" enctype="multipart/form-data" id="importStoreForm">
                @csrf

                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="importMenuModalLabel">Import Menu from Excel</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"><i class="fa fa-close"></i></button>
                </div>

                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label mb-0">Upload Excel File</label>

                        <a href="{{ asset('templates/menu-import-template.xlsx') }}" style="font-size: 0.875rem; font-weight: bold;" class="btn btn-outline-primary btn-sm">
                            Download Template
                        </a>
                    </div>

                    <input type="file" id="excelFile" class="form-control mb-3" accept=".xlsx,.xls,.csv">

                    <div id="importAlert" class="alert alert-danger d-none"></div>

                    <div class="table-responsive d-none" id="previewTableWrapper">
                        <table class="table table-bordered text-nowrap mb-0">
                            <thead class="border-top">
                                <tr>
                                    <th>No</th>
                                    <th>Category</th>
                                    <th>Menu Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Estimate</th>
                                    <th>Stock</th>
                                    <th>Image</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody"></tbody>
                        </table>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary d-none" id="processDataButton">
                        Process Data
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- end modal import menu -->

<script>
    const excelFile = document.getElementById('excelFile');
    const previewTableWrapper = document.getElementById('previewTableWrapper');
    const previewTableBody = document.getElementById('previewTableBody');
    const importAlert = document.getElementById('importAlert');
    const processDataButton = document.getElementById('processDataButton');
    const importMenuModal = document.getElementById('importMenuModal');

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function limitText(value, limit = 20) {
        const text = String(value ?? '');

        if (text.length <= limit) {
            return text;
        }

        return text.substring(0, limit) + '...';
    }

    function resetImportPreview() {
        previewTableBody.innerHTML = '';
        previewTableWrapper.classList.add('d-none');
        processDataButton.classList.add('d-none');
        processDataButton.disabled = true;
        importAlert.classList.add('d-none');
        importAlert.innerHTML = '';
    }

    function bindImagePreview() {
        document.querySelectorAll('.import-image-input').forEach(function (input) {
            input.addEventListener('change', function () {
                const index = this.dataset.index;
                const file = this.files[0];
                const preview = document.getElementById('imagePreview' + index);
                const fileName = document.getElementById('imageFileName' + index);

                if (!file) {
                    preview.style.display = 'none';
                    preview.src = '';
                    fileName.innerHTML = '';
                    return;
                }

                preview.src = URL.createObjectURL(file);
                preview.style.display = 'inline-block';
                fileName.innerHTML = escapeHtml(file.name);
            });
        });
    }

    if (importMenuModal) {
        importMenuModal.addEventListener('hidden.bs.modal', function () {
            resetImportPreview();
            excelFile.value = '';
        });
    }

    excelFile.addEventListener('click', function () {
        this.value = '';
    });

    excelFile.addEventListener('change', function () {
        const file = this.files[0];

        resetImportPreview();

        if (!file) {
            return;
        }

        const formData = new FormData();
        formData.append('file', file);

        fetch('{{ route('menu.import.preview') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async response => {
            const text = await response.text();

            let data;
            try {
                data = JSON.parse(text);
            } catch (error) {
                throw new Error(text);
            }

            if (!response.ok) {
                let message = 'Failed to read Excel file.';

                if (data.message) {
                    message = data.message;
                }

                if (data.errors) {
                    message = Object.values(data.errors).flat().join('<br>');
                }

                throw new Error(message);
            }

            return data;
        })
        .then(data => {
            if (!data.rows || data.rows.length === 0) {
                importAlert.innerHTML = 'Excel file is empty.';
                importAlert.classList.remove('d-none');
                return;
            }

            data.rows.forEach((row, index) => {
                const hasError = row.errors.length > 0;
                const rowClass = hasError ? 'table-danger' : 'table-success';

                const descriptionFull = row.description ?? '';
                const descriptionShort = limitText(descriptionFull, 20);

                const status = hasError
                    ? `<span class="text-danger">${row.errors.join('<br>')}</span>`
                    : `<span class="badge bg-success">Valid</span>`;

                previewTableBody.innerHTML += `
                    <tr class="${rowClass}">
                        <td>${index + 1}</td>

                        <td>
                            ${escapeHtml(row.category)}
                            <input type="hidden" name="rows[${index}][category]" value="${escapeHtml(row.category)}">
                        </td>

                        <td>
                            ${escapeHtml(row.name)}
                            <input type="hidden" name="rows[${index}][name]" value="${escapeHtml(row.name)}">
                        </td>

                        <td title="${escapeHtml(descriptionFull)}">
                            ${escapeHtml(descriptionShort)}
                            <input type="hidden" name="rows[${index}][description]" value="${escapeHtml(descriptionFull)}">
                        </td>

                        <td>
                            ${escapeHtml(row.price)}
                            <input type="hidden" name="rows[${index}][price]" value="${escapeHtml(row.price)}">
                        </td>

                        <td>
                            ${escapeHtml(row.estimated_minutes)}
                            <input type="hidden" name="rows[${index}][estimated_minutes]" value="${escapeHtml(row.estimated_minutes)}">
                        </td>

                        <td>
                            ${escapeHtml(row.stock)}
                            <input type="hidden" name="rows[${index}][stock]" value="${escapeHtml(row.stock)}">
                        </td>

                        <td class="text-center">
                            <label class="btn btn-outline-primary btn-sm mb-0" for="imageInput${index}" title="Upload Image">
                                <i class="fe fe-upload"></i>
                            </label>

                            <input
                                type="file"
                                name="images[${index}]"
                                id="imageInput${index}"
                                data-index="${index}"
                                class="d-none import-image-input"
                                accept="image/png,image/jpg,image/jpeg,image/webp"
                            >

                            <div class="mt-2">
                                <img
                                    id="imagePreview${index}"
                                    src=""
                                    width="46"
                                    height="46"
                                    style="display: none; object-fit: cover; border-radius: 6px;"
                                >
                            </div>

                            <small id="imageFileName${index}" class="text-muted d-block mt-1"></small>
                        </td>

                        <td>${status}</td>
                    </tr>
                `;
            });

            previewTableWrapper.classList.remove('d-none');
            processDataButton.classList.remove('d-none');

            bindImagePreview();

            if (data.has_error) {
                importAlert.innerHTML = 'Some data is invalid. Please fix the Excel file and upload again.';
                importAlert.classList.remove('d-none');
                processDataButton.disabled = true;
            } else {
                processDataButton.disabled = false;
            }
        })
        .catch(error => {
            importAlert.innerHTML = error.message || 'Failed to read Excel file. Please check your file format.';
            importAlert.classList.remove('d-none');
            processDataButton.classList.add('d-none');
            processDataButton.disabled = true;
        });
    });
</script>
@endsection
