<?php


header('Content-Type: application/json; charset=utf-8');

// TEMP: surface PHP errors while debugging
ini_set('display_errors','1'); error_reporting(E_ALL);

try {
    // Adjust paths to your structure:
    // root/
    //   Actions/register_customer_action.php   <-- this file
    //   Controllers/user_controller.php
    //   Classes/user_class.php
    //   db_class.php, core.php, etc.
    require_once '../controllers/user_controller.php';

    // We expect JSON because register.js sends contentType: 'application/json'
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['status'=>'error','message'=>'Invalid JSON payload']); exit;
    }

    // Call the controller wrapper that accepts an array (we set this up earlier)
    $res = register_customer_ctr($data);

    // HTTP code based on result
    $code = ($res['status'] ?? '') === 'success' ? 200 : 422;
    http_response_code($code);
    echo json_encode($res);
} catch (Throwable $e) {
    // While debugging, include message; in production, log and return generic
    http_response_code(500);
    echo json_encode([
        'status'=>'error',
        'message'=>'Server error: ' . $e->getMessage()
    ]);
}