<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
        // Set your Stripe secret key
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    
        // Get the products from the request
        $products = $request->input('products');
    
        // Prepare line items for the Stripe session
        $lineItems = [];
        foreach ($products as $product) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd', // Change to your desired currency
                    'product_data' => [
                        'name' => $product['title'],
                        'images' => [$product['image']], // Optional: Add product images
                    ],
                    'unit_amount' => $product['price'] * 100, // Convert to cents
                ],
                'quantity' => $product['quantity'],
            ];
        }
    
        try {
            // Create a Stripe checkout session with billing address collection
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'billing_address_collection' => 'required',
                'shipping_address_collection' => [
                    'allowed_countries' => null, // Remove country restrictions
                ],
                'phone_number_collection' => [
                    'enabled' => true,
                ],
                'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('checkout.cancel'),
            ]);
    
            // Return the session ID to the frontend
            return response()->json(['id' => $session->id]);
        } catch (ApiErrorException $e) {
            // Handle Stripe API errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function success(Request $request)
    {
    // Retrieve the session ID from the query parameters
    $sessionId = $request->query('session_id');

    // Fetch the session details from Stripe (optional)
    Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    $session = Session::retrieve($sessionId);

    // You can also save the payment details to your database here

    return view('checkout.success', ['session' => $session]);
    }

    public function cancel()
    {
        return view('checkout.cancel');
    }
}
