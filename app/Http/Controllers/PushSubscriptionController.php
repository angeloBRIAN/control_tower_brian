<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * Store a push subscription
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $user = auth()->user();
        $endpointHash = hash('sha256', $validated['endpoint']);

        // Upsert subscription (using hash for uniqueness since endpoint is TEXT)
        PushSubscription::updateOrCreate(
            ['endpoint_hash' => $endpointHash],
            [
                'user_id' => $user->id,
                'endpoint' => $validated['endpoint'],
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'content_encoding' => 'aesgcm',
            ]
        );

        return response()->json(['success' => true, 'message' => 'Push subscription saved']);
    }

    /**
     * Remove a push subscription
     */
    public function destroy(Request $request)
    {
        $endpoint = $request->input('endpoint');

        if ($endpoint) {
            $endpointHash = hash('sha256', $endpoint);
            PushSubscription::where('endpoint_hash', $endpointHash)->delete();
        }

        return response()->json(['success' => true, 'message' => 'Push subscription removed']);
    }

    /**
     * Get VAPID public key for client
     */
    public function vapidPublicKey()
    {
        return response()->json([
            'publicKey' => config('services.webpush.public_key'),
        ]);
    }
}
