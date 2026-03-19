<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Profile;
use App\Events\NotificationCreated;
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
     * @param int $profileId
     * @param string $title
     * @param string $body
     * @param string $type
     * @param array $data
     * @return \App\Models\Notification|null
     */
    public function notify($profileId, $title, $body, $type = 'system', $data = [])
    {
        try {
            $profile = Profile::find($profileId);
            if (!$profile) {
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


            // 3. Send Push Notification (Firebase) if configured and enabled
            $this->sendPushIfEnabled($profile, $title, $body, $type, $data);

            return $notification;
        } catch (\Exception $e) {
            Log::error("Error in NotificationService: " . $e->getMessage(), [
                'exception' => $e,
                'profile_id' => $profileId,
                'title' => $title
            ]);
            return null;
        }
    }

    /**
     * Send push notification based on user preferences
     * 
     * @param Profile $profile
     * @param string $title
     * @param string $body
     * @param string $type
     * @param array $data
     * @return bool
     */
    protected function sendPushIfEnabled(Profile $profile, $title, $body, $type, $data)
    {
        if (!$profile->fcm_device_token) {
            return false;
        }

        $preferences = $profile->notification_preferences ?? [];
        
        // Check global push Master Switch
        if (isset($preferences['push_notifications']) && !$preferences['push_notifications']) {
            Log::info("Push notifications disabled globally for profile {$profile->id}");
            return false;
        }

        // Check specific type switch (e.g., order_notifications). commerce_order usa la misma preferencia que order.
        $preferenceType = ($type === 'commerce_order') ? 'order' : $type;
        $typeKey = $preferenceType . '_notifications';
        if (isset($preferences[$typeKey]) && !$preferences[$typeKey]) {
            Log::info("Push notifications for type '{$type}' disabled for profile {$profile->id}");
            return false;
        }

        // Send via Firebase
        return $this->firebaseService->sendToDevice(
            $profile->fcm_device_token,
            $title,
            $body,
            array_merge($data, ['type' => $type])
        );
    }
}
