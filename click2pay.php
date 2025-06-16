<?php
if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

use Tygh\Http;
use Tygh\Registry;
use Tygh\Mailer;

if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'return') {
        $order_id       = $_REQUEST['order_id'] ?? 0;
        $transaction_id = $_REQUEST['transaction_id'] ?? '';

        if (!$order_id || !$transaction_id) {
            fn_set_notification('E', __('error'), 'Invalid payment response.');
            fn_redirect(fn_url("checkout.cart"));
            exit;
        }

        // Get order info
        $order_info = fn_get_order_info($order_id);
        if (empty($order_info)) {
            fn_set_notification('E', __('error'), 'Order not found.');
            fn_redirect(fn_url("checkout.cart"));
            exit;
        }

        // Get processor data
        $processor_data = fn_get_processor_data($order_info['payment_id']);
        if (empty($processor_data)) {
            fn_set_notification('E', __('error'), 'Payment processor data not found.');
            fn_redirect(fn_url("checkout.cart"));
            exit;
        }

        // Verify payment status with Click2Pay
        $response = fn_click2pay_verify_payment($transaction_id, $processor_data);

        if (isset($response['OrderStatus'])) {
            switch ($response['OrderStatus']) {
                case 2: // Payment successful
                    $pp_response = [
                        'order_status'   => 'P', // Mark order as Paid
                        'transaction_id' => $transaction_id,
                        'reason_text'    => 'Payment completed successfully.',
                    ];
                    fn_finish_payment($order_id, $pp_response);
                    fn_change_order_status($order_id, 'P', true);

                    fn_order_placement_routines('route', $order_id);
                    fn_send_order_confirmation_email($order_id);

                    // Clear cart after successful payment
                    unset($_SESSION['cart']);

                    fn_redirect('https://boukle.com/payment-confirmed');
                    exit;

                case 0: // Payment canceled by user
                    fn_set_notification('W', __('warning'), 'Payment was canceled.');
                    fn_redirect(fn_url("checkout.cart"));
                    exit;

                case 6: // Payment declined (insufficient funds, card expired, etc.)
                    $error_message = $response['ErrorMessage'] ?? 'Payment declined. Please try again.';
                    fn_set_notification('E', __('error'), $error_message);
                    exit; // Keep the user on Click2Pay page

                default: // Generic failure
                    fn_set_notification('E', __('error'), $response['ErrorMessage'] ?? 'Payment failed.');
                    fn_redirect(fn_url("checkout.cart"));
                    exit;
            }
        } else {
            fn_set_notification('E', __('error'), 'Unexpected error occurred.');
            fn_redirect(fn_url("checkout.cart"));
            exit;
        }
    }
} else {
    // ORDER INITIALIZATION
    $order_id  = $order_info['order_id'];
    $amount    = $order_info['total'] * 1000; // Convert to smallest currency unit
    $currency  = 788; // TND currency code

    $return_url = "https://boukle.com/index.php?dispatch=payment_notification.return&payment=click2pay&order_id=$order_id";
    $fail_url   = "https://boukle.com/index.php?dispatch=checkout.cart";


    $description = "Order #$order_id";

    // Retrieve processor data using the payment method ID from order_info
    $processor_data = fn_get_processor_data($order_info['payment_id']);

    // Extract additional settings from CS-Cart
    $page_view = (Registry::get('settings.Appearance.default_customer_area') === 'mobile') ? 'Mobile' : 'Desktop';
    $language  = Registry::get('settings.Appearance.backend_default_language');

    $payment_data = [
        'userName'           => $processor_data['processor_params']['username'],
        'password'           => $processor_data['processor_params']['password'],
        'orderNumber'        => $order_id,
        'amount'             => $amount,
        'currency'           => $currency,
        'returnUrl'          => $return_url, // Success URL
        'failUrl'            => $fail_url,   // CS-Cart failure page
        'description'        => $description,
        'pageView'           => $page_view,
        'language'           => $language,
        'sessionTimeoutSecs' => 1200,
    ];

    $response = fn_click2pay_generate_payment($payment_data);

    if (!empty($response['formUrl'])) {
        fn_redirect($response['formUrl'], true);
        exit;
    } else {
        fn_set_notification('E', __('error'), $response['errorMessage'] ?? 'Payment initialization failed.');
        fn_order_placement_routines('checkout');
    }
}

/**
 * Generate a payment request to Click2Pay.
 *
 * @param array $data Payment data
 * @return array Response from Click2Pay
 */
function fn_click2pay_generate_payment($data) {
    $url = 'https://test.clictopay.com/payment/rest/register.do';
    $response = Http::post($url, $data);
    return json_decode($response, true);
}

/**
 * Verify the payment status with Click2Pay.
 *
 * @param string $transaction_id Transaction ID from Click2Pay
 * @param array $processor_data Processor data
 * @return array Response from Click2Pay
 */
function fn_click2pay_verify_payment($transaction_id, $processor_data) {
    $url = 'https://test.clictopay.com/payment/rest/getOrderStatus.do';
    $params = [
        'userName' => $processor_data['processor_params']['username'],
        'password' => $processor_data['processor_params']['password'],
        'orderId'  => $transaction_id,
    ];

    $response = Http::get($url, $params);
    return json_decode($response, true);
}

/**
 * Send order confirmation email to the user.
 *
 * @param int $order_id Order ID
 */
function fn_send_order_confirmation_email($order_id) {
    $order_info = fn_get_order_info($order_id);
    Mailer::sendMail([
        'to'        => $order_info['email'],
        'from'      => 'no-reply@boukle.com',
        'subject'   => __('order_confirmation') . ' - Order #' . $order_info['order_id'],
        'body'      => __('order_confirmation_text', ['[order_id]' => $order_id]),
    ], 'C');
}
?>
