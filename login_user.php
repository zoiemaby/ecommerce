<!-- <

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function send_json($payload, $http_code = 200) {
    http_response_code($http_code);
    echo json_encode($payload);
    exit;
}


function redirect_or_json($success, $message, $redirect = '/', $data = []) {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($isAjax) {
        send_json(array_merge([
            'status' => $success ? 'success' : 'error',
            'message' => $message,
            'redirect' => $redirect
        ], $data), $success ? 200 : 400);
    } else {

        $_SESSION['flash_message'] = $message;
        header('Location: ' . $redirect);
        exit;
    }
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['status' => 'error', 'message' => 'Invalid request method.'], 405);
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($email === '' || $password === '') {
    send_json(['status' => 'error', 'message' => 'Email and password are required.'], 400);
}


$controllerPath = '../controllers/user_controller.php';
if (file_exists($controllerPath)) {
    require_once $controllerPath;
} else {
    if (file_exists('../controllers/user_controller.php')) {
        require_once'../controllers/user_controller.php';
    }
}


$user = false;
if (function_exists('login_customer_ctr')) {
    try {
        $user = login_customer_ctr($email, $password);
    } catch (Throwable $e) {
        send_json(['status' => 'error', 'message' => 'Server error during login.'], 500);
    }
} elseif (function_exists('login_user') ) {
    try {
        $user = login_user($email, $password);
    } catch (Throwable $e) {
        send_json(['status' => 'error', 'message' => 'Server error during login.'], 500);
    }
}

/* -------------------------
   Fallback: do local DB check (only if controller not available)
   ------------------------- */
if ($user === false && !function_exists('login_customer_ctr') && !function_exists('login_user')) {
    // Uncomment and update database credentials / table/column names for fallback usage.
    /*
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'your_database';

    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_errno) {
        send_json(['status' => 'error', 'message' => 'DB connection failed.'], 500);
    }

    // Example: customers table with columns id, email, password_hash, name, role
    $stmt = $conn->prepare('SELECT id, email, password_hash, name, role FROM customers WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {
            $user = [
                'id' => $row['id'],
                'email' => $row['email'],
                'name' => $row['name'],
                'role' => $row['role'] ?? 'customer'
            ];
        }
    }
    $stmt->close();
    $conn->close();
    */
}

/* -------------------------
   Handle auth result
   ------------------------- */
if ($user && is_array($user)) {
    $_SESSION['user_id']   = $user['id'] ?? null;
    $_SESSION['user_email']= $user['email'] ?? $email;
    $_SESSION['user_name'] = $user['name'] ?? ($user['first_name'] ?? 'Customer');
    $_SESSION['user_role'] = $user['role'] ?? 'customer';

    if (isset($user['phone'])) { $_SESSION['user_phone'] = $user['phone']; }
    if (isset($user['created_at'])) { $_SESSION['user_created_at'] = $user['created_at']; }

    session_regenerate_id(true);

    $redirectTo = '../index.php'; // change to your desired landing page
    redirect_or_json(true, 'Login successful.', $redirectTo, ['user' => [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'],
        'name' => $_SESSION['user_name'],
        'role' => $_SESSION['user_role']
    ]]);
} else {
    // Authentication failed
    redirect_or_json(false, 'Invalid email or password.', '../view/login.php');
}
 -->

 <?php
// login_customer_action.php
header('Content-Type: application/json');
session_start();

require_once '../controllers/customer_controller.php'; // adjust path if needed

function send_json($payload) {
    echo json_encode($payload);
    exit;
}

// Check if POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['status' => 'error', 'message' => 'Invalid request']);
}

// Grab inputs
$email    = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Validate
if (empty($email) || empty($password)) {
    send_json(['status' => 'error', 'message' => 'Email and password are required']);
}

// Call controller
$user = login_customer_ctr($email, $password);

if ($user === false) {
    send_json(['status' => 'error', 'message' => 'Invalid email or password']);
}


$_SESSION['user_id']    = $user['customer_id'];
$_SESSION['user_email'] = $user['customer_email'];
$_SESSION['user_name']  = $user['customer_name'];
$_SESSION['user_role']  = $user['user_role'];
$_SESSION['user_image'] = $user['customer_image'];

send_json([
    'status'  => 'success',
    'message' => 'Login successful',
    'user'    => [
        'id'    => $user['customer_id'],
        'name'  => $user['customer_name'],
        'email' => $user['customer_email'],
        'role'  => $user['user_role']
    ]
]);
