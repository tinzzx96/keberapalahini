<?php
header('Content-Type: application/json');

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$email = clean_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email dan password harus diisi']);
    exit;
}

// Query optimized dengan index
$sql = "SELECT id, firstName, lastName, email, password, role, status, joinDate 
        FROM users 
        WHERE email = ? 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Email tidak terdaftar']);
    exit;
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password'])) {
    if (md5($password) !== $user['password']) {
        echo json_encode(['success' => false, 'message' => 'Password salah']);
        exit;
    }
}

// Set session (minimal data)
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_firstName'] = $user['firstName'];
$_SESSION['user_lastName'] = $user['lastName'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['user_status'] = $user['status'];
$_SESSION['user_joinDate'] = date('d/m/Y', strtotime($user['joinDate']));

echo json_encode([
    'success' => true,
    'message' => 'Login berhasil',
    'user' => [
        'id' => $user['id'],
        'firstName' => $user['firstName'],
        'lastName' => $user['lastName'],
        'role' => $user['role']
    ]
]);
?>