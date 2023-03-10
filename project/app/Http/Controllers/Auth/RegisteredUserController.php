<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\HasEnsure;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    use HasEnsure;

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $password = $this->ensureIsString($request->password);

        $userId = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
        ])->id;

        $user = User::find($userId);

        UserProfile::create([
            'name' => $request->name,
            'user_id' => $userId,
        ]);
        if ($user instanceof \Illuminate\Contracts\Auth\Authenticatable) {
            event(new Registered($user));

            Auth::login($user);
        }

        return redirect('/profile');
    }
}
