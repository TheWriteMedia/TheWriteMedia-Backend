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
                "project_id"=> "thewritemedianotifs",
                "private_key_id"=> "c01758f214663a9ee67f98f283962a956b58cced",
                "private_key"=> "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCyFq4YaDFwc2MJ\ntMQj11ylfcdo2N5Id6DX7iT11nmyj3jGZCXGc3Z627j4S012wPUXrk5Zupn3BMa5\na9kZuZ/8PpHlOkWIsUmbLGloPEQPnwYG++qAdmo5nApgjkP27KDLCfErzYAKdFsg\n6SwoY9M0bK/Lxi40OP8KdfJV1xx9oKoI6OmNql6ALylCCqSlc17SWqGKHSjW7H09\ntgPWxV6Y0CCvmkb7DClWBDpBbptz0Qq5gfEOdj/ucpiS3jVJgo1IfdJoWOOXMk52\ngIaZQMTFl2fHyFb24sXVsJL0gy9gJrc72+KayKtcsjO2MbZK273ZYyilp0tiR9Qr\n6ZK2N76DAgMBAAECggEACoXljqKlfTgMO5gXWOjSXCMFA/CAnfaXpfq362Dbew4Y\nPFzRkJrk/th54w4MCz7WnKobjhjFHUAGgBFb2DXMOsr1sv5Q+oVDTKyAo/yfpu6m\nHAaJskKM/091m4P8hxwvWiABxK1Vagi7E8RDbDWN1tSvRJK2yzYs99DFW4Yk9FVP\nMszpu6Zl06es69N2sDIvN6qMngqS5CBZgc9SDoDi6R/PuXZjVsnMS1g1V1aSurtj\nvVQHPE19/BEdkuLW78LpZ9xJ4qpZfYOjprYva0qUkf3wBnA1BuERwqtumMz4s3KY\n2HcAK2BeObH5bcDzpfy4c2L05TeFXO4TGd93rp6JkQKBgQDo0N3gh3lXsJcoA+0g\ne0Es6nObX4mIdliACMbdQuK5u5BV6tLhJBNHc3jaVlCBDt4Wo0/1lvsQYTAZkPJu\ncV2nOiCmjTmT2/MlkyIz9mye1u+g1DlkETBmEvX/DrK6wxr1s/3ZttvWO+JB+hiA\nBRBSHpxfchLt+uQk7TuxwuDoUwKBgQDD0qkP7Z/B9PG1DdTlh7aMFf6bCJ41RToT\nE+RZ2vzDExQr3KtQvWmn3dFNxNjiahj3iv0okB/GCnXJNnLpWHfaI4nX75mZcy5s\ndaQwfkV/gK+CtDZeFZXGAzyXyFvokh5Yg/GxVuqDFcJZ3xwxe+ECSBYx79NCsyb/\nAbDZkuhLEQKBgQDjdl2w5ut470FfiSZ+W6270nyMyvS2bNnejbStrJGTYVuNddX+\nvZ5QatMijr4suXfmqjyO96nnwOybrfAOU5hqr/ICh2w/t9+BUJVUK2jIb7rDaVWB\nHQIqWFAbmCKrMb//WpGjynJSh0NxroWNXmUptlqVLgCsnjmUkmuGK55NnQKBgGKy\n4xNsKYGQ++LNveiSpqekzldF2Lfw1HyZIhdIEO4hx6Dz8EUZsw8w9jXEaax03XCn\nVUIEon8m9occMn76YC5Ki0eTNE/rhhKUmNT4T/8b1VqDioORTZQoPXojOm/WdgUX\nO9KuhDTd2r+BfKxUS+zieI1i25Bay+Tr7T88lGiBAoGAOmVdlopfSiiuWaE3GzAu\nPYeaHhUMHgQSemuz/hDgkFnw0RtESPfjCCTsomfQBiqx2RB2lmcg0+bAUs4P2O6l\n/CxvMCuW79aR1tu6IHUoKDvbJcS1pBCHBphC96b/YzVBRK3bqmDhhFhz5tgbeeVA\nrkmyW9GDvTet5j49YPn9ES8=\n-----END PRIVATE KEY-----\n",
                "client_email"=> "firebase-adminsdk-fbsvc@thewritemedianotifs.iam.gserviceaccount.com",
                "client_id"=> "104938808297776626575",
                "auth_uri"=> "https://accounts.google.com/o/oauth2/auth",
                "token_uri"=> "https://oauth2.googleapis.com/token",
                "auth_provider_x509_cert_url"=> "https://www.googleapis.com/oauth2/v1/certs",
                "client_x509_cert_url"=> "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-fbsvc%40thewritemedianotifs.iam.gserviceaccount.com",
                "universe_domain"=> "googleapis.com"
            ];

            $factory = (new Factory)
                ->withServiceAccount($serviceAccount)
                ->withDatabaseUri(config('firebase.database_url'));

            $messaging = $factory->createMessaging();
        }

        return $messaging;
    }


    public static function sendNotification($userId, $title, $message, $type = null, $referenceId = null)
    {
        $user = User::find($userId);
    
        if (!$user) {
            Log::warning("User not found for notification", ['user_id' => $userId]);
            return false;
        }
    
        // Save notification in DB regardless of FCM tokens
        $notification = NotificationModel::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'reference_id' => $referenceId,
            'is_read' => false,
        ]);
    
        // Check if user has any FCM tokens
        if (!empty($user->fcm_tokens) && is_array($user->fcm_tokens)) {
            try {
                $firebaseNotification = Notification::create($title, $message);
                
                foreach ($user->fcm_tokens as $token) {
                    if (!empty($token)) { // Skip empty tokens
                        $message = CloudMessage::withTarget('token', $token)
                            ->withNotification($firebaseNotification);
                        
                        self::getMessaging()->send($message);
                    }
                }
                
                Log::info('FCM notification sent successfully', [
                    'user_id' => $userId,
                    'tokens_count' => count($user->fcm_tokens),
                    'notification_id' => $notification->id
                ]);
                
            } catch (\Exception $e) {
                Log::error('FCM Error: ' . $e->getMessage(), [
                    'user_id' => $userId,
                    'notification_id' => $notification->id,
                    'error_trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            Log::warning('User has no FCM tokens', ['user_id' => $userId]);
        }
    
        return true;
    }
}