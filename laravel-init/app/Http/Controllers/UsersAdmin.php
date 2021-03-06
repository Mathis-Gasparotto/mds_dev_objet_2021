<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Requests\StoreUserFormRequest;
use App\Http\Requests\UpdateUserFormRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class UsersAdmin extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {

        return view("users.index", ['users' => User::all()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        return view("users.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUserFormRequest  $request
     */
    public function store(StoreUserFormRequest $request)
    {
        $input = $request->safe()->only(['name', 'email', 'password', 'avatar_url']);
        // $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        return redirect()->route('users.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Users  $users
     */
    public function show(User $user)
    {
        return view('users.show', ['user' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Users  $users
     */
    public function edit(User $user)
    {
        echo view('users.show', ['user' => $user]);
        return view('users.edit', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserFormRequest  $request
     * @param  \App\Models\User  $user
     */
    public function update(UpdateUserFormRequest $request, User $user)
    {
        $input = $request->safe()->only(['name', 'email', 'avatar_url']);
        $user->update($input);
        return redirect()->route('users.show', $user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     */
    public function destroy(User $user)
    {
        if (Auth::user() == $user) {
            return redirect(route('users.show' , $user))->with('error-perm', 'Since you are an admin, you cannot delete your account yourself.');
        }
        $user->delete();
        return redirect(route('users.index'));
    }

    public function changeRole(User $user)
    {
        if (Auth::user() == $user) {
            return redirect(route('user.dashboard'))->with('error-perm', "You cannot change your own role");
        }
        return view('users.edit_role', ['user' => $user]);

    }

    public function updateRole(Request $request, User $user)
    {
        if (Auth::user() == $user) {
            return redirect(route('user.dashboard'))->with('error-perm', "You cannot change your own role");
        }

        $attributes = $request->validate([
            'role' => [
                'required',
                new Enum(RoleEnum::class),
            ],
        ]);
        $user->update($attributes);
        return redirect(route('users.show', $user))->with('status', 'The role has been successfully updated');
    }

    public function loginAs(User $user)
    {
        if (Auth::user()->role != RoleEnum::Admin->value) {
            return redirect(route('users.index'))->with('error-perm', 'You do not have access to this part of the site.');
        }
        if (Auth::user() == $user) {
            return redirect(route('users.index'))->with('error-perm', 'You cannot login as you.');
        }
        Auth::login($user);
        return redirect(route('users.index'))->with('status', 'Login success!');
    }
}
