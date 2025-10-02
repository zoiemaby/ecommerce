<?php
require_once '../settings/core.php';
ensure_session();
if (!isLoggedIn()) {
  redirect('../view/login.php');
}
if (!isAdmin()) {
  redirect('../view/login.php');
}
require_once '../controllers/category_controller.php';
// Handle create, update, delete actions
// ...existing code...
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard , Create Category</title>

    <!-- Fonts & icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <script src="../assets/js/category.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet" />

    <style>
      :root{
        --primary: hsl(158,82%,25%);
        --white: #ffffff;
        --muted: #6b7280;
        --card-shadow: 0 8px 20px rgba(0,0,0,0.08);
        --radius: 12px;
        --max-width: 1100px;
        --gap: 24px;
      }

      /* reset */
      *{box-sizing:border-box;margin:0;padding:0}
      html,body{height:100%}
      body{
        font-family: "Montserrat", "Poppins", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
        background: linear-gradient(180deg, #f6fbfa 0%, #fbfefe 100%);
        color:#111827;
        -webkit-font-smoothing:antialiased;
        -moz-osx-font-smoothing:grayscale;
        padding: 24px;
        display: flex;
        justify-content: center;
      }

      /* app container */
      .app {
        width: 100%;
        max-width: var(--max-width);
      }

      /* header */
      .topbar {
        display:flex;
        align-items:center;
        gap:12px;
        margin-bottom: 22px;
      }
      .back-btn{
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 8px;
        border-radius: 8px;
        display: inline-flex;
        align-items:center;
        justify-content:center;
        color: var(--primary);
      }
      .page-title {
        flex:1;
        text-align:center;
      }
      .page-title h1{
        font-size: 20px;
        color: var(--primary);
        margin-bottom: 4px;
      }
      .page-sub {
        font-size:13px;
        color:var(--muted);
      }
      .profile-btn{
        background: none;
        border: 1px solid rgba(17,24,39,0.06);
        padding: 6px 8px;
        border-radius: 8px;
        cursor:pointer;
      }

      /* main layout: side-by-side on desktop, stacked on mobile */
      .cards {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--gap);
        align-items:start;
      }

      /* make the left (form) slightly wider visually */
      .cards > .left { min-width: 0; }
      .cards > .right { min-width: 0; }

      /* card common */
      .card {
        background: var(--white);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        padding: 28px;
        overflow: visible;
      }

      /* collection form */
      .collection-card {
        display: flex;
        flex-direction: column;
        gap: 18px;
      }

      .collection-card h2{
        color: var(--primary);
        font-size: 18px;
        margin-bottom: 4px;
      }
      .collection-card p.lead {
        font-size: 13px;
        color: var(--muted);
      }

      .collection-form{
        display:flex;
        flex-direction:column;
        gap:14px;
        width:100%;
      }

      .collection-input{
        padding:14px 16px;
        border-radius:8px;
        border:1px solid rgba(17,24,39,0.08);
        font-size:15px;
        background:#fff;
      }

      .create-btn{
        padding:14px 18px;
        border-radius:10px;
        border:none;
        cursor:pointer;
        font-weight:600;
        font-size:15px;
        background: linear-gradient(180deg, var(--primary), color-mix(in srgb, var(--primary) 85%, black 10%));
        color: var(--white);
        box-shadow: 0 6px 16px rgba(21,128,120,0.18);
        transition: transform .12s ease, box-shadow .12s ease;
      }
      .create-btn:hover{ transform: translateY(-3px); box-shadow: 0 10px 26px rgba(21,128,120,0.14) }

      .error-message{
        background: rgba(255,0,0,0.06);
        border: 1px solid rgba(255,0,0,0.12);
        color: #991b1b;
        padding:10px 12px;
        border-radius:8px;
        font-size:13px;
      }

      /* manage users */
      .manage-card h2{
        color: var(--primary);
        font-size:18px;
      }

      .user-list {
        margin-top:12px;
        display:flex;
        flex-direction:column;
        gap:12px;
      }

      .user-item{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        padding:14px;
        border-radius:10px;
        border:1px solid rgba(17,24,39,0.04);
        background: linear-gradient(180deg, #ffffff 0%, #fbfefe 100%);
      }

      .user-details p{ font-size:14px; color:#111827; margin-bottom:6px }
      .user-details p.small{ color:var(--muted); font-size:13px; margin:0 }

      .user-actions{
        display:flex;
        gap:8px;
      }

      .action-button {
        padding:8px 12px;
        font-size:13px;
        border-radius:8px;
        border:none;
        cursor:pointer;
        font-weight:600;
        color:var(--white);
        background: var(--primary);
        transition: transform .12s ease;
      }
      .action-button.secondary {
        background: transparent;
        color: var(--primary);
        border: 1px solid rgba(17,24,39,0.06);
      }
      .action-button:hover{ transform: translateY(-3px) }

      /* responsive */
      @media (max-width: 920px){
        .cards{ grid-template-columns: 1fr; }
        .page-title { text-align:left }
      }

      @media (max-width: 420px){
        body { padding: 12px; }
        .card { padding: 18px; }
        .collection-input, .create-btn { font-size:14px; padding:12px }
      }
    </style>
  </head>

  <body>
    <div class="app">
      <!-- Top bar -->
      <div class="topbar">
        <a href="index.php" class="back-btn" aria-label="Back to dashboard">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 19l-7-7 7-7" />
          </svg>
        </a>

        <div class="page-title">
          <h1>Create New Category</h1>
          <div class="page-sub">Quickly add a new Category — it will appear in the category list.</div>
        </div>

        <div>
          <button class="profile-btn" aria-label="Profile">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
              stroke-linecap="round" stroke-linejoin="round" style="color:var(--primary)">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
          </button>
        </div>
      </div>

      <!-- Main cards container -->
      <div class="cards">
        <!-- Left: Collection form (fills more visually) -->
        <div class="left card collection-card">
          <h2><i class="fa-solid fa-layer-group" style="color:var(--primary); margin-right:8px"></i> Create New Category</h2>
          <p class="lead">Give your Category a name. Keep it concise and descriptive so users can easily find it.</p>

          <form action="" method="post" class="collection-form" novalidate>
            <!-- Server-side error area (PHP rendered) -->

            <?php
            // Handle create
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'])) {
                $name = trim($_POST['category_name']);
                $errors = [];
                if ($name === '') {
                    $errors[] = 'Category name required.';
                } elseif (category_name_exists_ctr($name)) {
                    $errors[] = 'Category name must be unique.';
                }
                if (empty($errors)) {
                    $result = add_category_ctr($name);
                    if ($result === false) {
                        $errors[] = 'Failed to add category. Check database connection, table structure, and error logs.';
                        echo '<div class="error-message">'.implode('<br>', array_map('htmlspecialchars', $errors)).'</div>';
                    } else {
                        echo '<script>window.location.reload();</script>';
                    }
                } else {
                    echo '<div class="error-message">'.implode('<br>', array_map('htmlspecialchars', $errors)).'</div>';
                }
            }
            ?>

            <input
              type="text"
              name="category_name"
              placeholder="Enter Category Name"
              required
              class="collection-input"
              aria-label="Collection name"
            />

            <button type="submit" class="create-btn">Create Category</button>
          </form>

          <!-- optional helpful note -->
          <div style="margin-top: 8px; color:var(--muted); font-size:13px">
            Tip: category names are visible to users — avoid using personal data or special characters.
          </div>
        </div>

        <!-- Right: Category List -->
        <div class="right card manage-card">
          <h2><i class="fa-solid fa-users" style="color:var(--primary); margin-right:8px"></i> Category List</h2>
          
          <div class="user-list">
            <?php
      $categories = list_categories_ctr();
      foreach ($categories as $cat) {
        echo '<div class="user-item">';
        echo '<div class="user-details">';
        echo '<p><strong>ID:</strong> '.htmlspecialchars($cat['cat_id']).'</p>';
        echo '<p class="small"><strong>Name:</strong> '.htmlspecialchars($cat['cat_name']).'</p>';
        echo '</div>';
        echo '<div class="user-actions">';
        echo '<form method="post" style="display:inline;"><input type="hidden" name="delete_id" value="'.htmlspecialchars($cat['cat_id']).'"><button class="action-button secondary" type="submit" title="Delete">Delete</button></form>';
        echo '<form method="post" style="display:inline;"><input type="hidden" name="update_id" value="'.htmlspecialchars($cat['cat_id']).'"><input type="text" name="update_name" value="'.htmlspecialchars($cat['cat_name']).'" required><button class="action-button" type="submit" title="Update">Update</button></form>';
        echo '</div>';
        echo '</div>';
      }
      // Handle delete
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
        delete_category_ctr((int)$_POST['delete_id']);
        echo '<script>window.location.reload();</script>';
      }
      // Handle update
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'], $_POST['update_name'])) {
        edit_category_ctr((int)$_POST['update_id'], $_POST['update_name']);
        echo '<script>window.location.reload();</script>';
      }
            ?>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
