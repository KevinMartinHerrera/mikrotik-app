<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }
    public function showDashboard()
    {
        if (session('authenticated')) {
            $groups = session('groups', []); 
            $interfaces = session('interfaces', []); 
            $users = session('users', []);
            $ips = session('ips', []); // Agregar las direcciones IP
    
            return view('dashboard', compact('groups', 'interfaces', 'users', 'ips'));
        } else {
            return redirect()->route('show.login');
        }
    }
    public function logout()
    {
        // Limpiar la sesión
        session()->flush();

        // Redirigir al usuario al login
        return redirect()->route('show.login')->with('message', 'Sesión cerrada correctamente');
    }
    
}
