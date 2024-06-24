<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    public function index() {
        return view('auth.login');
    }

    public function login_proses(Request $request){
        $request->validate([
            'nama' => 'required',
            'password' => 'required',
        ]);
        $user = [
            'nama' => $request->nama,
            'password' => $request->password,
        ];

        if (Auth::attempt($user)){
            // return ("berhasil");
            return redirect()->route('admin.dashboard');
        }else{
            // return ("gagal");
            return redirect()->route('login')->with('failed','Nama atau Password salah');
        }
        // $credentials = $request->validate([

        //     'nama' => ['required'],
        //     'password' => ['required'],
        // ]);

        // if (Auth::attempt($credentials)) {
        //     $request->session()->regenerate();

        //     return redirect()->intended('admin/dashboard');
        // }

        // return back()->withErrors([
        //     'email' => 'The provided credentials do not match our records.',
        // ]);

    }

    public function logout(){
        Auth::logout();
        return redirect()->route('login')->with('success','Kamu berhasil logout');
    }
}
