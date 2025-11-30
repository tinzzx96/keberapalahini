<?php
require_once 'config/config.php';
require_once 'config/midtrans_config.php';

echo "<h1>Midtrans Connection Test</h1>";

echo "<h2>Configuration:</h2>";
echo "Server Key: " . substr(MIDTRANS_SERVER_KEY, 0, 20) . "...xxxxx<br>";
echo "Client Key: " . substr(MIDTRANS_CLIENT_KEY, 0, 20) . "...xxxxx<br>";
echo "API URL: " . MIDTRANS_API_URL . "<br>";
echo "Is Production: " . (MIDTRANS_IS_PRODUCTION ? 'YES' : 'NO') . "<br>";

echo "<h2>Testing API Connection...</h2>";

// Test data
$order_id = 'TEST-' . time();
$gross_amount = 120000;

$customer_details = [
    'first_name' => 'Test',
    'last_name' => 'User',
    'email' => 'test@example.com',
    'phone' => '08123456789'
];

$item_details = [
    [
        'id' => 'TEST-ITEM',
        'price' => 120000,
        'quantity' => 1,
        'name' => 'Test Payment'
    ]
];

$result = createMidtransToken($order_id, $gross_amount, $customer_details, $item_details);

echo "<h3>API Response:</h3>";
echo "<pre>";
print_r($result);
echo "</pre>";

if ($result['success']) {
    echo "<div style='background: #00ff88; padding: 20px; color: #000; border-radius: 10px;'>";
    echo "<h2>✅ MIDTRANS API CONNECTION SUCCESSFUL!</h2>";
    echo "Snap Token: " . $result['token'];
    echo "</div>";
} else {
    echo "<div style='background: #ff003c; padding: 20px; color: #fff; border-radius: 10px;'>";
    echo "<h2>❌ MIDTRANS API CONNECTION FAILED!</h2>";
    echo "Error: " . $result['message'];
    echo "</div>";
    
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Check if Server Key and Client Key are correct</li>";
    echo "<li>Make sure payment methods are enabled in Midtrans Dashboard</li>";
    echo "<li>Check your internet connection</li>";
    echo "<li>Verify you're using Sandbox keys (starts with SB-Mid-...)</li>";
    echo "</ul>";
}
?>