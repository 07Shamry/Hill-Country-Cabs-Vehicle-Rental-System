<?php
session_start();
include '../includes/db_connect.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Default Filters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$category_filter = $_GET['category'] ?? 'all';

// Build the Category SQL condition dynamically
$cat_condition = "";
if ($category_filter !== 'all') {
    $cat_condition = " AND c.category_name = '$category_filter'";
}

// 1. Fetch Revenue Summary (Updated with JOINs for category filtering)
$query = "SELECT 
            COUNT(b.booking_id) as total_bookings,
            SUM(b.total_price) as total_revenue,
            AVG(b.total_price) as avg_per_booking
          FROM bookings b
          JOIN vehicles v ON b.vehicle_id = v.vehicle_id
          JOIN categories c ON v.category_id = c.category_id
          WHERE b.booking_status = 'Completed' 
          AND (b.end_date BETWEEN '$start_date' AND '$end_date')
          $cat_condition";

$summary_res = $conn->query($query);
$summary = $summary_res->fetch_assoc();

// 2. Fetch Revenue by Vehicle Category
$category_query = "SELECT c.category_name, COUNT(b.booking_id) as booking_count, SUM(b.total_price) as category_revenue 
                   FROM bookings b
                   JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                   JOIN categories c ON v.category_id = c.category_id
                   WHERE b.booking_status = 'Completed' 
                   AND (b.end_date BETWEEN '$start_date' AND '$end_date')
                   $cat_condition
                   GROUP BY c.category_name
                   ORDER BY category_revenue DESC";
$category_res = $conn->query($category_query);

// Prepare data for Chart.js
$cat_names = [];
$cat_revenues = [];
while($cat_row = $category_res->fetch_assoc()) {
    $cat_names[] = $cat_row['category_name'];
    $cat_revenues[] = $cat_row['category_revenue'];
}
$category_res->data_seek(0); // Reset for table

// 3. Fetch Detailed Transaction List
$details_query = "SELECT b.booking_id, b.end_date, b.total_price, u.full_name, v.brand, v.model, c.category_name 
                  FROM bookings b
                  JOIN users u ON b.user_id = u.user_id
                  JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                  JOIN categories c ON v.category_id = c.category_id
                  WHERE b.booking_status = 'Completed' 
                  AND (b.end_date BETWEEN '$start_date' AND '$end_date')
                  $cat_condition
                  ORDER BY b.end_date DESC";
$details = $conn->query($details_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Revenue Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media print {
            .btn, form, .navbar { display: none !important; }
            .card { border: 1px solid #ddd !important; box-shadow: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">&larr; Back to Dashboard</a>
    </div>
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fa-solid fa-chart-line text-success"></i> Financial Revenue Report</h2>
        <button onclick="window.print()" class="btn btn-outline-dark"><i class="fa-solid fa-print"></i> Print Report</button>
    </div>

    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Vehicle Type</label>
                    <select name="category" class="form-select">
                        <option value="all" <?php echo ($category_filter == 'all') ? 'selected' : ''; ?>>All Vehicles</option>
                        <option value="Car" <?php echo ($category_filter == 'Car') ? 'selected' : ''; ?>>Cars</option>
                        <option value="Van" <?php echo ($category_filter == 'Van') ? 'selected' : ''; ?>>Vans</option>
                        <option value="Motorbike" <?php echo ($category_filter == 'Motorbike') ? 'selected' : ''; ?>>Bikes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter Data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white border-0 shadow">
                <div class="card-body text-center">
                    <h6 class="text-uppercase small">Total Revenue</h6>
                    <h2 class="fw-bold mb-0">$<?php echo number_format($summary['total_revenue'] ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white border-0 shadow">
                <div class="card-body text-center">
                    <h6 class="text-uppercase small">Completed Bookings</h6>
                    <h2 class="fw-bold mb-0"><?php echo $summary['total_bookings']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-dark text-white border-0 shadow">
                <div class="card-body text-center">
                    <h6 class="text-uppercase small">Avg. Ticket Size</h6>
                    <h2 class="fw-bold mb-0">$<?php echo number_format($summary['avg_per_booking'] ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">Revenue Distribution</div>
                <div class="card-body d-flex justify-content-center">
                    <div style="width: 300px; height: 300px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">Category Stats</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th>Bookings</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($category_res->num_rows > 0): ?>
                                <?php while($cat = $category_res->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $cat['category_name']; ?></td>
                                    <td><?php echo $cat['booking_count']; ?></td>
                                    <td class="text-end text-success fw-bold">$<?php echo number_format($cat['category_revenue'], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center">No data for this period.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-white fw-bold">Transaction Details</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Return Date</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Type</th>
                        <th class="text-end">Amount Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($details->num_rows > 0): ?>
                        <?php while($row = $details->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['booking_id']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['end_date'])); ?></td>
                            <td><?php echo $row['full_name']; ?></td>
                            <td><?php echo $row['brand'] . " " . $row['model']; ?></td>
                            <td><span class="badge bg-secondary"><?php echo $row['category_name']; ?></span></td>
                            <td class="text-end fw-bold text-success">$<?php echo number_format($row['total_price'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4">No revenue recorded for this period.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const chartLabels = <?php echo json_encode($cat_names); ?>;
    const chartData = <?php echo json_encode($cat_revenues); ?>;

    if(chartLabels.length > 0) {
        const ctx = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartLabels,
                datasets: [{
                    data: chartData,
                    backgroundColor: [
                        '#0d6efd', // Blue for Cars
                        '#198754', // Green for Vans
                        '#ffc107', // Yellow for Bikes
                        '#dc3545', // Red
                        '#6f42c1'  // Purple
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
</script>
</body>
</html>