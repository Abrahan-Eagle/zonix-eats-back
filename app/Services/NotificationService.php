<?php

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\Notification;
use App\Models\Profile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Create a notification and dispatch events/push
     *
     * @param  int  $profileId
     * @param  string  $title
     * @param  string  $body
     * @param  string  $type
     * @param  array  $data
     * @return \App\Models\Notification|null
     */
    public function notify($profileId, $title, $body, $type = 'system', $data = [])
    {
        try {
            $profile = Profile::find($profileId);
            if (! $profile) {
                Log::warning("Notification failed: Profile $profileId not found");

                return null;
            }

            // 1. Save to Database
            $notification = Notification::create([
                'profile_id' => $profileId,
                'title' => $title,
                'body' => $body,
                'type' => $type,
                'data' => $data,
            ]);

            // 2. Broadcast via Pusher
            // The NotificationCreated event should handle the broadcastOn() and broadcastWith()
            Log::debug('NotificationService: Dispatching NotificationCreated', [
                'id' => $notification->id,
                'profile_id' => $profileId,
                'type' => $type,
            ]);
            event(new NotificationCreated($notification));
            Cache::increment('metrics:realtime:notification_broadcast_emitted_total');

            // 3. Send Push Notification (Firebase) if configured and enabled
            $pushSent = $this->sendPushIfEnabled($profile, $title, $body, $type, $data);
            if ($pushSent) {
                Cache::increment('metrics:realtime:fcm_sent_total');
            }

            return $notification;
        } catch (\Exception $e) {
            Cache::increment('metrics:realtime:notification_emit_failed_total');
            Log::error('Error in NotificationService: '.$e->getMessage(), [
                'exception' => $e,
                'profile_id' => $profileId,
                'title' => $title,
            ]);

            return null;
        }
    }

    /**
     * Send push notification based on user preferences
     *
     * @param  string  $title
     * @param  string  $body
     * @param  string  $type
     * @param  array  $data
     * @return bool
     */
    protected function sendPushIfEnabled(Profile $profile, $title, $body, $type, $data)
    {
        if (! $profile->fcm_device_token) {
            Cache::increment('metrics:realtime:fcm_skipped_no_token_total');

            return false;
        }

        $preferences = $profile->notification_preferences ?? [];

        // Check global push Master Switch
        if (isset($preferences['push_notifications']) && ! $preferences['push_notifications']) {
            Log::info("Push notifications disabled globally for profile {$profile->id}");
            Cache::increment('metrics:realtime:fcm_skipped_preferences_total');

            return false;
        }

        // Check specific type switch (e.g., order_notifications). commerce_order usa la misma preferencia que order.
        $preferenceType = ($type === 'commerce_order') ? 'order' : $type;
        $typeKey = $preferenceType.'_notifications';
        if (isset($preferences[$typeKey]) && ! $preferences[$typeKey]) {
            Log::info("Push notifications for type '{$type}' disabled for profile {$profile->id}");
            Cache::increment('metrics:realtime:fcm_skipped_preferences_total');

            return false;
        }

        // Send via Firebase
        $result = $this->firebaseService->sendToDevice(
            $profile->fcm_device_token,
            $title,
            $body,
            array_merge($data, ['type' => $type])
        );
        if (! $result) {
            Cache::increment('metrics:realtime:fcm_failed_total');
            Log::warning('FCM send failed', [
                'profile_id' => $profile->id,
                'type' => $type,
            ]);
        }

        return $result;
    }
}
