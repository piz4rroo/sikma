<?php

namespace App\Http\Controllers\Auth;

// Pastikan Anda mengimport Controller dasar
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Lokasi redirect setelah login berhasil.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Buat instance controller baru.
     *
     * @return void
     */
    public function __construct()
    {
        // Terapkan middleware di constructor, bukan di method lain
        $this->middleware('guest')->except('logout');
    }
    
    // Metode lain...
}