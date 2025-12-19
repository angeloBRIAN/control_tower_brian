<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\Auditable; // Add this line

class LdapServer extends Model
{
    use HasFactory, Auditable; // Add this trait

    protected $fillable = [
        'name',
        'host',
        'port',
        'base_dn',
        'bind_dn',
        'bind_password',
        'user_filter',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'port' => 'integer',
    ];
}
