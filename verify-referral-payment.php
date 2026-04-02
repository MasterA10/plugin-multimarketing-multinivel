<?php
/**
 * Verification Script: Referral Payment Tracking (FIXED PATHS)
 */
$wp_load = __DIR__ . '/../../../wp-load.php';
if (!file_exists($wp_load)) {
    die("Cannot find wp-load.php at $wp_load");
}
require_once $wp_load;

echo "### Starting Referral Payment Verification ###\n";

global $wpdb;
$table_referrals = $wpdb->prefix . 'lms_referrals';

// 1. Check if columns exist
$check_order_id = $wpdb->get_results( "SHOW COLUMNS FROM `$table_referrals` LIKE 'order_id'" );
$check_order_total = $wpdb->get_results( "SHOW COLUMNS FROM `$table_referrals` LIKE 'order_total'" );

if ( ! empty( $check_order_id ) && ! empty( $check_order_total ) ) {
    echo "[SUCCESS] Database columns 'order_id' and 'order_total' exist.\n";
} else {
    echo "[INFO] Missing columns. Attempting to add them...\n";
    $activator_path = __DIR__ . '/expressive-core/includes/class-expressive-activator.php';
    if (file_exists($activator_path)) {
        require_once $activator_path;
        Expressive_Activator::activate();
        echo "[INFO] Activation run. Re-checking columns...\n";
        $check_order_id = $wpdb->get_results( "SHOW COLUMNS FROM `$table_referrals` LIKE 'order_id'" );
        if (!empty($check_order_id)) {
             echo "[SUCCESS] Columns added successfully.\n";
        } else {
             echo "[FAILURE] Could not add columns automatically.\n";
        }
    } else {
        echo "[FAILURE] Activator not found at $activator_path\n";
    }
}

// 2. Create a Mock Order with a referral
if (!class_exists('WC_Order')) {
    echo "WooCommerce is NOT active. Cannot proceed.\n";
    exit;
}

$user_email = 'payment_test_' . time() . '@example.com';
$student_id = wp_create_user( 'payment_test_' . time(), 'pass123', $user_email );
$order = wc_create_order( array( 'customer_id' => $student_id ) );
$order->set_total( 250.00 );
$order->update_meta_data( '_exp_referred_by', 'admin' ); 
$order->save();

echo "Mock Order ID: " . $order->get_id() . " with Total: 250.00\n";

// 3. Trigger the completion hook
do_action( 'woocommerce_order_status_completed', $order->get_id() );
echo "Hook 'woocommerce_order_status_completed' triggered.\n";

// 4. Verify the entry in lms_referrals
$entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_referrals WHERE order_id = %d", $order->get_id() ) );

if ( $entry ) {
    echo "[SUCCESS] Referral entry found!\n";
    echo "Order ID in DB: " . $entry->order_id . "\n";
    echo "Order Total in DB: " . $entry->order_total . "\n";
} else {
    echo "[FAILURE] No referral record found for this order.\n";
}

echo "\n### Verification Finished ###\n";
