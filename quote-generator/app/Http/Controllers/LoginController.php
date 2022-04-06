<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterFormRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function auth()
    {
        $githubUser = Socialite::driver('github')->user();
        $user = User::where('github_id', $githubUser->id)->first();

        if ($user) {
            Auth::login($user);
            return redirect(route('index'))->with('success', "You have been logged in");
        }

        return view('auth.register', ['github_id' => $githubUser->id, 'email' => $githubUser->email, 'name' => $githubUser->name]);

    }

    public function redirect()
    {
        return Socialite::driver('github')->redirect();
    }

    public function registration(RegisterFormRequest $request)
    {
        $input = $request->safe()->only([
            'github_id',
            'name',
            'email',
            'contact_email',
            'phone',
            'address',
            'bank_account_owner',
            'bank_domiciliation',
            'bank_rib',
            'bank_iban',
            'bank_bic',
            'company_name',
            'company_siret',
            'company_ape',
        ]);
        $user = User::create($input);
        Auth::login($user);
        return redirect()->route('index')->with('success', "You have been registered and logged in");
    }

    public function logout()
    {
        Auth::logout();
        return redirect(route('auth.login'))->with('success', "You have been logout");
    }
}
