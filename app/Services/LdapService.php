<?php

namespace App\Services;

use App\Models\LdapServer;

class LdapService
{
    protected $connection;
    protected $server;

    /**
     * Connect to the active LDAP server
     */
    public function connectToActive()
    {
        $this->server = LdapServer::where('active', true)->first();
        
        if (!$this->server) {
            throw new \Exception('No active LDAP server configured');
        }

        return $this->connect($this->server->host, $this->server->port);
    }

    /**
     * Connect to LDAP server
     */
    public function connect($host, $port)
    {
        $this->connection = ldap_connect($host, $port);
        if ($this->connection) {
            ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);
            return true;
        }
        return false;
    }

    /**
     * Bind to LDAP server
     */
    public function bind($dn = null, $password = null)
    {
        if (!$this->connection) {
            return false;
        }
        
        // Suppress warnings for bind failures
        try {
            return @ldap_bind($this->connection, $dn, $password);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Search LDAP directory
     */
    public function search($baseDn, $filter, $attributes = [])
    {
        if (!$this->connection) {
            return false;
        }

        $search = @ldap_search($this->connection, $baseDn, $filter, $attributes);
        if ($search) {
            return ldap_get_entries($this->connection, $search);
        }
        return false;
    }

    /**
     * Search users by name or username
     */
    public function searchUsers($searchTerm)
    {
        // Connect to active LDAP server
        $this->connectToActive();
        
        // Bind with service account
        if ($this->server->bind_dn && $this->server->bind_password) {
            if (!$this->bind($this->server->bind_dn, $this->server->bind_password)) {
                throw new \Exception('Failed to bind to LDAP server: ' . $this->getLastError());
            }
        } else {
            // Anonymous bind
            if (!$this->bind()) {
                throw new \Exception('Failed to bind to LDAP server');
            }
        }

        // Build search filter - search by cn, sAMAccountName, or uid
        $escapedTerm = ldap_escape($searchTerm, '', LDAP_ESCAPE_FILTER);
        $filter = "(|(cn=*{$escapedTerm}*)(sAMAccountName=*{$escapedTerm}*)(uid=*{$escapedTerm}*)(mail=*{$escapedTerm}*))";
        
        // Use custom filter if provided
        if ($this->server->user_filter) {
            $filter = str_replace('%s', $escapedTerm, $this->server->user_filter);
        }

        // Search attributes
        $attributes = ['cn', 'sAMAccountName', 'uid', 'mail', 'displayName', 'givenName', 'sn'];

        $results = $this->search($this->server->base_dn, $filter, $attributes);
        
        if (!$results || $results['count'] == 0) {
            return [];
        }

        $users = [];
        for ($i = 0; $i < min($results['count'], 10); $i++) {  // Limit to 10 results
            $entry = $results[$i];
            $users[] = [
                'username' => $entry['samaccountname'][0] ?? $entry['uid'][0] ?? $entry['cn'][0] ?? '',
                'name' => $entry['displayname'][0] ?? $entry['cn'][0] ?? '',
                'email' => $entry['mail'][0] ?? '',
            ];
        }

        return $users;
    }

    /**
     * Get Last Error
     */
    public function getLastError()
    {
        if ($this->connection) {
            return ldap_error($this->connection);
        }
        return "No connection";
    }
}
