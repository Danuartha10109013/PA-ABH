<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProcessLogin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = "Login";
        return view("page.auth.signin", ["title"=> $title]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProcessLogin $request)
    {
        $exists = User::where("email", $request->email)->first();
        if (empty($exists)) {
            notyf()->error("Email tidak ditemukan!");
            return redirect()->route("login");
        }
        if (!Hash::check($request->password, $exists->password)) {
            notyf()->error("Password salah!");
            return redirect()->route("login");
        }
        if (Auth::attempt($request->only("email", "password"))) {
            notyf()->success("Login berhasil!");
            return redirect()->route("index");
        } else {
            notyf()->error("Login gagal!");
        }

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function logout()
    {
        Auth::logout();
        notyf()->success("Logout berhasil!");
        return redirect()->route("login");
    }
}
