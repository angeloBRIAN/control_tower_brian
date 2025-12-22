<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SmtpSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'port' => 'integer',
        ];
    }

    /**
     * Encrypt password when setting.
     */
    public function setPasswordAttribute($value): void
    {
        if ($value) {
            $this->attributes['password'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt password when getting.
     */
    public function getDecryptedPasswordAttribute(): ?string
    {
        if ($this->password) {
            try {
                return Crypt::decryptString($this->password);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Get the active SMTP configuration.
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Apply this SMTP config to the mail configuration.
     */
    public function applyToMailer(): void
    {
        config([
            'mail.mailers.smtp.host' => $this->host,
            'mail.mailers.smtp.port' => $this->port,
            'mail.mailers.smtp.username' => $this->username,
            'mail.mailers.smtp.password' => $this->decrypted_password,
            'mail.mailers.smtp.encryption' => $this->encryption,
            'mail.from.address' => $this->from_address,
            'mail.from.name' => $this->from_name,
        ]);
    }
}
