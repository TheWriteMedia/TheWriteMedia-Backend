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
                "project_id"=> "thewritemedia-c3f40",
                "private_key_id"=> "640dcd73607bb74ca9ac4863764a40778fd423cc",
                "private_key"=> "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCa4pU8LfE8ksdt\nAgYQZGeJ0awRPXuY5qBRR7G78+W6EqLxhfT9W6Sje+pOJtvenYOxf18fD7fywQYw\noKFFJqzJOKilfd5zynxCxu8FVBu86/pXArSZ1aCmrdiQk2G3QqSalH+sKodHQ/ED\nAjjJKpRN2wnIR135w7ni2Q2xokWQDn3fkKAL4JEYhjfVYtU8RZEJCIV+8TPAgoV+\nQBxoE12U+iHnR0XvX+WydnSGjG2DE9pXrzfSb4uhKb8JHbFTQq29LE4+IQfLY2sT\nYHkEPwH8qXEQUPr/Kue/6KTovP9H0Me4TtG/h9T83cfbbe/xCi0MhLUeWM+I37Eu\neZqgG0axAgMBAAECggEAFBhBGetxALWfY5RvYnmQWFhobV0vd7U2SA1X8SVx4OwR\nkWnakPNunxpQ2h5fCNkhUKpQlkhVCo0CB1WTztO3d6ze7oev0JCNAiHquWOB7Mcl\n9dt/QHUvsGheMFW6yArAreCF1vm2juew8X28jdb1IkvAj2g4+FJtP9H+3ZTK1ydi\ngHCX0Iw1qNoDO/SzCruGJ8LrEvlUpjU0aNKEE17ZkJAjrP3QVGhzVY1kYc0xdqYx\nqKpn5J1yOQUl8RqYFofV9715ZtDseK1HchNdFK5LH7yWBUdm0nr93Ogsjg53iVC1\ntU1xck0AybK2Wz0in258awjuknuJP2Wdcy7pH/+VVQKBgQDHfbNm9BrqXrYJ5b9E\n6vknYdcHI1cI+YdcRWoIKCPiYIcyvF6cUteyO1DJQ1ozueLJyLB7EuZ4Ao+rFSLa\n6Fa1r474dJS+xjuYXxs6FOr0I4XBtLbrdLPY5UO63yC2HQAi7CDVpoiZTcOcvd9l\nP50G3DAQf8awnUk7ACeOK72rxQKBgQDGwjyY4muN/Yc5s6LH77tZQh5uxc+LcfAs\n9kzOH9Pf0CVWx7BErt3pNEVKs1p41ivDkcFpyvGUGyWoyppbyM7ayyVGi0ChZhxL\nqsUIvUM+pQnzNcYHvxlrbIQRUFsbX0VH87MAY2FOlfCFH+AX3AiGin5HcCIz6FFQ\n4DPEEBXB/QKBgQCpKUE4uyQbu2ZX7DcN5MaUC5ZAGkqSdK3Ao6fu9MLBEqNydWMw\n4dq/6yZtFCzoEedqvkBQjM1b1KgpJcohoMTuWck9i/YZ85TTd0pqLRSzI3Anuusl\nrBdZg8e0LcLaSqsz8dFB+m54uQV341YM0C2ChLJJO2CroSRY4J+qIhM7FQKBgB5t\ng/tfxllxc684ufXj1ImgnqoUrGZLphosBqwToUsFQYIchfat0pkuAGGkPXh8SSzi\ndnqsr/kdgtFpWuIsRsamDOnUzasnx7MpzIo+9zly6KgMFmn/LnQMo9dGtvvkxOV0\nAHZRwIbYW37110evzrpSH2zuVL4flbtM53/feoU9AoGBAMMkQKGjYDj5uutrD3dZ\nwuHjQE2VCvPBHT2hCdMRWDcLqe5DjT1OrBip7wqpAVoKAC11Q9WLzFXnZ5bTuNCi\nOvjaeO9mh8Au55w9NNLMJ8Bo7gk/jP8OQ7WqRd8sCxhLaCg3xCN3iAsiB5GfeSBH\nhSBK20Zknbi7IjM1SUv8gTVv\n-----END PRIVATE KEY-----\n",
                "client_email"=> "firebase-adminsdk-fbsvc@thewritemedia-c3f40.iam.gserviceaccount.com",
                "client_id"=> "108640634725326739625",
                "auth_uri"=> "https://accounts.google.com/o/oauth2/auth",
                "token_uri"=> "https://oauth2.googleapis.com/token",
                "auth_provider_x509_cert_url"=> "https://www.googleapis.com/oauth2/v1/certs",
                "client_x509_cert_url"=> "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-fbsvc%40thewritemedia-c3f40.iam.gserviceaccount.com",
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