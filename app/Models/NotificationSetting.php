<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_type',
        'email_enabled',
        'sms_enabled',
        'push_enabled',
        'settings',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'push_enabled' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get the user that owns the notification setting.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a specific notification type is enabled for a user.
     */
    public static function isEnabledForUser($userId, $notificationType, $channel = 'email')
    {
        $setting = static::where('user_id', $userId)
            ->where('notification_type', $notificationType)
            ->first();

        if (!$setting) {
            // Default to enabled if no setting exists
            return true;
        }

        switch ($channel) {
            case 'email':
                return $setting->email_enabled;
            case 'sms':
                return $setting->sms_enabled;
            case 'push':
                return $setting->push_enabled;
            default:
                return false;
        }
    }

    /**
     * Get default notification settings for a user.
     */
    public static function getDefaultSettings()
    {
        return [
            'appointment_created' => ['email' => true, 'sms' => false, 'push' => true],
            'appointment_updated' => ['email' => true, 'sms' => false, 'push' => true],
            'appointment_cancelled' => ['email' => true, 'sms' => false, 'push' => true],
            'appointment_reminder' => ['email' => true, 'sms' => false, 'push' => true],
            'payment_received' => ['email' => true, 'sms' => false, 'push' => true],
            'payment_overdue' => ['email' => true, 'sms' => false, 'push' => true],
            'payment_reminder' => ['email' => true, 'sms' => false, 'push' => true],
        ];
    }
}
