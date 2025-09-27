<?php
// delete_category_action.php

session_start();
require_once '../controllers/category_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

    if ($category_id <= 0) {
        $_SESSION['Category_Error'] = "Invalid category ID.";
        header("Location: ../admin/category.php");
        exit;
    }

    $deleted = delete_category_ctr($category_id);

    if ($deleted) {
        $_SESSION['Category_Success'] = "Category deleted successfully.";
    } else {
        $_SESSION['Category_Error'] = "Failed to delete category. Try again.";
    }

    header("Location: ../admin/category.php");
    exit;
} else {
    $_SESSION['Category_Error'] = "Invalid request method.";
    header("Location: ../admin/category.php");
    exit;
}
