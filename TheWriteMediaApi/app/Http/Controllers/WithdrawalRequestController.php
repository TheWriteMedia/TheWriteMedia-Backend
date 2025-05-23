<?php

namespace App\Http\Controllers;

use App\Mail\WithdrawalStatusMail;
use App\Models\TotalAccumulatedRoyalty;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
class WithdrawalRequestController extends Controller
{

    /**
     * Get all withdrawal requests for the authenticated author
     */
    public function authorRequests(Request $request)
    {
        $user = $request->user();
        
        $withdrawal_requests = WithdrawalRequest::where('user_id', $user->id)
            ->with(['user']) // Optional: if you want to include user details
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'withdrawal_requests' => $withdrawal_requests
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    //Show all withdrawal requests for admins and authors for withdrawal history
    $withdrawal_requests = WithdrawalRequest::with(['user'])->latest()->get();

    return response()->json([
        'withdrawal_requests' => $withdrawal_requests
    ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $user = $request->user();
    
    $request->validate([
        'name' => 'required|string|max:1000',
        'withdraw_value' => 'required|numeric',
        'mailing_address' => 'required|string|max:1000'
    ]);

    // Check for existing PENDING withdrawal request
    $existingActiveRequest = WithdrawalRequest::where('user_id', $user->id)
        ->where('status', 'PENDING')
        ->exists();

    if ($existingActiveRequest) {
        return response()->json([
            'status' => 'error',
            'message' => 'You already have an active withdrawal request'
        ], 400);
    }

    // Get user's total accumulated royalty
    $totalRoyalty = TotalAccumulatedRoyalty::where('user_id', $user->id)
        ->value('value') ?? 0;

    // Validate withdraw value against available royalty
    if ($request->withdraw_value > $totalRoyalty) {
        return response()->json([
            'status' => 'error',
            'message' => 'Withdrawal amount exceeds your available royalty balance',
            'available_royalty' => $totalRoyalty
        ], 400);
    }

    // Validate minimum withdrawal amount
    if ($request->withdraw_value < 50) {
        return response()->json([
            'status' => 'error',
            'message' => 'Minimum withdrawal amount is $50'
        ], 400);
    }

    // Create the withdrawal request
    $withdrawal_request = WithdrawalRequest::create([
        'user_id' => $user->id,
        'name' => $request->name,
        'withdraw_value' => $request->withdraw_value,
        'mailing_address' => $request->mailing_address,
        'status' => 'PENDING'
    ]);

       // Update the TotalAccumulatedRoyalty with remaining balance
       TotalAccumulatedRoyalty::where('user_id', $user->id)->update([
        'value' => $totalRoyalty - $request->withdraw_value
    ]);

       // Notify all web admins
       $this->notifyAdminsAboutWithdrawalRequest($user, $withdrawal_request);

    return response()->json([
        'status' => 'success',
        'message' => 'Withdrawal request created successfully',
        'withdrawal_request' => $withdrawal_request,
        'remaining_balance' => $totalRoyalty - $request->withdraw_value
    ], 201);
}

/**
 * Notify all web admins about a new withdrawal request
 */

 protected function notifyAdminsAboutWithdrawalRequest(User $author, WithdrawalRequest $withdrawalRequest)
 {
     $admins = User::where('user_type', User::USER_TYPE_WEB_ADMIN)
                  ->whereNotNull('fcm_tokens')  // More reliable check for MongoDB
                  ->get();
 
     $title = 'New Withdrawal Request';
     $message = "Author {$author->user_name} has requested a withdrawal of \${$withdrawalRequest->withdraw_value}";
 
     foreach ($admins as $admin) {
         // Skip if no FCM tokens
         if (empty($admin->fcm_tokens) || !is_array($admin->fcm_tokens)) {
             Log::warning('Admin has no FCM tokens', [
                 'admin_id' => $admin->_id,
                 'fcm_tokens' => $admin->fcm_tokens
             ]);
             continue;
         }
 
         try {
             $success = NotificationService::sendNotification(
                 $admin->_id,
                 $title,
                 $message,
                 'withdrawal',
                 $withdrawalRequest->id
             );
             
             Log::info('Admin notification attempt', [
                 'admin_id' => $admin->_id,
                 'withdrawal_id' => $withdrawalRequest->id,
                 'fcm_tokens' => $admin->fcm_tokens,
                 'success' => $success
             ]);
         } catch (\Exception $e) {
             Log::error('Failed to notify admin', [
                 'admin_id' => $admin->_id,
                 'error' => $e->getMessage()
             ]);
         }
     }
 }
public function cancel(WithdrawalRequest $withdrawalRequest)
{
    // Only allow cancellation if status is PENDING or PROCESSING
    if (!in_array($withdrawalRequest->status, ['PENDING', 'PROCESSING'])) {
        return response()->json([
            'status' => 'error',
            'message' => 'Only pending or processing withdrawal requests can be cancelled'
        ], 400);
    }

    // Get the current total royalty
    $totalRoyalty = TotalAccumulatedRoyalty::where('user_id', $withdrawalRequest->user_id)
        ->value('value') ?? 0;

    // Update the withdrawal request status to CANCELLED
    $withdrawalRequest->update([
        'status' => 'CANCELLED'
    ]);

    // Restore the withdrawn amount to the user's total accumulated royalty
    TotalAccumulatedRoyalty::where('user_id', $withdrawalRequest->user_id)->update([
        'value' => $totalRoyalty + $withdrawalRequest->withdraw_value
    ]);

    // Load the user relationship
    $withdrawalRequest->load('user');

      // Notify the user
      $this->notifyUserAboutWithdrawalStatus($withdrawalRequest, 'cancelled'); // or appropriate action


  // Send email
  Mail::to($withdrawalRequest->user->user_email)
  ->send(new WithdrawalStatusMail($withdrawalRequest, 'cancelled', $withdrawalRequest->user));

    return response()->json([
        'status' => 'success',
        'message' => 'Withdrawal request cancelled successfully',
        'withdrawal_request' => $withdrawalRequest,
        'updated_balance' => $totalRoyalty + $withdrawalRequest->withdraw_value
    ], 200);
}

/**
 * Mark a withdrawal request as PROCESSING (admin action)
 */
public function markAsProcessing(WithdrawalRequest $withdrawalRequest)
{
    // Only allow status change from PENDING to PROCESSING
    if ($withdrawalRequest->status !== 'PENDING') {
        return response()->json([
            'status' => 'error',
            'message' => 'Only pending withdrawal requests can be marked as processing'
        ], 400);
    }

    $withdrawalRequest->update([
        'status' => 'PROCESSING'
    ]);

      // In your controller methods, first load the relationship:
$withdrawalRequest->load('user');

   // Notify the user
   $this->notifyUserAboutWithdrawalStatus($withdrawalRequest, 'processing');

      // Send email
      Mail::to($withdrawalRequest->user->user_email)
      ->send(new WithdrawalStatusMail($withdrawalRequest, 'processing', $withdrawalRequest->user));

    return response()->json([
        'status' => 'success',
        'message' => 'Withdrawal request marked as processing',
        'withdrawal_request' => $withdrawalRequest
    ], 200);
}

/**
 * Mark a withdrawal request as MAILED (admin action)
 */
public function markAsMailed(WithdrawalRequest $withdrawalRequest)
{
    // Only allow status change from PROCESSING to MAILED
    if ($withdrawalRequest->status !== 'PROCESSING') {
        return response()->json([
            'status' => 'error',
            'message' => 'Only processing withdrawal requests can be marked as mailed'
        ], 400);
    }

    $withdrawalRequest->update([
        'status' => 'MAILED'
    ]);
      // In your controller methods, first load the relationship:
$withdrawalRequest->load('user');

 // Notify the user
 $this->notifyUserAboutWithdrawalStatus($withdrawalRequest, 'mailed');

      // Send email
      Mail::to($withdrawalRequest->user->user_email)
      ->send(new WithdrawalStatusMail($withdrawalRequest, 'mailed', $withdrawalRequest->user));

    return response()->json([
        'status' => 'success',
        'message' => 'Withdrawal request marked as mailed',
        'withdrawal_request' => $withdrawalRequest
    ], 200);
}


/**
 * Mark a withdrawal request as COMPLETED (admin action)
 */
public function markAsCompleted(WithdrawalRequest $withdrawalRequest)
{
    // Only allow status change from MAILED to COMPLETED
    if ($withdrawalRequest->status !== 'MAILED') {
        return response()->json([
            'status' => 'error',
            'message' => 'Only mailed withdrawal requests can be marked as completed'
        ], 400);
    }

    $withdrawalRequest->update([
        'status' => 'COMPLETED',
        'date_received' => now() // Set current date/time
    ]);

      // In your controller methods, first load the relationship:
$withdrawalRequest->load('user');

 // Notify the user
 $this->notifyUserAboutWithdrawalStatus($withdrawalRequest, 'completed');

     // Send email
     Mail::to($withdrawalRequest->user->user_email)
     ->send(new WithdrawalStatusMail($withdrawalRequest, 'completed', $withdrawalRequest->user));

    return response()->json([
        'status' => 'success',
        'message' => 'Withdrawal request marked as completed',
        'withdrawal_request' => $withdrawalRequest
    ], 200);
}

/**
 * Notify user about withdrawal status changes
 */
protected function notifyUserAboutWithdrawalStatus(WithdrawalRequest $withdrawalRequest, string $action): void
{
    // Ensure user relationship is loaded
    $withdrawalRequest->load('user');
    $user = $withdrawalRequest->user;
    
    if (!$user) {
        Log::warning('Withdrawal status notification failed: User not found', [
            'withdrawal_request_id' => $withdrawalRequest->id,
            'action' => $action,
            'user_id' => $withdrawalRequest->user_id
        ]);
        return;
    }

    // Check if user has any valid FCM tokens
    if (empty($user->fcm_tokens) || !is_array($user->fcm_tokens)) {
        Log::warning('Withdrawal status notification failed: User has no FCM tokens', [
            'user_id' => $user->id,
            'withdrawal_request_id' => $withdrawalRequest->id,
            'action' => $action,
            'fcm_tokens' => $user->fcm_tokens ?? null
        ]);
        
        // Still create the notification in database even without FCM tokens
        // This allows user to see it when they log in
    }

    $statusMessages = [
        'processing' => [
            'title' => 'Withdrawal Processing',
            'message' => "Your withdrawal of \${$withdrawalRequest->withdraw_value} is being processed"
        ],
        'mailed' => [
            'title' => 'Withdrawal Mailed',
            'message' => "Your withdrawal of \${$withdrawalRequest->withdraw_value} has been mailed"
        ],
        'completed' => [
            'title' => 'Withdrawal Completed',
            'message' => "Your withdrawal of \${$withdrawalRequest->withdraw_value} has been completed"
        ],
        'cancelled' => [
            'title' => 'Withdrawal Cancelled',
            'message' => "Your withdrawal of \${$withdrawalRequest->withdraw_value} has been cancelled"
        ]
    ];

    if (!isset($statusMessages[$action])) {
        Log::error('Invalid withdrawal status action', [
            'user_id' => $user->id,
            'withdrawal_request_id' => $withdrawalRequest->id,
            'action' => $action,
            'valid_actions' => array_keys($statusMessages)
        ]);
        return;
    }

    Log::info('Sending withdrawal status notification', [
        'user_id' => $user->id,
        'withdrawal_request_id' => $withdrawalRequest->id,
        'action' => $action,
        'has_fcm_tokens' => !empty($user->fcm_tokens)
    ]);

    try {
        $success = NotificationService::sendNotification(
            $user->id,
            $statusMessages[$action]['title'],
            $statusMessages[$action]['message'],
            'withdrawal_status',
            $withdrawalRequest->id
        );

        if (!$success) {
            Log::error('NotificationService returned failure', [
                'user_id' => $user->id,
                'notification_data' => $statusMessages[$action]
            ]);
        }
    } catch (\Exception $e) {
        Log::error('Failed to send withdrawal status notification', [
            'user_id' => $user->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}
    /**
     * Display the specified resource.
     */
    public function show(WithdrawalRequest $withdrawalRequest)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Withdrawal Request retrieved successfully',
            'withdrawalRequest' => $withdrawalRequest,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WithdrawalRequest $withdrawalRequest)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:1000',
            'withdraw_value' => 'required|numeric',
            'mailing_address' => 'required|string|max:1000'
        ]);
    
         // Update book fair details
         $withdrawalRequest->update($fields);
    
        return response()->json([
            'message' => 'Withdrawal Request updated successfully.',
            'withdrawal_request' => $withdrawalRequest
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WithdrawalRequest $withdrawalRequest)
    {
        $withdrawalRequest->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Withdawal Request deleted successfully',
        ], 200);
    }
}
