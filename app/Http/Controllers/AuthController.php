<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $ldapServers = \App\Models\LdapServer::where('active', true)->get();
        return view('auth.login', compact('ldapServers'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
            'login_source' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $loginSource = $request->input('login_source');

        if ($loginSource === 'local') {
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();
                return redirect()->intended(route('dashboard'));
            }
        } else {
            // LDAP Login
            $server = \App\Models\LdapServer::find($loginSource);
            if ($server && $server->active) {
                $ldapService = new \App\Services\LdapService();
                
                if ($ldapService->connect($server->host, $server->port)) {
                    // 1. Bind with service account (if configured) or anonymous to search for user DN
                    if ($server->bind_dn) {
                        $bind = $ldapService->bind($server->bind_dn, $server->bind_password);
                    } else {
                        $bind = $ldapService->bind(); // Anonymous
                    }

                    if ($bind) {
                        // 2. Search for user DN
                        // Replace %s in filter with username/email
                        $username = $request->email;
                        // If email is provided, you might want to extract username part or use entire email depending on LDAP config
                        // For this example, assuming input is username or email matches filter
                        
                        $filter = sprintf($server->user_filter, $username);
                        $results = $ldapService->search($server->base_dn, $filter);

                        // If no results and input looks like email, try extracting username
                        if ((!$results || $results['count'] === 0) && filter_var($username, FILTER_VALIDATE_EMAIL)) {
                             $extractedUser = explode('@', $username)[0];
                             $filter = sprintf($server->user_filter, $extractedUser);
                             $results = $ldapService->search($server->base_dn, $filter);
                        }

                        if ($results && $results['count'] > 0) {
                            $entry = $results[0];
                            $userDn = $entry['dn'];

                            // 3. Bind with found User DN and Password
                            if ($ldapService->bind($userDn, $request->password)) {
                                // Auth Successful
                                
                                // Sync/Create Local User
                                // Extract attributes - highly dependent on LDAP schema (AD vs OpenLDAP vs Zimbra)
                                // Trying common attributes
                                $cn = $entry['cn'][0] ?? $username;
                                $mail = $entry['mail'][0] ?? $username . '@example.com'; // Fallback if no mail

                                $user = User::updateOrCreate(
                                    ['email' => $mail],
                                    [
                                        'name' => $cn,
                                        'password' => Hash::make(\Illuminate\Support\Str::random(32)), // Random pass for local db
                                        'email_verified_at' => now(),
                                    ]
                                );

                                Auth::login($user, $request->boolean('remember'));
                                $request->session()->regenerate();
                                return redirect()->intended(route('dashboard'));
                            }
                        }
                    }
                }
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Account created successfully!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
