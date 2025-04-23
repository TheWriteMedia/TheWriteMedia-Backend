<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\User;
use App\Models\Notification as NotificationModel;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected static function getMessaging()
    {
        static $messaging = null;

        if (!$messaging) {
            $serviceAccount = [
                "type"=> "service_account",
                "project_id"=> "thewritemedia-6ee4f",
                "private_key_id"=> "ace22067a9cd92726bd51fffab8eb5e7a8a6fcf8",
                "private_key"=> "-----BEGIN PRIVATE KEY-----\nMIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQCyPXdMTNxeZTWW\nOqe1pm7+zF81oITTPmpUE0BUfiwBKtH5063ci91/Z/vJ9elE9Iw42KKIcXujdzFg\nfRUfMPoMOQGNLyrG1xLZN7p8SXn2azl244l17FoTL7SRPVsIXIq7K3P74HDPJKfz\n2CY96+MwqZsyKXvIJL9Lr4Z1Hngq6nx9qfZrUPyN2/vPOFbDHAIZMH2QE9SO+JBB\nWETgOkmbIAVxY8D2MvW1HlVlAZUh8ZgO9K3mwZPzqP2fX/6IoSfIDqhVS+50BrQE\nYM0owrIGAi1fQxGSkwaU9980pcjEIlo9dmEYizrgYT+VRIRa7cfqiCIVZWP/nMeS\ni7CA22ZxAgMBAAECggEAD5A7av+pHFr1B2WewEdEmN4KkDmwb2ubSDUYIGiV+hId\nnJVlsITL9fSD8VoWIM3DsrED3CsgkyD0wEqbvgn+Q3P01TmhoBQmkq4BjBm0WxZ4\nwY9TsObO5K79MtNrL/s4p+g+6+boosW+HpYonK6Mu3KL2SDzLII86+RrNq3O61xB\n0/+s1PrF7wvPrUOulr2Fr+coQ3MKfrvbzKjokCdp6Jzx60q2/fPlsrP54+sfxtMj\njHGoTEAABfZBopRCW1em+ID/LmsEySk+zQgCQpCbsG+Nx/RInZZkt6ikOsCOGGvW\n078XbauOsDfr93gP0nnUvxQz1jr/6z/KXFel3zmkQwKBgQDl2psV3v0XQdXZ4x5h\n70vWjpd8tvngd5v9IcwAeoqIcingNQHHGjuG4CSY/2b2t39/NR0deNZMtlzw70EQ\nRZvyrOs5zMd1fTTs9ptDmVhYHrWco9RybOSRdj9pSG49B4wVL95sCTxHkAoUuG/v\n46RpMpnAHTDxB+Pg6cAJN+mMuwKBgQDGg9rVsV06Gf+dDcsu8Un9FjPNwU+MoK8z\nNYa1wTHGqUrZ3jVFuTY8L8f8VDN+iz2gtaRDjiNGb5b/99CDDFSfJR2syWuF86oI\nFk1i+O4ywgBCSaqWNLCdEQ9jGAAgCVs62XrqfY9mBrNcAnllRKORz4gTcGUGfq40\nSs4xnQZcwwKBgQCanPX9PEYawtpK+o+RaRomdTxJU/u5ljuDhpxnJgbRyZmJi55q\ng6bPDy8PC54DD2Pf+aZgEMx2RaU3HHkzrN892jtQYgM5cMONMkU2r4vRmranazyY\ny87kWhpFg8dDnjQIfG6tVXkK2kUtsvL49rO9X6A0rm4hdCFBRXs7uDSNyQKBgQDE\nXUtLVD2uqmbxuOJCVgVlJuApaE3DAnMriYfuI+OT6oQTDL3PcUzXrj/5NmUViLB2\nVxbciC4Wj21jV4PXDYxrO/ClqTwF7ahEhaMZArnEVaih6XKnUAlf5K8+y0/cYMTw\nao4ug/Mj7f7fRcdFR+cw5YNqDySvjwHM0i4yX2watwKBgQCq1TES6H03r+k1oc6J\nL8qCzcHjAJ9RDjmkp3ODQ6Fpl69AaPm6S/BtY8KRQDU3F1jGOxsrY3IXZL9mg2Ii\ndH84nIAhCbShQ9IbmCZtmILRpuMKM6lIr9cvy1kWCykPbzP5MDygYdwwEYTbZX++\n6dBfG0R4orMdDUL6PESn9HdC/A==\n-----END PRIVATE KEY-----\n",
                "client_email"=> "firebase-adminsdk-fbsvc@thewritemedia-6ee4f.iam.gserviceaccount.com",
                "client_id"=> "117031082059543670124",
                "auth_uri"=> "https://accounts.google.com/o/oauth2/auth",
                "token_uri"=> "https://oauth2.googleapis.com/token",
                "auth_provider_x509_cert_url"=> "https://www.googleapis.com/oauth2/v1/certs",
                "client_x509_cert_url"=> "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-fbsvc%40thewritemedia-6ee4f.iam.gserviceaccount.com",
                "universe_domain"=> "googleapis.com"
            ];

            $factory = (new Factory)
                ->withServiceAccount($serviceAccount)
                ->withDatabaseUri(config('firebase.database_url'));

            $messaging = $factory->createMessaging();
        }

        return $messaging;
    }

    // Update NotificationService (app/Services/NotificationService.php):
// app/Services/NotificationService.php
public static function sendNotification($userId, $title, $message, $type = null, $referenceId = null)
{
    $user = User::find($userId);

    if (!$user || empty($user->fcm_tokens)) {
        return false;
    }

    // Save notification in DB
    NotificationModel::create([
        'user_id' => $userId,
        'title' => $title,
        'message' => $message,
        'type' => $type,
        'reference_id' => $referenceId,
        'is_read' => false,
    ]);

    try {
        $notification = Notification::create($title, $message);
        
        foreach ($user->fcm_tokens as $token) {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification);
            
            self::getMessaging()->send($message);
        }
        
        return true;
    } catch (\Exception $e) {
        Log::error('FCM Error: ' . $e->getMessage());
        return false;
    }
}
}