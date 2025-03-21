<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Category;

class MenuController extends Controller
{
    /**
     * Display a listing of the available menus for customers.
     */
    public function index(Request $request)
    {
        $query = Menu::where('is_available', true)->where('is_archived', false);

        // Filter berdasarkan nama menu jika ada input pencarian
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        // Filter berdasarkan kategori jika ada input kategori
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        // Ambil daftar menu yang tersedia dengan paginasi 12 item per halaman
        $menus = $query->paginate(12);

        // Ambil semua kategori untuk dropdown filter
        $categories = Category::all();

        // Tampilkan view customer.menus.index dengan data yang sudah diproses
        return view('customer.menus.index', compact('menus', 'categories'));
    }
}
