<?php
session_start();
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die("Access denied.");
}
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_role = $_SESSION['admin_role'] ?? 'admin'; // Only set here!

// DB connection
$conn = new mysqli("localhost", "root", "", "ecommerce");
if ($conn->connect_error) die("DB error");

// Sidebar selection logic
$page = $_GET['page'] ?? 'dashboard';

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['order_status'];
    $allowed = ['received', 'in delivery', 'delivered'];
    if (in_array($new_status, $allowed)) {
        $stmt = $conn->prepare("UPDATE transactions SET order_status=? WHERE id=?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin.php?page=orders");
    exit();
}

// Pagination and filtering for users
$user_filter = '';
$user_params = [];
if ($page === 'users') {
    $where = [];
    if (!empty($_GET['userid'])) {
        $where[] = "id=?";
        $user_params[] = $_GET['userid'];
    }
    if (!empty($_GET['regdate'])) {
        $where[] = "DATE(created_at)=?";
        $user_params[] = $_GET['regdate'];
    }
    $where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";
    $limit = 20;
    $offset = max(0, intval($_GET['offset'] ?? 0));
    $sql = "SELECT id, name, email, address, city, phone, created_at FROM users $where_sql ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $stmt = $conn->prepare($sql);
    if ($user_params) {
        $types = str_repeat("s", count($user_params));
        $stmt->bind_param($types, ...$user_params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $users = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Pagination and filtering for orders
if ($page === 'orders') {
    $where = [];
    $order_params = [];
    if (!empty($_GET['userid'])) {
        $where[] = "t.user_id=?";
        $order_params[] = $_GET['userid'];
    }
    if (!empty($_GET['orderdate'])) {
        $where[] = "DATE(t.created_at)=?";
        $order_params[] = $_GET['orderdate'];
    }
    $where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";
    $limit = 20;
    $offset = max(0, intval($_GET['offset'] ?? 0));
    $sql = "SELECT t.id, t.product_name, t.product_color, t.product_quantity, t.total_price, t.order_status, t.created_at, u.name, u.email, u.address, u.city
            FROM transactions t JOIN users u ON t.user_id=u.id
            $where_sql
            ORDER BY t.created_at DESC LIMIT $limit OFFSET $offset";
    $stmt = $conn->prepare($sql);
    if ($order_params) {
        $types = str_repeat("s", count($order_params));
        $stmt->bind_param($types, ...$order_params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $orders = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// For charts: fetch data for dashboard
if ($page === 'dashboard') {
    // Date filter
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';
    $date_filter = '';
    if ($from && $to) {
        $date_filter = "WHERE created_at BETWEEN '$from 00:00:00' AND '$to 23:59:59'";
    } elseif ($from) {
        $date_filter = "WHERE created_at >= '$from 00:00:00'";
    } elseif ($to) {
        $date_filter = "WHERE created_at <= '$to 23:59:59'";
    }

    // Users per month (last 6 months or filtered)
    $user_stats = [];
    $user_sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt FROM users $date_filter GROUP BY ym ORDER BY ym DESC LIMIT 6";
    $res = $conn->query($user_sql);
    while ($row = $res->fetch_assoc()) $user_stats[] = $row;
    $user_stats = array_reverse($user_stats);

    // Orders per month (last 6 months or filtered)
    $order_stats = [];
    $order_sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt FROM transactions $date_filter GROUP BY ym ORDER BY ym DESC LIMIT 6";
    $res = $conn->query($order_sql);
    while ($row = $res->fetch_assoc()) $order_stats[] = $row;
    $order_stats = array_reverse($order_stats);

    // Order status breakdown (filtered)
    $status_stats = [];
    $status_sql = "SELECT order_status, COUNT(*) as cnt FROM transactions $date_filter GROUP BY order_status";
    $res = $conn->query($status_sql);
    while ($row = $res->fetch_assoc()) $status_stats[$row['order_status']] = $row['cnt'];

    // Total sales per month (last 12 months)
    $salesPerMonth = [];
    $res = $conn->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_price) as total
        FROM transactions
        WHERE order_status='delivered'
        GROUP BY month
        ORDER BY month DESC
        LIMIT 12
    ");
    while ($row = $res->fetch_assoc()) {
        $salesPerMonth[$row['month']] = (float)$row['total'];
    }
    $salesPerMonth = array_reverse($salesPerMonth, true);

    // Total sales per category
    $salesPerCategory = [];
    $res = $conn->query("
        SELECT p.category, SUM(t.product_quantity * t.total_price / t.product_quantity) as total
        FROM transactions t
        JOIN products p ON t.product_name = p.name
        WHERE t.order_status='delivered'
        GROUP BY p.category
    ");
    while ($row = $res->fetch_assoc()) {
        $salesPerCategory[$row['category']] = (float)$row['total'];
    }
}

// Admin Management Logic
if ($page === 'admins') {
    $admin_id = $_SESSION['admin_id'] ??
    $admin_role = $_SESSION['admin_role'] ?? 'admin';

    // Add admin
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];
        $role = $_POST['role'] ?? 'admin';
        $email = trim($_POST['email']);
        if (!$username || !$password) {
            $msg = ["Username and password required.", "red"];
        } elseif ($password !== $confirm) {
            $msg = ["Passwords do not match.", "red"];
        } else {
            $exists = $conn->query("SELECT id FROM admins WHERE username='". $conn->real_escape_string($username) ."'")->num_rows;
            if ($exists) {
                $msg = ["Username already exists.", "red"];
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO admins (username, password, role, status, email) VALUES (?, ?, ?, 'active', ?)");
                $stmt->bind_param("ssss", $username, $hash, $role, $email);
                $stmt->execute();
                // Log
                $conn->query("INSERT INTO admin_logs (admin_id, action, details) VALUES ($admin_id, 'add_admin', 'Added admin $username')");
                // Email notification (simple mail)
                @mail($email, "EcoNest Admin Account", "You have been added as an admin to EcoNest. Username: $username");
                $msg = ["Admin added!", "green"];
            }
        }
    }

    // Delete admin
    if (isset($_GET['delete_admin']) && is_numeric($_GET['delete_admin']) && $admin_role === 'superadmin') {
        $id = intval($_GET['delete_admin']);
        if ($id == $admin_id) {
            $msg = ["You cannot delete yourself.", "red"];
        } else {
            $conn->query("DELETE FROM admins WHERE id=$id");
            $conn->query("INSERT INTO admin_logs (admin_id, action, details) VALUES ($admin_id, 'delete_admin', 'Deleted admin ID $id')");
            $msg = ["Admin deleted!", "green"];
            // Optionally send email to deleted admin (if you want)
        }
    }

    // Enable/Disable admin
    if (isset($_GET['toggle_admin']) && is_numeric($_GET['toggle_admin']) && $admin_role === 'superadmin') {
        $id = intval($_GET['toggle_admin']);
        $admin = $conn->query("SELECT status FROM admins WHERE id=$id")->fetch_assoc();
        $new_status = ($admin['status'] === 'active') ? 'disabled' : 'active';
        $conn->query("UPDATE admins SET status='$new_status' WHERE id=$id");
        $conn->query("INSERT INTO admin_logs (admin_id, action, details) VALUES ($admin_id, 'toggle_admin', 'Set admin ID $id to $new_status')");
        $msg = ["Admin status updated!", "green"];
    }

    // Reset password for other admins (superadmin only)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_admin_reset']) && $admin_role === 'superadmin') {
        $reset_id = intval($_POST['reset_admin_id']);
        $new_pass = $_POST['new_admin_password'];
        $confirm_pass = $_POST['confirm_admin_password'];
        if ($new_pass && $new_pass === $confirm_pass) {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admins SET password=? WHERE id=?");
            $stmt->bind_param("si", $hash, $reset_id);
            $stmt->execute();
            $conn->query("INSERT INTO admin_logs (admin_id, action, details) VALUES ($admin_id, 'reset_password', 'Reset password for admin ID $reset_id')");
            $msg = ["Password reset!", "green"];
        } else {
            $msg = ["Passwords do not match.", "red"];
        }
    }

    // Edit admin username (require old password)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_admin_edit'])) {
        $edit_id = intval($_POST['edit_admin_id']);
        $new_username = trim($_POST['edit_admin_username']);
        $old_password = $_POST['old_password'] ?? '';
        $row = $conn->query("SELECT password_hash FROM admins WHERE id=$edit_id")->fetch_assoc();
        if (!$new_username) {
            $msg = ["Username required.", "red"];
        } elseif (!password_verify($old_password, $row['password_hash'])) {
            $msg = ["Old password incorrect.", "red"];
        } else {
            $stmt = $conn->prepare("UPDATE admins SET username=? WHERE id=?");
            $stmt->bind_param("si", $new_username, $edit_id);
            $stmt->execute();
            $conn->query("INSERT INTO admin_logs (admin_id, action, details) VALUES ($admin_id, 'edit_username', 'Changed username for admin ID $edit_id')");
            $msg = ["Admin username updated!", "green"];
        }
    }

    // Change your password
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
        $old = $_POST['old_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_new_password'];
        $res = $conn->query("SELECT password_hash FROM admins WHERE id=$admin_id");
        $row = $res->fetch_assoc();
        if (!password_verify($old, $row['password_hash'])) {
            $msg = ["Old password incorrect.", "red"];
        } elseif ($new !== $confirm) {
            $msg = ["New passwords do not match.", "red"];
        } elseif (strlen($new) < 5) {
            $msg = ["Password too short.", "red"];
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admins SET password_hash=? WHERE id=?");
            $stmt->bind_param("si", $hash, $admin_id);
            $stmt->execute();
            $conn->query("INSERT INTO admin_logs (admin_id, action, details) VALUES ($admin_id, 'change_password', 'Changed own password')");
            $msg = ["Password changed!", "green"];
        }
    }

    // List admins
    $result = $conn->query("SHOW COLUMNS FROM admins LIKE 'created_at'");
    $has_created_at = $result && $result->num_rows > 0;
    $admins = $conn->query(
        $has_created_at
        ? "SELECT id, username, role, status, created_at, email FROM admins ORDER BY id DESC"
        : "SELECT id, username, role, status, email FROM admins ORDER BY id DESC"
    );
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | EcoNest</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { background: #f4f4f4; margin:0; font-family: 'Segoe UI', Arial, sans-serif; }
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar {
            width: 220px;
            background: #2f6b29;
            color: #fff;
            display: flex;
            flex-direction: column;
            padding-top: 0;
            min-height: 100vh;
        }
        .sidebar .logo {
            font-size: 1.7em;
            font-weight: bold;
            color: #fff;
            letter-spacing: 1px;
            padding: 28px 0 18px 0;
            text-align: center;
            border-bottom: 1px solid #25611f;
        }
        .sidebar nav {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-top: 20px;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 16px 32px;
            font-size: 1.08em;
            border-left: 4px solid transparent;
            transition: background 0.2s, border 0.2s;
        }
        .sidebar a.active, .sidebar a:hover {
            background: #25611f;
            border-left: 4px solid #ffe082;
        }
        .sidebar .logout-link {
            margin-top: auto;
            background: #e74c3c;
            color: #fff;
            text-align: center;
            padding: 14px 0;
            font-weight: bold;
            border: none;
            border-radius: 0;
            cursor: pointer;
            text-decoration: none;
        }
        .main-content {
            flex: 1;
            padding: 0 0 40px 0;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            background: #fff;
            box-shadow: 0 2px 8px rgba(47,107,41,0.06);
            padding: 18px 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .topbar .admin-info {
            color: #2f6b29;
            font-weight: 500;
            font-size: 1.08em;
        }
        .admin-container {
            max-width: 1200px;
            margin: 30px auto 0 auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 30px 30px 30px 30px;
        }
        h2 { color: #2f6b29; margin-bottom: 18px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 10px 8px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
        }
        th {
            background: #e8f5e9;
            color: #2f6b29;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .status-received { color: #e67e22; font-weight:bold; }
        .status-in_delivery { color: #2980b9; font-weight:bold; }
        .status-delivered { color: #27ae60; font-weight:bold; }
        .update-btn {
            background: #2f6b29;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 6px 15px;
            cursor: pointer;
            font-size: 1em;
            transition: background 0.2s;
        }
        .update-btn:hover { background: #25611f; }
        .filter-form {
            margin-bottom: 18px;
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
        }
        .filter-form input, .filter-form select {
            padding: 7px 10px;
            border-radius: 5px;
            border: 1px solid #c8e6c9;
            font-size: 1em;
        }
        .filter-form button {
            background: #2f6b29;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 7px 18px;
            font-size: 1em;
            cursor: pointer;
        }
        @media (max-width: 900px) {
            .admin-container { padding: 10px 2vw; }
            table, th, td { font-size: 0.97em; }
            .sidebar { width: 100px; }
            .sidebar .logo { font-size: 1.1em; padding: 18px 0 10px 0; }
            .sidebar a { font-size: 0.97em; padding: 12px 10px; }
            .topbar { padding: 12px 10px; }
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <aside class="sidebar">
        <div class="logo">EcoNest <span style="color:#ffe082;">Admin</span></div>
        <nav>
            <a href="../modules/admin.php?page=dashboard" class="<?php if($page=='dashboard') echo 'active'; ?>">Dashboard</a>
            <a href="../modules/admin.php?page=users" class="<?php if($page=='users') echo 'active'; ?>">Users</a>
            <a href="../modules/admin.php?page=orders" class="<?php if($page=='orders') echo 'active'; ?>">Orders</a>
            <a href="../modules/admin.php?page=products" class="<?php if($page=='products') echo 'active'; ?>">Products</a>
            <a href="../modules/admin.php?page=admins" class="<?php if($page=='admins') echo 'active'; ?>">Admins</a>
            <a href="../modules/admin.php?page=admin_logs" class="<?php if($page=='admin_logs') echo 'active'; ?>">Admin Logs</a>
        </nav>
        <a href="../modules/logout.php" class="logout-link">Logout</a>
    </aside>
    <div class="main-content">
        <div class="topbar">
            <span style="font-size:1.2em;font-weight:bold;color:#2f6b29;">
                <?php echo ucfirst($page); ?>
            </span>
            <span class="admin-info">
                <?php echo htmlspecialchars($admin_username); ?>
            </span>
        </div>
        <div class="admin-container">
            <?php if ($page === 'dashboard'): ?>
                <h2>Welcome, <?php echo htmlspecialchars($admin_username); ?>!</h2>
                <form method="get" style="margin-bottom:22px;display:flex;gap:18px;align-items:center;">
                    <input type="hidden" name="page" value="dashboard">
                    <label><b>From:</b> <input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>"></label>
                    <label><b>To:</b> <input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>"></label>
                    <button type="submit" style="padding:7px 18px;background:#2f6b29;color:#fff;border:none;border-radius:5px;">Filter</button>
                    <?php if ($from || $to): ?>
                        <a href="../modules/admin.php?page=dashboard" style="margin-left:10px;color:#e74c3c;text-decoration:underline;">Clear</a>
                    <?php endif; ?>
                </form>

                <!-- 2x2 grid for small charts -->
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:30px;max-width:900px;margin:0 auto 40px auto;">
                    <div>
                        <div style="text-align:center;font-weight:bold;margin-bottom:8px;">User Registrations Per Month</div>
                        <canvas id="usersChart" height="180"></canvas>
                    </div>
                    <div>
                        <div style="text-align:center;font-weight:bold;margin-bottom:8px;">Orders Per Month</div>
                        <canvas id="ordersChart" height="180"></canvas>
                    </div>
                    <div>
                        <div style="text-align:center;font-weight:bold;margin-bottom:8px;">Order Status Breakdown</div>
                        <canvas id="statusChart" height="180"></canvas>
                    </div>
                    <div>
                        <div style="text-align:center;font-weight:bold;margin-bottom:8px;">Total Sales Per Category</div>
                        <canvas id="salesPerCategoryChart" height="180"></canvas>
                    </div>
                     <!-- Full-width sales per month chart -->
                <div style="max-width:900px;margin:0 auto 0 auto;">
                    <h3 style="text-align:center;">Total Sales Per Month</h3>
                    <canvas id="salesPerMonthChart" height="180"></canvas>
                </div>
                </div>

               

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                // Users per month
                new Chart(document.getElementById('usersChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_column($user_stats, 'ym')); ?>,
                        datasets: [{
                            label: 'Registrations',
                            data: <?php echo json_encode(array_column($user_stats, 'cnt')); ?>,
                            backgroundColor: '#2f6b29'
                        }]
                    },
                    options: {
                        plugins: {
                            title: { display: true, text: 'User Registrations Per Month' }
                        },
                        scales: {
                            x: { title: { display: true, text: 'Month' } },
                            y: { title: { display: true, text: 'Number of Users' }, beginAtZero: true }
                        }
                    }
                });
                // Orders per month
                new Chart(document.getElementById('ordersChart').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_column($order_stats, 'ym')); ?>,
                        datasets: [{
                            label: 'Orders',
                            data: <?php echo json_encode(array_column($order_stats, 'cnt')); ?>,
                            borderColor: '#2980b9',
                            backgroundColor: 'rgba(41,128,185,0.1)',
                            fill: true
                        }]
                    },
                    options: {
                        plugins: {
                            title: { display: true, text: 'Orders Per Month' }
                        },
                        scales: {
                            x: { title: { display: true, text: 'Month' } },
                            y: { title: { display: true, text: 'Number of Orders' }, beginAtZero: true }
                        }
                    }
                });
                // Order status breakdown
                new Chart(document.getElementById('statusChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode(array_keys($status_stats)); ?>,
                        datasets: [{
                            data: <?php echo json_encode(array_values($status_stats)); ?>,
                            backgroundColor: ['#e67e22','#2980b9','#27ae60']
                        }]
                    },
                    options: {
                        plugins: {
                            title: { display: true, text: 'Order Status Breakdown' },
                            legend: { display: true, position: 'bottom' }
                        }
                    }
                });
                // Sales per category
                new Chart(document.getElementById('salesPerCategoryChart').getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: <?php echo json_encode(array_keys($salesPerCategory)); ?>,
                        datasets: [{
                            label: 'Total Sales (KSh)',
                            data: <?php echo json_encode(array_values($salesPerCategory)); ?>,
                            backgroundColor: [
                                '#2f6b29','#5d8c56','#e74c3c','#2980b9','#f39c12',
                                '#8e44ad','#16a085','#d35400','#34495e','#7f8c8d'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: { display: true, text: 'Total Sales Per Category' }
                        }
                    }
                });
                // Sales per month (full width)
                new Chart(document.getElementById('salesPerMonthChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_keys($salesPerMonth)); ?>,
                        datasets: [{
                            label: 'Total Sales (KSh)',
                            data: <?php echo json_encode(array_values($salesPerMonth)); ?>,
                            backgroundColor: '#2f6b29'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: { display: true, text: 'Total Sales Per Month' },
                            legend: { display: false }
                        },
                        scales: {
                            x: { title: { display: true, text: 'Month' } },
                            y: { beginAtZero: true, title: { display: true, text: 'Total Sales (KSh)' } }
                        }
                    }
                });
                </script>
            <?php elseif ($page === 'users'): ?>
                <h2>Users</h2>
                <form class="filter-form" method="get" action="../modules/admin.php">
                    <input type="hidden" name="page" value="users">
                    <input type="text" name="userid" placeholder="User ID" value="<?php echo htmlspecialchars($_GET['userid'] ?? ''); ?>">
                    <input type="date" name="regdate" placeholder="Registration Date" value="<?php echo htmlspecialchars($_GET['regdate'] ?? ''); ?>">
                    <button type="submit">Filter</button>
                </form>
                <form method="POST" onsubmit="return confirm('Are you sure?');">
                    <div style="margin-bottom:10px;display:flex;gap:10px;align-items:center;">
                        <select name="bulk_action" required>
                            <option value="">Bulk Action</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                        <button type="submit" style="padding:7px 18px;background:#e74c3c;color:#fff;border:none;border-radius:5px;">Apply</button>
                    </div>
                    <table>
                        <tr>
                            <th><input type="checkbox" onclick="toggleAll(this, 'selected_users[]')"></th>
                            <th>ID</th><th>Name</th><th>Email</th><th>Address</th><th>City</th><th>Phone</th><th>Registered</th>
                        </tr>
                        <?php if (!empty($users)): foreach ($users as $u): ?>
                        <tr>
                            <td><input type="checkbox" name="selected_users[]" value="<?php echo $u['id']; ?>"></td>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo htmlspecialchars($u['address']); ?></td>
                            <td><?php echo htmlspecialchars($u['city']); ?></td>
                            <td><?php echo htmlspecialchars($u['phone']); ?></td>
                            <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="8" style="text-align:center;">No users found.</td></tr>
                        <?php endif; ?>
                    </table>
                </form>
            <?php elseif ($page === 'orders'): ?>
                <h2>Orders</h2>
                <form class="filter-form" method="get" action="../modules/admin.php">
                    <input type="hidden" name="page" value="orders">
                    <input type="text" name="userid" placeholder="User ID" value="<?php echo htmlspecialchars($_GET['userid'] ?? ''); ?>">
                    <input type="date" name="orderdate" placeholder="Order Date" value="<?php echo htmlspecialchars($_GET['orderdate'] ?? ''); ?>">
                    <button type="submit">Filter</button>
                </form>
                <form method="POST" onsubmit="return confirm('Are you sure?');">
                    <div style="margin-bottom:10px;display:flex;gap:10px;align-items:center;">
                        <select name="bulk_action" required>
                            <option value="">Bulk Action</option>
                            <option value="delete">Delete Selected</option>
                            <option value="mark_delivered">Mark as Delivered</option>
                        </select>
                        <button type="submit" style="padding:7px 18px;background:#2f6b29;color:#fff;border:none;border-radius:5px;">Apply</button>
                    </div>
                    <table>
                        <tr>
                            <th><input type="checkbox" onclick="toggleAll(this, 'selected_orders[]')"></th>
                            <th>ID</th><th>User</th><th>Email</th>
                            <th>Address</th><th>City</th>
                            <th>Product</th><th>Color</th><th>Qty</th><th>Total</th>
                            <th>Status</th><th>Date</th><th>Update Status</th>
                        </tr>
                        <?php if (!empty($orders)): foreach ($orders as $o): ?>
                        <tr>
                            <td><input type="checkbox" name="selected_orders[]" value="<?php echo $o['id']; ?>"></td>
                            <td><?php echo $o['id']; ?></td>
                            <td><?php echo htmlspecialchars($o['name']); ?></td>
                            <td><?php echo htmlspecialchars($o['email']); ?></td>
                            <td><?php echo htmlspecialchars($o['address']); ?></td>
                            <td><?php echo htmlspecialchars($o['city']); ?></td>
                            <td><?php echo htmlspecialchars($o['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($o['product_color']); ?></td>
                            <td><?php echo $o['product_quantity']; ?></td>
                            <td>KSh <?php echo $o['total_price']; ?></td>
                            <td class="status-<?php echo str_replace(' ', '_', $o['order_status']); ?>"><?php echo $o['order_status']; ?></td>
                            <td><?php echo $o['created_at']; ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                    <select name="order_status">
                                        <option value="received" <?php if($o['order_status']=='received') echo 'selected'; ?>>Received</option>
                                        <option value="in delivery" <?php if($o['order_status']=='in delivery') echo 'selected'; ?>>In Delivery</option>
                                        <option value="delivered" <?php if($o['order_status']=='delivered') echo 'selected'; ?>>Delivered</option>
                                    </select>
                                    <button type="submit" name="update_status" class="update-btn">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="13" style="text-align:center;">No orders found.</td></tr>
                        <?php endif; ?>
                    </table>
                </form>
            <?php elseif ($page === 'products'): ?>

<?php
// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $image = '';

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $img_name = time() . '_' . basename($_FILES['image']['name']);
        $target = __DIR__ . "../images" . $img_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image = $img_name;
        }
    }

    $stmt = $conn->prepare("INSERT INTO products (name, description, category, image, price, stock) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdi", $name, $description, $category, $image, $price, $stock);
    $stmt->execute();
    echo "<div class='msg' style='color:green;'>Product added!</div>";
}

// Handle Edit Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $image = $_POST['existing_image'];

    // Handle new image upload
    if (!empty($_FILES['image']['name'])) {
        $img_name = time() . '_' . basename($_FILES['image']['name']);
        $target = __DIR__ . "/../images/" . $img_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image = $img_name;
        }
    }

    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, category=?, image=?, price=?, stock=? WHERE id=?");
    $stmt->bind_param("ssssdis", $name, $description, $category, $image, $price, $stock, $id);
    $stmt->execute();
    echo "<div class='msg' style='color:green;'>Product updated!</div>";
}

// Handle Delete Product
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id=$id");
    echo "<div class='msg' style='color:red;'>Product deleted!</div>";
}

// Show Add/Edit Form
if (isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit')) {
    $edit = false;
    $product = ['id'=>'','name'=>'','description'=>'','category'=>'','image'=>'','price'=>'','stock'=>''];
    if ($_GET['action'] === 'edit' && isset($_GET['id'])) {
        $edit = true;
        $id = intval($_GET['id']);
        $res = $conn->query("SELECT * FROM products WHERE id=$id");
        $product = $res->fetch_assoc();
    }
    // Define categories
    $categories = [
        'bamboo' => 'Bamboo Products',
        'beeswax' => 'Beeswax Products',
        'reusable' => 'Reusable Products',
        'compostable' => 'Compostable Products',
        'upcycled' => 'Upcycled Products',
        'skincare' => 'Natural Skincare',
        'cleaning' => 'Eco-Friendly Cleaning',
        'fashion' => 'Sustainable Fashion',
        'gardening' => 'Gardening and Outdoor',
        'home' => 'Home and Kitchen'
    ];
?>
    <h2 style="margin-bottom:22px;"><?php echo $edit ? "Edit" : "Add"; ?> Product</h2>
    <form method="post" enctype="multipart/form-data" style="max-width:500px;display:flex;flex-direction:column;gap:18px;">
        <?php if ($edit): ?>
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($product['image']); ?>">
        <?php endif; ?>
        <label>Name:
            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required style="width:100%;padding:8px;">
        </label>
        <label>Description:
            <textarea name="description" required style="width:100%;padding:8px;"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </label>
        <label>Category:
            <select name="category" required style="width:100%;padding:8px;">
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $key => $label): ?>
                    <option value="<?php echo $key; ?>" <?php if($product['category']==$key) echo 'selected'; ?>>
                        <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Price (KSh):
            <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required style="width:100%;padding:8px;">
        </label>
        <label>Stock:
            <input type="number" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required style="width:100%;padding:8px;">
        </label>
        <label>Image:
            <input type="file" name="image" accept="image/*">
            <?php if ($edit && $product['image']): ?>
                <div style="margin-top:8px;"><img src="../images/<?php echo htmlspecialchars($product['image']); ?>" alt="" style="width:60px;height:60px;object-fit:cover;"></div>
            <?php endif; ?>
        </label>
        <div style="display:flex;gap:16px;align-items:center;margin-top:8px;">
            <button type="submit" name="<?php echo $edit ? 'edit_product' : 'add_product'; ?>" class="update-btn" style="text-decoration:none;"><?php echo $edit ? 'Update' : 'Add'; ?> Product</button>
            <a href="../modules/admin.php?page=products" style="background:#888;color:#fff;padding:8px 18px;border-radius:5px;text-decoration:none;display:inline-block;">Cancel</a>
        </div>
    </form>
    <hr style="margin:30px 0;">
<?php
}
// Product List
?>
    <h2 style="margin-bottom:18px;">Product List</h2>
    <a href="../modules/admin.php?page=products&action=add" style="background:#2f6b29;color:#fff;padding:8px 18px;border-radius:5px;text-decoration:none;display:inline-block;margin-bottom:18px;">+ Add Product</a>
    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Image</th><th>Actions</th>
        </tr>
        <?php
        $res = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
        while ($p = $res->fetch_assoc()):
        ?>
        <tr>
            <td><?php echo $p['id']; ?></td>
            <td><?php echo htmlspecialchars($p['name']); ?></td>
            <td><?php echo htmlspecialchars($categories[$p['category']] ?? $p['category']); ?></td>
            <td>KSh <?php echo $p['price']; ?></td>
            <td><?php echo $p['stock']; ?></td>
            <td>
                <?php if ($p['image']): ?>
                    <img src="../images/<?php echo htmlspecialchars($p['image']); ?>" alt="" style="width:40px;height:40px;object-fit:cover;">
                <?php endif; ?>
            </td>
            <td>
                <a href="../modules/admin.php?page=products&action=edit&id=<?php echo $p['id']; ?>" style="background:#2980b9;color:#fff;padding:6px 14px;border-radius:5px;text-decoration:none;display:inline-block;">Edit</a>
                <a href="../modules/admin.php?page=products&delete=<?php echo $p['id']; ?>" 
                   class="delete-link" 
                   data-id="<?php echo $p['id']; ?>"
                   style="background:#e74c3c;color:#fff;padding:6px 14px;border-radius:5px;text-decoration:none;display:inline-block;margin-left:8px;">
                   Delete
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php elseif ($page === 'admins'): ?>
    <h2>Admin Management</h2>
    <?php if (!empty($msg)): ?>
        <div class="msg" style="background:<?php echo $msg[1]==='green'?'#e8f5e9':'#fdecea'; ?>;color:<?php echo $msg[1]==='green'?'#2f6b29':'#e74c3c'; ?>;padding:12px 18px;border-radius:6px;margin-bottom:18px;text-align:center;font-weight:bold;" id="autoHideMsg">
            <?php echo htmlspecialchars($msg[0]); ?>
        </div>
    <?php endif; ?>

    <form method="POST" style="margin-bottom:22px;display:flex;gap:18px;align-items:center;flex-wrap:wrap;">
        <input type="text" name="username" placeholder="Username" required style="padding:7px;">
        <input type="email" name="email" placeholder="Email" required style="padding:7px;">
        <input type="password" name="password" placeholder="Password" required style="padding:7px;">
        <input type="password" name="confirm_password" placeholder="Confirm Password" required style="padding:7px;">
        <select name="role" required style="padding:7px;">
            <option value="admin">Admin</option>
            <option value="superadmin">Super Admin</option>
        </select>
        <button type="submit" name="add_admin" style="background:#2f6b29;color:#fff;border:none;padding:7px 18px;border-radius:5px;">Add Admin</button>
    </form>
    <?php
    // Edit username form
    if (isset($_GET['edit_admin']) && is_numeric($_GET['edit_admin'])) {
        $edit_id = intval($_GET['edit_admin']);
        $edit_admin = $conn->query("SELECT * FROM admins WHERE id=$edit_id")->fetch_assoc();
        if ($edit_admin):
    ?>
    <form method="POST" style="margin-bottom:18px;display:flex;gap:12px;align-items:center;">
        <input type="hidden" name="edit_admin_id" value="<?php echo $edit_admin['id']; ?>">
        <input type="text" name="edit_admin_username" value="<?php echo htmlspecialchars($edit_admin['username']); ?>" required style="padding:7px;">
        <input type="password" name="old_password" placeholder="Your Old Password" required style="padding:7px;">
        <button type="submit" name="save_admin_edit" style="background:#2f6b29;color:#fff;padding:7px 18px;border:none;border-radius:5px;">Save</button>
        <a href="../modules/admin.php?page=admins" style="margin-left:10px;">Cancel</a>
    </form>
    <?php endif; } ?>

    <?php
    // Reset password form
    if (isset($_GET['reset_admin']) && is_numeric($_GET['reset_admin']) && $admin_role === 'superadmin') {
        $reset_id = intval($_GET['reset_admin']);
    ?>
    <form method="POST" style="margin-bottom:18px;display:flex;gap:12px;align-items:center;">
        <input type="hidden" name="reset_admin_id" value="<?php echo $reset_id; ?>">
        <input type="password" name="new_admin_password" placeholder="New Password" required style="padding:7px;">
        <input type="password" name="confirm_admin_password" placeholder="Confirm Password" required style="padding:7px;">
        <button type="submit" name="save_admin_reset" style="background:#f39c12;color:#fff;padding:7px 18px;border:none;border-radius:5px;">Reset</button>
        <a href="../modules/admin.php?page=admins" style="margin-left:10px;">Cancel</a>
    </form>
    <?php } ?>

    <table>
        <tr>
            <th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><?php if ($has_created_at): ?><th>Created</th><?php endif; ?><th>Action</th>
        </tr>
        <?php while ($a = $admins->fetch_assoc()): ?>
        <tr>
            <td><?php echo $a['id']; ?></td>
            <td><?php echo htmlspecialchars($a['username']); ?></td>
            <td><?php echo htmlspecialchars($a['email']); ?></td>
            <td><?php echo htmlspecialchars($a['role']); ?></td>
            <td>
                <?php if ($a['status'] === 'active'): ?>
                    <span style="color:#27ae60;font-weight:bold;">Active</span>
                <?php else: ?>
                    <span style="color:#e74c3c;font-weight:bold;">Disabled</span>
                <?php endif; ?>
            </td>
            <?php if ($has_created_at): ?><td><?php echo $a['created_at']; ?></td><?php endif; ?>
            <td>
                <?php if ($a['id'] != $admin_id): ?>
                    <?php if ($admin_role === 'superadmin'): ?>
                        <a href="../modules/admin.php?page=admins&edit_admin=<?php echo $a['id']; ?>" style="background:#2980b9;color:#fff;padding:6px 14px;border-radius:5px;text-decoration:none;">Edit</a>
                        <a href="../modules/admin.php?page=admins&reset_admin=<?php echo $a['id']; ?>" style="background:#f39c12;color:#fff;padding:6px 14px;border-radius:5px;text-decoration:none;margin-left:5px;">Reset Password</a>
                        <a href="../modules/admin.php?page=admins&delete_admin=<?php echo $a['id']; ?>"
                           class="delete-admin-link"
                           style="background:#e74c3c;color:#fff;padding:6px 14px;border-radius:5px;text-decoration:none;margin-left:5px;">
                           Delete
                        </a>
                        <a href="../modules/admin.php?page=admins&toggle_admin=<?php echo $a['id']; ?>" style="background:#888;color:#fff;padding:6px 14px;border-radius:5px;text-decoration:none;margin-left:5px;">
                            <?php echo $a['status'] === 'active' ? 'Disable' : 'Enable'; ?>
                        </a>
                    <?php else: ?>
                        <span style="color:#888;">No permission</span>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color:#888;">(You)</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <h3>Change Your Password</h3>
    <form method="POST" style="max-width:400px;display:flex;flex-direction:column;gap:14px;">
        <input type="password" name="old_password" placeholder="Old Password" required style="padding:7px;">
        <input type="password" name="new_password" placeholder="New Password" required style="padding:7px;">
        <input type="password" name="confirm_new_password" placeholder="Confirm New Password" required style="padding:7px;">
        <button type="submit" name="change_password" style="background:#2980b9;color:#fff;border:none;padding:7px 18px;border-radius:5px;">Change Password</button>
    </form>
<?php elseif ($page === 'admin_logs' && $admin_role === 'superadmin'): ?>
    <h2>Admin Activity Log</h2>
    <table>
        <tr>
            <th>ID</th><th>Admin ID</th><th>Username/Email</th><th>action<th>Details</th><th>Date</th>
        </tr>
        <?php
        $logs = $conn->query("SELECT * FROM admin_logs ORDER BY created_at DESC LIMIT 100");
        while ($log = $logs->fetch_assoc()):
        ?>
        <tr>
            <td><?php echo $log['id']; ?></td>
            <td><?php echo $log['admin_id']; ?></td>
            <td>
                <?php
                // Get admin username
                $admin = $conn->query("SELECT username FROM admins WHERE id=" . $log['admin_id'])->fetch_assoc();
                echo htmlspecialchars($admin['username']);
                ?>
            <td><?php echo htmlspecialchars($log['action']); ?></td>
            <td><?php echo htmlspecialchars($log['details']); ?></td>
            <td><?php echo $log['created_at']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php endif; ?>
        </div>
    </div>
</div>
<div id="deleteModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);z-index:9999;justify-content:center;align-items:center;">
    <div style="background:#fff;padding:32px 28px 22px 28px;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,0.18);min-width:320px;max-width:90vw;text-align:center;position:relative;">
        <div style="font-size:1.2em;color:#e74c3c;font-weight:bold;margin-bottom:18px;">
            Are you sure you want to delete this product?
        </div>
        <div style="display:flex;justify-content:center;gap:18px;">
            <button id="confirmDeleteBtn" style="background:#e74c3c;color:#fff;padding:8px 22px;border:none;border-radius:5px;font-size:1em;cursor:pointer;">Delete</button>
            <button id="cancelDeleteBtn" style="background:#888;color:#fff;padding:8px 22px;border:none;border-radius:5px;font-size:1em;cursor:pointer;">Cancel</button>
        </div>
    </div>
</div>
<div id="deleteAdminModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);z-index:9999;justify-content:center;align-items:center;">
    <div style="background:#fff;padding:32px 28px 22px 28px;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,0.18);min-width:320px;max-width:90vw;text-align:center;position:relative;">
        <div style="font-size:1.2em;color:#e74c3c;font-weight:bold;margin-bottom:18px;">
            Are you sure you want to delete this admin?
        </div>
        <div style="display:flex;justify-content:center;gap:18px;">
            <button id="confirmDeleteAdminBtn" style="background:#e74c3c;color:#fff;padding:8px 22px;border:none;border-radius:5px;font-size:1em;cursor:pointer;">Delete</button>
            <button id="cancelDeleteAdminBtn" style="background:#888;color:#fff;padding:8px 22px;border:none;border-radius:5px;font-size:1em;cursor:pointer;">Cancel</button>
        </div>
    </div>
</div>
<script>
function toggleAll(source, name) {
    document.querySelectorAll('input[name="'+name+'"]').forEach(function(cb) {
        cb.checked = source.checked;
    });
}
// Hide action messages after 3 seconds
document.addEventListener('DOMContentLoaded', function() {
    var msg = document.querySelector('.msg');
    if (msg) {
        setTimeout(function() {
            msg.style.transition = "opacity 0.5s";
            msg.style.opacity = 0;
            setTimeout(function() { msg.style.display = "none"; }, 500);
        }, 3000);
    }
});
</script>
<script>
document.querySelectorAll('.delete-link').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        var modal = document.getElementById('deleteModal');
        modal.style.display = 'flex';
        // Store the delete URL
        modal.dataset.deleteUrl = this.href;
    });
});
document.getElementById('confirmDeleteBtn').onclick = function() {
    var modal = document.getElementById('deleteModal');
    window.location.href = modal.dataset.deleteUrl;
};
document.getElementById('cancelDeleteBtn').onclick = function() {
    document.getElementById('deleteModal').style.display = 'none';
};
// Optional: close modal on outside click
document.getElementById('deleteModal').onclick = function(e) {
    if (e.target === this) this.style.display = 'none';
};
</script>
<script>
document.querySelectorAll('.delete-admin-link').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        var modal = document.getElementById('deleteAdminModal');
        modal.style.display = 'flex';
        modal.dataset.deleteUrl = this.href;
    });
});
document.getElementById('confirmDeleteAdminBtn').onclick = function() {
    var modal = document.getElementById('deleteAdminModal');
    window.location.href = modal.dataset.deleteUrl;
};
document.getElementById('cancelDeleteAdminBtn').onclick = function() {
    document.getElementById('deleteAdminModal').style.display = 'none';
};
document.getElementById('deleteAdminModal').onclick = function(e) {
    if (e.target === this) this.style.display = 'none';
};
</script>
<script>
const salesPerMonthLabels = <?php echo json_encode(array_keys($salesPerMonth)); ?>;
const salesPerMonthData = <?php echo json_encode(array_values($salesPerMonth)); ?>;
const salesPerCategoryLabels = <?php echo json_encode(array_keys($salesPerCategory)); ?>;
const salesPerCategoryData = <?php echo json_encode(array_values($salesPerCategory)); ?>;

// Bar chart: Sales per month
new Chart(document.getElementById('salesPerMonthChart'), {
    type: 'bar',
    data: {
        labels: salesPerMonthLabels,
        datasets: [{
            label: 'Total Sales (KSh)',
            data: salesPerMonthData,
            backgroundColor: '#2f6b29'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Pie chart: Sales per category
new Chart(document.getElementById('salesPerCategoryChart'), {
    type: 'pie',
    data: {
        labels: salesPerCategoryLabels,
        datasets: [{
            label: 'Total Sales (KSh)',
            data: salesPerCategoryData,
            backgroundColor: [
                '#2f6b29','#5d8c56','#e74c3c','#2980b9','#f39c12',
                '#8e44ad','#16a085','#d35400','#34495e','#7f8c8d'
            ]
        }]
    },
    options: {
        responsive: true
    }
});
</script>
</body>
</html>