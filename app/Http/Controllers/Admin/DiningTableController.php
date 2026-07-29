<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DiningTable;

class DiningTableController extends Controller
{
    // 1. Tampil Data
    public function index()
    {       
        $tables = DiningTable::orderBy('table_number', 'asc')->get(); 
        
        return view('Admin.diningtable.index', compact('tables'));
    }

    // 2. Simpan Data Baru
    public function store(Request $request)
    {
        $request->validate([
            'table_number' => 'required|string|max:255|unique:dining_tables,table_number',
        ], [
            'table_number.unique' => 'Nomor atau nama meja ini sudah terdaftar!',
        ]);

        DiningTable::create([
            'table_number' => $request->table_number,
            'qr_token' => bin2hex(random_bytes(16)),
        ]);

        return redirect()->back()->with('success', 'Table created successfully.');
    }

    // 4. Hapus Data
    public function destroy($id)
    {
        $table = DiningTable::findOrFail($id);
        $table->delete();

        return redirect()->back()->with('success', 'Table deleted successfully!');
    }
}