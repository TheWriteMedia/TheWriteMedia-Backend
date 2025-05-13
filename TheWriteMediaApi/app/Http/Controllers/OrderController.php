<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Mail\OrderStatusUpdate;
use App\Mail\ShippingFeeUpdate;
use App\Models\Order;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
class OrderController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Show all orders in descending order by creation date
        $orders = Order::orderBy('created_at', 'desc')->get();

        return response()->json([
            'orders' => $orders
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'contactNo' => 'required',
            'country' => 'required',
            'addressLine1' => 'required',
            'province' => 'required',
            'city' => 'required',
            'postalCode' => 'required',
            'items' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $order = Order::create([
                'email_address' => $request->email,
                'contactno' => $request->contactNo,
                'country' => $request->country,
                'address_line_one' => $request->addressLine1,
                'address_line_two' => $request->addressLine2 ?? '',
                'province' => $request->province,
                'city' => $request->city,
                'postal_code' => $request->postalCode,
                'items' => $request->items,
                'total' => $request->total,
                'shipping_fee' => 0, // Default to 0
                'status' => 'PENDING' // Explicitly set status
            ]);

                // Send email confirmation to customer
                $this->sendOrderConfirmationEmail($order);

                // Notify all admins about the new order
                $this->notifyAdminsAboutNewOrder($order);
    

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

       /**
     * Send order confirmation email to customer
     */
    protected function sendOrderConfirmationEmail(Order $order)
    {
        try {
            Mail::to($order->email_address)->send(new OrderConfirmation($order));
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'email' => $order->email_address
            ]);
        }
    }

       /**
     * Notify all web admins about a new order
     */
    protected function notifyAdminsAboutNewOrder(Order $order)
    {
        $admins = User::where('user_type', User::USER_TYPE_WEB_ADMIN)
                     ->whereNotNull('fcm_tokens')
                     ->get();

        $title = 'New Order Received';
        $message = "New order #{$order->id} for \${$order->total} from {$order->email_address}";

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
                    'order',
                    $order->id
                );
                
                Log::info('Admin notification attempt', [
                    'admin_id' => $admin->_id,
                    'order_id' => $order->id,
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

    
    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

     /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
{
    $validator = Validator::make($request->all(), [
        'shipping_fee' => 'sometimes|numeric|min:0',
        'status' => 'sometimes|in:PENDING,PROCESSING,SHIPPED,COMPLETED,CANCELLED',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        $updates = [];
        $statusChanged = false;
        $shippingFeeChanged = false;
        $oldTotal = $order->total;

        if ($request->has('shipping_fee')) {
            $updates['shipping_fee'] = $request->shipping_fee;
            // Recalculate total
            $itemsTotal = array_reduce($order->items, function($sum, $item) {
                return $sum + ($item['price'] * $item['quantity']);
            }, 0);
            $updates['total'] = $itemsTotal + $request->shipping_fee;
            $shippingFeeChanged = true;
        }

        if ($request->has('status')) {
            // Validate status transition
            if ($request->status === 'PROCESSING' && $order->shipping_fee <= 0) {
                return response()->json([
                    'message' => 'Cannot set status to PROCESSING without shipping fee',
                ], 400);
            }

            $updates['status'] = $request->status;
            $statusChanged = true;
        }

        $order->update($updates);

        if ($shippingFeeChanged) {
            // Send shipping fee update email
            Mail::to($order->email_address)->send(new ShippingFeeUpdate($order, $oldTotal));
        }

        if ($statusChanged) {
            // Send email notification to customer
            Mail::to($order->email_address)->send(new OrderStatusUpdate($order));
            
            // Notify admins about status change
            $this->notifyAdminsAboutStatusChange($order);
        }

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to update order',
            'error' => $e->getMessage()
        ], 500);
    }
}
    protected function notifyAdminsAboutStatusChange(Order $order)
    {
        $admins = User::where('user_type', User::USER_TYPE_WEB_ADMIN)
                     ->whereNotNull('fcm_tokens')
                     ->get();

        $title = 'Order Status Updated';
        $message = "Order #{$order->id} status changed to {$order->status}";

        foreach ($admins as $admin) {
            if (empty($admin->fcm_tokens) || !is_array($admin->fcm_tokens)) continue;

            try {
                NotificationService::sendNotification(
                    $admin->_id,
                    $title,
                    $message,
                    'order_status',
                    $order->id
                );
            } catch (\Exception $e) {
                Log::error('Failed to notify admin', [
                    'admin_id' => $admin->_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Order deleted successfully',
        ], 200);
    }
}
