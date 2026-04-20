<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: ../admin_login.php"); exit(); }

// 1. Fetch Stats
$total_vehicles = $conn->query("SELECT count(*) as c FROM vehicles")->fetch_assoc()['c'];
$total_bookings = $conn->query("SELECT count(*) as c FROM bookings")->fetch_assoc()['c'];
$pending_bookings = $conn->query("SELECT count(*) as c FROM bookings WHERE booking_status='Pending'")->fetch_assoc()['c'];
$revenue = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE booking_status = 'Completed'")->fetch_assoc()['total'];
$overdue_count = $conn->query("SELECT count(*) as c FROM bookings WHERE end_date < CURDATE() AND booking_status='Confirmed'")->fetch_assoc()['c'];

// 2. Chart Data
$chart_sql = "SELECT vehicles.model, COUNT(bookings.booking_id) as count 
              FROM bookings 
              JOIN vehicles ON bookings.vehicle_id = vehicles.vehicle_id 
              GROUP BY bookings.vehicle_id 
              ORDER BY count DESC LIMIT 5";
$chart_res = $conn->query($chart_sql);
$models = []; $counts = [];
while($row = $chart_res->fetch_assoc()) { $models[] = $row['model']; $counts[] = $row['count']; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card-stat { transition: 0.3s; color: white; border: none; }
        .card-stat:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-4">
    <a class="navbar-brand" href="#">Admin Panel</a>
    <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
</nav>

<div class="container mt-4">
    <h2 class="mb-4">Dashboard Overview</h2>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-stat bg-success p-3">
                <h3>$<?php echo number_format($revenue ?? 0, 2); ?></h3>
                <span>Total Revenue</span>
                <i class="fa-solid fa-sack-dollar fa-2x float-end opacity-50"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat bg-warning p-3 text-dark">
                <h3><?php echo $pending_bookings; ?></h3>
                <span>Pending Approvals</span>
                <i class="fa-solid fa-clock fa-2x float-end opacity-50"></i>
                <a href="manage_bookings.php" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat bg-primary p-3">
                <h3><?php echo $total_bookings; ?></h3>
                <span>Total Bookings</span>
                <i class="fa-solid fa-calendar-check fa-2x float-end opacity-50"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat <?php echo ($overdue_count>0)?'bg-danger':'bg-secondary'; ?> p-3">
                <h3><?php echo $overdue_count; ?></h3>
                <span>Overdue Vehicles</span>
                <i class="fa-solid fa-triangle-exclamation fa-2x float-end opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow border-0 p-4">
                <h5>Most Popular Vehicles</h5>
                <canvas id="vehicleChart"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow border-0 p-4">
                <h5>Quick Actions</h5>
                <div class="d-grid gap-2 mt-3">
                    <a href="manage_vehicles.php" class="btn btn-outline-dark">
                        <i class="fa-solid fa-car"></i> Manage Fleet
                    </a>
                    <a href="manage_bookings.php" class="btn btn-outline-dark">
                        <i class="fa-solid fa-list-check"></i> Manage Bookings
                    </a>
                    <a href="revenue_report.php" class="btn btn-outline-dark">
                        <i class="fa-solid fa-chart-line"></i> Detailed Revenue Report
                    </a>
                    <a href="../index.php" target="_blank" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-globe"></i> View Website
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    new Chart(document.getElementById('vehicleChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($models); ?>,
            datasets: [{ label: 'Bookings', data: <?php echo json_encode($counts); ?>, backgroundColor: '#0d6efd' }]
        }
    });
</script>

</body>
</html>