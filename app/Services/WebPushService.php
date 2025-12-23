<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class WebPushService
{
    private WebPush $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => config('services.webpush.subject'),
                'publicKey' => config('services.webpush.public_key'),
                'privateKey' => config('services.webpush.private_key'),
            ],
        ]);
    }

    /**
     * Send push notification to a user
     */
    public function sendToUser(User $user, string $title, string $body, ?string $url = null, ?string $icon = null): void
    {
        $subscriptions = $user->pushSubscriptions;

        if ($subscriptions->isEmpty()) {
            return;
        }

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'icon' => $icon ?? '/images/icon-192.png',
            'url' => $url ?? '/',
            'badge' => '/images/icon-192.png',
        ]);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub->endpoint,
                'publicKey' => $sub->public_key,
                'authToken' => $sub->auth_token,
                'contentEncoding' => $sub->content_encoding ?? 'aesgcm',
            ]);

            $this->webPush->queueNotification($subscription, $payload);
        }

        // Flush and handle results
        foreach ($this->webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                // Subscription expired or invalid, remove it
                $endpoint = $report->getEndpoint();
                PushSubscription::where('endpoint', $endpoint)->delete();
            }
        }
    }

    /**
     * Send push to multiple users
     */
    public function sendToUsers($users, string $title, string $body, ?string $url = null): void
    {
        foreach ($users as $user) {
            $this->sendToUser($user, $title, $body, $url);
        }
    }

    /**
     * Send push to users with specific role
     */
    public function sendToRole(string $role, string $title, string $body, ?string $url = null): void
    {
        $users = User::where('role', $role)->get();
        $this->sendToUsers($users, $title, $body, $url);
    }
}
