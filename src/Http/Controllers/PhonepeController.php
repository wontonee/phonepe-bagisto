<?php

namespace Wontonee\Phonepe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\InvoiceRepository;
use Wontonee\Phonepe\Services\PhonepeService;
use Wontonee\Phonepe\Services\LicenseService;
use Webkul\Checkout\Http\Requests\OrderRequest;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Sales\Transformers\OrderResource;

/**
 * Phonepe Controller
 * 
 * All logs are written to storage/logs/Phonepe.log
 */

class PhonepeController extends Controller
{
    /**
     * PhonePe service instance
     *
     * @var PhonepeService
     */
    protected $PhonepeService;

    /**
     * License service instance
     *
     * @var LicenseService
     */
    protected $licenseService;

    /**
     * Order repository instance
     *
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * Invoice repository instance
     *
     * @var InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * Cart repository instance
     *
     * @var CartRepository
     */
    protected $cartRepository;

    /**
     * Create a new controller instance.
     *
     * @param PhonepeService $PhonepeService
     * @param LicenseService $licenseService
     * @param OrderRepository $orderRepository
     * @param InvoiceRepository $invoiceRepository
     * @param CartRepository $cartRepository
     * @return void
     */
    public function __construct(
        PhonepeService $PhonepeService,
        LicenseService $licenseService,
        OrderRepository $orderRepository,
        InvoiceRepository $invoiceRepository,
        CartRepository $cartRepository
    ) {
        $this->PhonepeService = $PhonepeService;
        $this->licenseService = $licenseService;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Redirect to PhonePe payment gateway
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {

        try {
            // Validate license before processing payment
            if (!$this->licenseService->isValid()) {
                Log::channel('phonepe')->error('Payment blocked - Invalid license');
                
                return redirect()->route('shop.checkout.cart.index')
                    ->with('error', 'PhonePe payment gateway is not available. Please contact support.');
            }

            $cart = Cart::getCart();

            if (!$cart) {
                return redirect()->route('shop.checkout.cart.index')
                    ->with('error', 'Cart not found. Please try again.');
            }

            // Generate unique merchant order ID
            $merchantOrderId = 'ORD-' . time() . '-' . rand(1000, 9999);

            // Calculate amount in paisa (PhonePe requirement)
            $grandTotal = $cart->grand_total;
            $amountInPaisa = (int) round($grandTotal * 100);

            // Minimum amount check (100 paisa = ₹1)
            if ($amountInPaisa < 100) {
                return redirect()->route('shop.checkout.cart.index')
                    ->with('error', 'Minimum order amount is ₹1');
            }

            // Prepare payment data
            $paymentData = [
                'merchantOrderId' => $merchantOrderId,
                'amount' => $amountInPaisa,
                'expireAfter' => 1800, // 30 minutes default
                'paymentFlow' => [
                    'type' => 'PG_CHECKOUT',
                    'message' => 'Payment for Order #' . $cart->id,
                    'merchantUrls' => [
                        'redirectUrl' => route('phonepe.callback'),
                    ],
                ],
            ];

            // Store cart and order data in session for callback
            session([
                'phonepe_cart_id' => $cart->id,
                'phonepe_merchant_order_id' => $merchantOrderId,
                'phonepe_amount' => $grandTotal,
            ]);

            // Create payment order via PhonePe API
            $response = $this->PhonepeService->createPayment($paymentData);

            if (isset($response['redirectUrl']) && isset($response['orderId'])) {
                // Store PhonePe order ID in session
                session(['phonepe_order_id' => $response['orderId']]);

                Log::channel('phonepe')->info('Redirecting to payment gateway', [
                    'merchant_order_id' => $merchantOrderId,
                    'phonepe_order_id' => $response['orderId'],
                    'amount' => $grandTotal,
                ]);

                // Redirect to PhonePe payment page
                return redirect($response['redirectUrl']);
            }

            throw new \Exception('Invalid response from PhonePe: Missing redirect URL');

        } catch (\Exception $e) {

            Log::channel('phonepe')->error('Payment initiation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'PhonePe payment initiation failed. Please try again.');
            
            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Handle PhonePe payment callback
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        try {
            // Validate license before processing callback
            if (!$this->licenseService->isValid()) {
                Log::channel('phonepe')->error('Callback blocked - Invalid license');
                
                session()->flash('error', 'PhonePe payment gateway is not available. Please contact support.');
                
                return redirect()->route('shop.checkout.cart.index');
            }

            // Get stored session data
            $cartId = session('phonepe_cart_id');
            $merchantOrderId = session('phonepe_merchant_order_id');
            $phonepeOrderId = session('phonepe_order_id');

            if (!$cartId || !$merchantOrderId) {
                Log::channel('phonepe')->error('Callback - Missing session data', [
                    'cart_id' => $cartId,
                    'merchant_order_id' => $merchantOrderId,
                ]);
                
                session()->flash('error', 'Payment session expired. Please try again.');
                
                return redirect()->route('shop.checkout.cart.index');
            }

            // Verify payment status with PhonePe using merchant order ID
            $paymentStatus = $this->PhonepeService->checkPaymentStatus($merchantOrderId);

            Log::channel('phonepe')->info('Payment callback received', [
                'merchant_order_id' => $merchantOrderId,
                'phonepe_order_id' => $phonepeOrderId ?? 'N/A',
                'status' => $paymentStatus['state'] ?? 'unknown',
            ]);

            // Check if payment is successful
            if (isset($paymentStatus['state']) && $paymentStatus['state'] === 'COMPLETED') {
                return $this->handleSuccessfulPayment($cartId, $phonepeOrderId ?? $merchantOrderId, $paymentStatus);
            }

            // Payment failed or pending
            $errorMessage = $paymentStatus['message'] ?? 'Payment was not completed successfully';
            
            Log::channel('phonepe')->warning('Payment not successful', [
                'merchant_order_id' => $merchantOrderId,
                'phonepe_order_id' => $phonepeOrderId ?? 'N/A',
                'state' => $paymentStatus['state'] ?? 'unknown',
            ]);

            session()->flash('warning', 'Payment was not completed. Please try again.');
            
            return redirect()->route('shop.checkout.cart.index');

        } catch (\Exception $e) {
            Log::channel('phonepe')->error('Callback processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Payment verification failed. Please contact support.');
            
            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Handle successful payment and create order
     *
     * @param int $cartId
     * @param string $phonepeOrderId
     * @param array $paymentStatus
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function handleSuccessfulPayment($cartId, $phonepeOrderId, $paymentStatus)
    {
        try {
            $cart = $this->cartRepository->find($cartId);

            if (!$cart) {
                throw new \Exception('Cart not found');
            }

            // Prepare order data
            $data = (new OrderResource($cart))->jsonSerialize();
            
            // Create order
            $order = $this->orderRepository->create($data);

            // Update order with PhonePe payment details
            $this->orderRepository->update([
                'status' => 'processing',
            ], $order->id);

            // Add payment information to order
            $order->payment->update([
                'additional' => array_merge($order->payment->additional ?? [], [
                    'phonepe_order_id' => $phonepeOrderId,
                    'phonepe_payment_state' => $paymentStatus['state'] ?? null,
                    'phonepe_transaction_id' => $paymentStatus['transactionId'] ?? null,
                ]),
            ]);

            // Create invoice for the order
            $this->invoiceRepository->create($this->prepareInvoiceData($order));

            // Deactivate cart
            Cart::deActivateCart();

            // Clear PhonePe session data
            session()->forget([
                'phonepe_cart_id',
                'phonepe_merchant_order_id',
                'phonepe_order_id',
                'phonepe_amount',
            ]);

            // Flash order ID to session for success page
            session()->flash('order_id', $order->id);

           /* Log::channel('phonepe')->info('Order created successfully', [
                'order_id' => $order->id,
                'phonepe_order_id' => $phonepeOrderId,
            ]);*/

            return redirect()->route('shop.checkout.onepage.success');

        } catch (\Exception $e) {
            Log::channel('phonepe')->error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'phonepe_order_id' => $phonepeOrderId,
            ]);

            session()->flash('error', 'Order creation failed. Please contact support with your payment details.');

            throw $e;
        }
    }

    /**
     * Prepare invoice data for order
     *
     * @param \Webkul\Sales\Models\Order $order
     * @return array
     */
    protected function prepareInvoiceData($order)
    {
        $invoiceData = [
            'order_id' => $order->id,
        ];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }

    /**
     * Handle cancelled payment
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel()
    {
        // Validate license (log only, don't block cancellation)
        if (!$this->licenseService->isValid()) {
            Log::channel('phonepe')->warning('Cancel request with invalid license');
        } else {
            Log::channel('phonepe')->info('Payment cancelled by user');
        }

        // Clear session data
        session()->forget([
            'phonepe_cart_id',
            'phonepe_merchant_order_id',
            'phonepe_order_id',
            'phonepe_amount',
        ]);

        session()->flash('warning', 'Payment was cancelled. You can try again.');

        return redirect()->route('shop.checkout.cart.index');
    }
}


