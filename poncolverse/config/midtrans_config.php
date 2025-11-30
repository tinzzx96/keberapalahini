<?php
// Midtrans Configuration
// ⚠️ GANTI DENGAN KEY LU!
define('MIDTRANS_SERVER_KEY', 'xxx'); // atau Mid-server-xxx (non-sandbox)
define('MIDTRANS_CLIENT_KEY', 'xxx'); // atau Mid-client-xxx (non-sandbox)
define('MIDTRANS_IS_PRODUCTION', false); // false = sandbox, true = production
define('MIDTRANS_IS_SANITIZED', true);
define('MIDTRANS_IS_3DS', true);

// Midtrans API URL
if (MIDTRANS_IS_PRODUCTION) {
    define('MIDTRANS_SNAP_URL', 'https://app.midtrans.com/snap/snap.js');
    define('MIDTRANS_API_URL', 'https://api.midtrans.com/v2');
} else {
    define('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/snap.js');
    define('MIDTRANS_API_URL', 'https://api.sandbox.midtrans.com/v2');
}

/**
 * Create Midtrans Snap Token
 * 
 * @param string $order_id Unique order ID
 * @param float $gross_amount Total payment amount
 * @param array $customer_details Customer information
 * @param array $item_details Items to be paid
 * @return array ['success' => bool, 'token' => string, 'message' => string]
 */
function createMidtransToken($order_id, $gross_amount, $customer_details, $item_details) {
    // ✅ COMPLETE PARAMS (INI YANG KURANG!)
    $params = [
        'transaction_details' => [
            'order_id' => $order_id,
            'gross_amount' => (int)$gross_amount
        ],
        'customer_details' => $customer_details,
        'item_details' => $item_details,
        
        // ✅ ENABLED PAYMENTS (WAJIB!)
        'enabled_payments' => [
            'credit_card',
            'gopay',
            'shopeepay', 
            'other_qris',
            'bca_va',
            'bni_va',
            'bri_va',
            'permata_va',
            'echannel', // Mandiri Bill
            'other_va',
            'indomaret',
            'alfamart'
        ],
        
        // ✅ CREDIT CARD SETTINGS
        'credit_card' => [
            'secure' => true,
            'bank' => 'bca',
            'installment' => [
                'required' => false
            ],
            'whitelist_bins' => []
        ],
        
        // ✅ CALLBACKS
        'callbacks' => [
            'finish' => 'http://localhost/poncolverse/payment_callback.php?status=success'
        ],
        
        // ✅ CUSTOMER DETAILS (LENGKAP)
        'customer_details' => [
            'first_name' => $customer_details['first_name'] ?? 'Customer',
            'last_name' => $customer_details['last_name'] ?? '',
            'email' => $customer_details['email'] ?? 'customer@example.com',
            'phone' => $customer_details['phone'] ?? '08123456789',
            'billing_address' => [
                'first_name' => $customer_details['first_name'] ?? 'Customer',
                'last_name' => $customer_details['last_name'] ?? '',
                'email' => $customer_details['email'] ?? 'customer@example.com',
                'phone' => $customer_details['phone'] ?? '08123456789',
                'address' => 'Jl. Midtrans No. 1',
                'city' => 'Jakarta',
                'postal_code' => '12345',
                'country_code' => 'IDN'
            ],
            'shipping_address' => [
                'first_name' => $customer_details['first_name'] ?? 'Customer',
                'last_name' => $customer_details['last_name'] ?? '',
                'email' => $customer_details['email'] ?? 'customer@example.com',
                'phone' => $customer_details['phone'] ?? '08123456789',
                'address' => 'Jl. Midtrans No. 1',
                'city' => 'Jakarta',
                'postal_code' => '12345',
                'country_code' => 'IDN'
            ]
        ]
    ];
    
    // ✅ CURL REQUEST
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => MIDTRANS_API_URL . '/snap/transactions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($params),
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false, // Untuk localhost (production harus true!)
        CURLOPT_SSL_VERIFYHOST => false, // Untuk localhost (production harus true!)
        CURLOPT_VERBOSE => true // Untuk debugging
    ]);
    
    // ✅ EXECUTE
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    curl_close($curl);
    
    // ✅ LOG UNTUK DEBUGGING
    error_log("=== MIDTRANS API REQUEST ===");
    error_log("URL: " . MIDTRANS_API_URL . '/snap/transactions');
    error_log("Order ID: " . $order_id);
    error_log("Amount: " . $gross_amount);
    error_log("HTTP Code: " . $http_code);
    error_log("Response: " . $response);
    if ($curl_error) {
        error_log("cURL Error: " . $curl_error);
    }
    error_log("=== END ===");
    
    // ✅ HANDLE CURL ERROR
    if ($curl_error) {
        return [
            'success' => false,
            'message' => 'Connection error: ' . $curl_error,
            'error_type' => 'curl_error'
        ];
    }
    
    // ✅ DECODE RESPONSE
    $result = json_decode($response, true);
    
    // ✅ HANDLE SUCCESS (HTTP 201)
    if ($http_code == 201 && isset($result['token'])) {
        return [
            'success' => true,
            'token' => $result['token'],
            'redirect_url' => $result['redirect_url'] ?? ''
        ];
    }
    
    // ✅ HANDLE ERROR (HTTP 4xx, 5xx)
    $error_message = 'Failed to create payment token';
    
    if (isset($result['error_messages']) && is_array($result['error_messages'])) {
        $error_message = implode(', ', $result['error_messages']);
    } elseif (isset($result['message'])) {
        $error_message = $result['message'];
    } elseif (isset($result['error'])) {
        $error_message = $result['error'];
    }
    
    // ✅ HANDLE SPECIFIC ERROR CODES
    switch ($http_code) {
        case 400:
            $error_message = 'Bad Request: ' . $error_message . ' (Cek parameter yang dikirim)';
            break;
        case 401:
            $error_message = 'Unauthorized: Server Key salah atau tidak valid';
            break;
        case 402:
            $error_message = 'Payment Required: Merchant belum aktif atau belum setup payment method';
            break;
        case 403:
            $error_message = 'Forbidden: Akses ditolak. Cek merchant account status';
            break;
        case 404:
            $error_message = 'Not Found: Endpoint tidak ditemukan';
            break;
        case 500:
            $error_message = 'Server Error: Midtrans server sedang bermasalah';
            break;
        case 503:
            $error_message = 'Service Unavailable: Midtrans maintenance';
            break;
    }
    
    return [
        'success' => false,
        'message' => $error_message,
        'http_code' => $http_code,
        'raw_response' => $response,
        'error_type' => 'api_error'
    ];
}

/**
 * Verify Midtrans Signature (untuk webhook/notification)
 */
function verifyMidtransSignature($order_id, $status_code, $gross_amount, $signature_key) {
    $hash = hash('sha512', $order_id . $status_code . $gross_amount . MIDTRANS_SERVER_KEY);
    return $signature_key === $hash;
}

/**
 * Get Transaction Status dari Midtrans
 */
function getMidtransTransactionStatus($order_id) {
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => MIDTRANS_API_URL . '/v2/' . $order_id . '/status',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($http_code == 200) {
        return json_decode($response, true);
    }
    
    return null;
}
?>
```

---

## 🔍 **CEK ERROR LOG**

Sekarang setiap request ke Midtrans akan **LOG** di PHP error log. Cek error log:

**Windows XAMPP:**
```
C:\xampp\apache\logs\error.log
```

**Cari baris:**
```
=== MIDTRANS API REQUEST ===