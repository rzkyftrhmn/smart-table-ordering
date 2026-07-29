<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Imports\MenuItemsPreviewImport;
use Maatwebsite\Excel\Facades\Excel;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $menuItems = MenuItem::with(['category', 'menuDiscounts.discount'])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('Admin.menu.index', compact('menuItems', 'search'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $menuItem = new MenuItem([
            'stock' => 1,
            'is_active' => true,
        ]);

        return view('Admin.menu.form', compact('categories', 'menuItem'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')->store('menu-items', 'public');
        }

        $validated['stock'] = 1;
        $validated['is_available'] = true;
        $validated['is_active'] = true;

        MenuItem::create($validated);

        return redirect()
            ->route('menu.index')
            ->with('success', 'Menu item created successfully.');
    }

    // Edit and Update Methods
    public function edit(MenuItem $menuItem)
    {
        $categories = Category::orderBy('name')->get();

        return view('Admin.menu.form', compact('categories', 'menuItem'));
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($request->hasFile('image')) {
            if ($menuItem->image_url) {
                Storage::disk('public')->delete($menuItem->image_url);
            }

            $validated['image_url'] = $request->file('image')->store('menu-items', 'public');
        }

        $validated['is_available'] = $menuItem->stock > 0;

        $menuItem->update($validated);

        return redirect()
            ->route('menu.index')
            ->with('success', 'Menu item updated successfully.');
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return redirect()
            ->route('menu.index')
            ->with('success', 'Menu item deleted successfully.');
    }

    // Quick Edit Methods
    public function editQuick(MenuItem $menuItem)
    {
        $menuItem->load(['category', 'menuDiscounts.discount']);

        return view('Admin.menu.quick-form', compact('menuItem'));
    }

    public function updateQuick(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $validated['is_available'] = $validated['stock'] > 0;
        $validated['is_active'] = $request->boolean('is_active');

        $menuItem->update($validated);

        return redirect()
            ->route('menu.index')
            ->with('success', 'Menu item updated successfully.');
    }

    public function show(MenuItem $menuItem)
    {
        $menuItem->load('category');

        return view('Admin.menu.show', compact('menuItem'));
    }

    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
        ]);

        $rows = Excel::toCollection(new MenuItemsPreviewImport, $request->file('file'))->first() ?? collect();

        $previewRows = [];
        $hasError = false;

        foreach ($rows as $index => $row) {
            $errors = [];

            $categoryName = trim($row['category'] ?? '');
            $category = Category::where('name', $categoryName)->first();

            $price = $this->cleanPrice($row['price'] ?? null);
            $estimatedMinutes = $row['estimated_minutes'] ?? null;
            $stock = $row['stock'] ?? 1;

            if (! $category) {
                $errors[] = 'Category is not registered.';
            }

            if (empty($row['name'])) {
                $errors[] = 'Menu name is required.';
            }

            if ($price === null || $price < 0) {
                $errors[] = 'Price cannot be negative or empty.';
            }

            if ($estimatedMinutes !== null && $estimatedMinutes !== '' && (int) $estimatedMinutes < 1) {
                $errors[] = 'Estimated minutes must be at least 1.';
            }

            if ($stock !== null && $stock !== '' && (int) $stock < 0) {
                $errors[] = 'Stock cannot be negative.';
            }

            if (! empty($errors)) {
                $hasError = true;
            }

            $previewRows[] = [
                'category' => $categoryName,
                'name' => $row['name'] ?? '',
                'description' => $row['description'] ?? '',
                'price' => $row['price'] ?? '',
                'estimated_minutes' => $estimatedMinutes,
                'stock' => $stock,
                'errors' => $errors,
            ];
        }

        return response()->json([
            'has_error' => $hasError,
            'rows' => $previewRows,
        ]);
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'rows' => ['required', 'array'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        foreach ($request->rows as $index => $row) {
            $category = Category::where('name', trim($row['category']))->first();

            if (! $category) {
                return back()->withErrors([
                    'import' => 'Category "' . $row['category'] . '" is not registered. Please upload again.',
                ]);
            }

            $stock = isset($row['stock']) && $row['stock'] !== ''
                ? (int) $row['stock']
                : 1;

            $imagePath = null;

            if ($request->hasFile("images.$index")) {
                $imagePath = $request->file("images.$index")->store('menu-items', 'public');
            }

            MenuItem::create([
                'category_id' => $category->id,
                'name' => trim($row['name']),
                'description' => $row['description'] ?? null,
                'price' => $this->cleanPrice($row['price']),
                'image_url' => $imagePath,
                'estimated_minutes' => $row['estimated_minutes'] ?? null,
                'stock' => $stock,
                'is_available' => $stock > 0,
                'is_active' => true,
            ]);
        }

        return redirect()
            ->route('menu.index')
            ->with('success', 'Menu imported successfully.');
    }

    private function cleanPrice($price): ?float
    {
        if ($price === null || $price === '') {
            return null;
        }

        if (is_numeric($price)) {
            return (float) $price;
        }

        return (float) str_replace(['.', ','], ['', '.'], $price);
    }

    public function storeDiscount(Request $request, MenuItem $menuItem)
    {
    $validated = $request->validateWithBag('discount', [
        'discount_name' => ['required', 'string', 'max:100'],
        'percentage'    => ['required', 'numeric', 'min:1', 'max:100'],
        'start_date'    => ['required', 'date', 'after_or_equal:now'],
        'end_date'      => ['required', 'date', 'after:start_date'],
    ]);
    
    // dd($validated);

    $alreadyHasDiscount = $menuItem->menuDiscounts()
        ->whereHas('discount', fn($q) => $q
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
        )->exists();

    if ($alreadyHasDiscount) {
        return redirect()
            ->route('menu.quick-edit', $menuItem)
            ->withErrors(['Menu ini sudah memiliki diskon aktif.'], 'discount')
            ->withInput();
    }

    $discount = \App\Models\Discount::create([
        'name'       => $validated['discount_name'],
        'percentage' => $validated['percentage'],
        'start_date' => $validated['start_date'],
        'end_date'   => $validated['end_date'],
        'created_by' => \App\Models\User::where('username', 'admin')->first()->id,
    ]);

    $menuItem->menuDiscounts()->create([
        'discount_id' => $discount->id,
    ]);

    return redirect()
        ->route('menu.quick-edit', $menuItem)
        ->with('success', 'Diskon berhasil ditambahkan.');
    }

    public function destroyDiscount(MenuItem $menuItem, \App\Models\Discount $discount)
    {
        $menuItem->menuDiscounts()->where('discount_id', $discount->id)->delete();
        $discount->delete();

        return redirect()
            ->route('menu.quick-edit', $menuItem)
            ->with('success', 'Diskon berhasil dihapus.');
    }
}
