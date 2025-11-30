<?php
require_once 'config/config.php';
require_once 'config/midtrans_config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Cek parameter
if (!isset($_GET['plan_id']) || !isset($_GET['plan_name']) || !isset($_GET['price'])) {
    header('Location: index.php');
    exit;
}

$plan_id = intval($_GET['plan_id']);
$plan_name = htmlspecialchars($_GET['plan_name']);
$price = floatval($_GET['price']);

// Ambil data user
$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$user_name = $_SESSION['user_firstName'] . ' ' . $_SESSION['user_lastName'];

// Generate order ID
$order_id = 'ORDER-' . $user_id . '-' . time();

// Customer details untuk Midtrans
$customer_details = [
    'first_name' => $_SESSION['user_firstName'],
    'last_name' => $_SESSION['user_lastName'],
    'email' => $user_email,
    'phone' => '08123456789'
];

// Item details
$item_details = [
    [
        'id' => 'PLAN-' . $plan_id,
        'price' => (int)$price,
        'quantity' => 1,
        'name' => 'Paket ' . $plan_name . ' - 30 Hari'
    ]
];

// ✅ CREATE SNAP TOKEN
$snap_result = createMidtransToken($order_id, $price, $customer_details, $item_details);

// ✅ CEK APAKAH BERHASIL
if (!$snap_result['success']) {
    // Show error page
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Error - PoncolVerse</title>
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
      <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
          font-family: 'Poppins', sans-serif;
          background: linear-gradient(135deg, #0a0a0a 0%, #1a0a0f 100%);
          color: #fff;
          min-height: 100vh;
          display: flex;
          justify-content: center;
          align-items: center;
          padding: 2rem;
        }
        .error-container {
          background: linear-gradient(145deg, #1a1a1a, #0f0f0f);
          border: 2px solid rgba(255, 0, 60, 0.3);
          border-radius: 20px;
          padding: 3rem;
          max-width: 500px;
          width: 100%;
          text-align: center;
        }
        .error-icon {
          width: 80px;
          height: 80px;
          background: rgba(255, 0, 60, 0.1);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          margin: 0 auto 2rem;
          font-size: 2.5rem;
          color: #ff003c;
        }
        h1 {
          font-size: 2rem;
          margin-bottom: 1rem;
          color: #ff003c;
        }
        .error-message {
          background: rgba(255, 0, 60, 0.1);
          border: 1px solid rgba(255, 0, 60, 0.3);
          border-radius: 10px;
          padding: 1rem;
          margin: 2rem 0;
          color: #ddd;
        }
        .back-btn {
          display: inline-block;
          padding: 1rem 2rem;
          background: linear-gradient(135deg, #ff003c, #ff4d7a);
          border: none;
          border-radius: 50px;
          color: white;
          font-weight: 700;
          text-decoration: none;
          transition: all 0.3s;
        }
        .back-btn:hover {
          transform: translateY(-3px);
          box-shadow: 0 10px 30px rgba(255, 0, 60, 0.5);
        }
      </style>
    </head>
    <body>
      <div class="error-container">
        <div class="error-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1>Gagal Membuat Pembayaran</h1>
        <div class="error-message">
          <strong>Error:</strong> <?php echo htmlspecialchars($snap_result['message']); ?>
        </div>
        <p style="color: #aaa; margin: 1rem 0;">
          Pastikan Midtrans Server Key dan Client Key sudah benar di <code>config/midtrans_config.php</code>
        </p>
        <a href="index.php#paket" class="back-btn">
          <i class="fas fa-arrow-left"></i> Kembali ke Paket
        </a>
      </div>
    </body>
    </html>
    <?php
    exit;
}

$snap_token = $snap_result['token'];

// Save transaction ke database
$sql = "INSERT INTO transactions (user_id, subscription_plan_id, order_id, gross_amount, payment_type, transaction_status, created_at) 
        VALUES (?, ?, ?, ?, 'midtrans', 'pending', NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iisd", $user_id, $plan_id, $order_id, $price);
$stmt->execute();
$transaction_id = $conn->insert_id;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pembayaran - PoncolVerse</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0a0a0a 0%, #1a0a0f 100%);
      color: #fff;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 2rem;
    }
    .payment-container {
      background: linear-gradient(145deg, #1a1a1a, #0f0f0f);
      border: 2px solid rgba(255, 0, 60, 0.3);
      border-radius: 20px;
      padding: 3rem;
      max-width: 500px;
      width: 100%;
      text-align: center;
    }
    .payment-icon {
      width: 80px;
      height: 80px;
      background: rgba(255, 0, 60, 0.1);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 2rem;
      font-size: 2.5rem;
      color: #ff003c;
    }
    h1 {
      font-family: 'Orbitron', sans-serif;
      font-size: 2rem;
      margin-bottom: 1rem;
      background: linear-gradient(135deg, #ff003c, #ff4d7a);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }
    .plan-details {
      background: rgba(255, 0, 60, 0.05);
      border: 1px solid rgba(255, 0, 60, 0.2);
      border-radius: 15px;
      padding: 1.5rem;
      margin: 2rem 0;
    }
    .plan-name {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    .plan-price {
      font-size: 2.5rem;
      font-weight: 700;
      color: #ff003c;
      margin: 1rem 0;
    }
    .order-info {
      font-size: 0.9rem;
      color: #aaa;
      margin-bottom: 2rem;
    }
    .pay-btn {
      width: 100%;
      padding: 1rem;
      background: linear-gradient(135deg, #ff003c, #ff4d7a);
      border: none;
      border-radius: 50px;
      color: white;
      font-size: 1.1rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .pay-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(255, 0, 60, 0.5);
    }
    .cancel-link {
      display: inline-block;
      margin-top: 1.5rem;
      color: #aaa;
      text-decoration: none;
      transition: color 0.3s;
    }
    .cancel-link:hover { color: #ff003c; }
  </style>
</head>
<body>
  <div class="payment-container">
    <div class="payment-icon">
      <i class="fas fa-credit-card"></i>
    </div>
    
    <h1>Checkout Pembayaran</h1>
    
    <div class="plan-details">
      <div class="plan-name">Paket <?php echo $plan_name; ?></div>
      <div class="plan-price">Rp<?php echo number_format($price, 0, ',', '.'); ?></div>
      <div>30 Hari Akses Penuh</div>
    </div>
    
    <div class="order-info">
      <strong>Order ID:</strong> <?php echo $order_id; ?><br>
      <strong>Atas Nama:</strong> <?php echo $user_name; ?>
    </div>
    
    <button class="pay-btn" id="pay-button">
      <i class="fas fa-lock"></i> Bayar Sekarang
    </button>
    
    <a href="index.php#paket" class="cancel-link">
      <i class="fas fa-arrow-left"></i> Batal
    </a>
  </div>

  <script src="<?php echo MIDTRANS_SNAP_URL; ?>" data-client-key="<?php echo MIDTRANS_CLIENT_KEY; ?>"></script>
  <script>
    const payButton = document.getElementById('pay-button');
    
    payButton.addEventListener('click', function() {
      snap.pay('<?php echo $snap_token; ?>', {
        onSuccess: function(result) {
          console.log('Payment success:', result);
          window.location.href = 'payment_callback.php?order_id=<?php echo $order_id; ?>&status=success';
        },
        onPending: function(result) {
          console.log('Payment pending:', result);
          window.location.href = 'payment_callback.php?order_id=<?php echo $order_id; ?>&status=pending';
        },
        onError: function(result) {
          console.log('Payment error:', result);
          alert('Pembayaran gagal! Silakan coba lagi.');
        },
        onClose: function() {
          console.log('Payment popup closed');
        }
      });
    });
  </script>
</body>
</html>