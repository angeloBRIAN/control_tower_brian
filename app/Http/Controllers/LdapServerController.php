<?php

namespace App\Http\Controllers;

use App\Models\LdapServer;
use App\Services\LdapService;
use Illuminate\Http\Request;

class LdapServerController extends Controller
{
    public function index()
    {
        $servers = LdapServer::all();
        return view('settings.ldap.index', compact('servers'));
    }

    public function create()
    {
        return view('settings.ldap.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer',
            'base_dn' => 'required|string',
            'bind_dn' => 'nullable|string',
            'bind_password' => 'nullable|string',
            'user_filter' => 'required|string',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->boolean('active');

        LdapServer::create($validated);

        return redirect()->route('admin.ldap.index')->with('success', 'LDAP Server added successfully.');
    }

    public function edit(LdapServer $ldapServer)
    {
        return view('settings.ldap.form', ['server' => $ldapServer]);
    }

    public function update(Request $request, LdapServer $ldapServer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer',
            'base_dn' => 'required|string',
            'bind_dn' => 'nullable|string',
            'bind_password' => 'nullable|string',
            'user_filter' => 'required|string',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->boolean('active');

        $ldapServer->update($validated);

        return redirect()->route('admin.ldap.index')->with('success', 'LDAP Server updated successfully.');
    }

    public function destroy(LdapServer $ldapServer)
    {
        $ldapServer->delete();
        return redirect()->route('admin.ldap.index')->with('success', 'LDAP Server deleted successfully.');
    }

    public function testConnection(LdapServer $ldapServer, LdapService $ldapService)
    {
        if ($ldapService->connect($ldapServer->host, $ldapServer->port)) {
            if ($ldapService->bind($ldapServer->bind_dn, $ldapServer->bind_password)) {
                return back()->with('success', 'Connection successful! Bind accepted.');
            }
            return back()->with('error', 'Connection/Bind failed: ' . $ldapService->getLastError());
        }
        return back()->with('error', 'Could not initialize LDAP connection.');
    }
}
