<?php
// update_category_action.php


require_once '../controllers/category_controller.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $newName = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';

    if ($id <= 0 || empty($newName)) {
        $_SESSION['error'] = 'Invalid category ID or name.';
        header('Location: ../admin/category.php?id=' . $id);
        exit();
    }

    // Check uniqueness (exclude current id)
    if (category_name_exists_ctr($newName, $id)) {
        $_SESSION['error'] = 'Category name already exists. Please choose another name.';
        header('Location: ../admin/category.php?id=' . $id);
        exit();
    }

    $result = edit_category_ctr($id, $newName);

    if ($result) {
        $_SESSION['success'] = 'Category updated successfully!';
        header('Location: ../admin/category.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to update category. Please try again.';
        header('Location: ../admin/category.php?id=' . $id);
        exit();
    }

} else {
    header('HTTP/1.1 405 Method Not Allowed');
    echo 'Method Not Allowed';
    exit();
}
?>
