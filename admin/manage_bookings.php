<?php
session_start();
include '../includes/db_connect.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// --- ACTION 1: APPROVE BOOKING (With Driver Assignment) ---
if (isset($_POST['approve_booking'])) {
    $booking_id = $_POST['booking_id'];
    $driver_name = $_POST['driver_name'] ?? NULL; // "John Doe"
    $driver_phone = $_POST['driver_phone'] ?? NULL; // "077-1234567"
    
    // Update booking status and driver info
    $stmt = $conn->prepare("UPDATE bookings SET booking_status='Confirmed', driver_name=?, driver_phone=? WHERE booking_id=?");
    $stmt->bind_param("ssi", $driver_name, $driver_phone, $booking_id);
    $stmt->execute();
    
    header("Location: manage_bookings.php?msg=approved");
}

// --- ACTION 2: REJECT BOOKING ---
if (isset($_POST['reject_booking'])) {
    $booking_id = $_POST['booking_id'];
    $vehicle_id = $_POST['vehicle_id'];
    
    // 1. Mark Booking as Cancelled
    $conn->query("UPDATE bookings SET booking_status='Cancelled' WHERE booking_id=$booking_id");
    
    // 2. Free up the vehicle immediately
    $conn->query("UPDATE vehicles SET status='Available' WHERE vehicle_id=$vehicle_id");
    
    header("Location: manage_bookings.php?msg=rejected");
}

// --- ACTION 3: MARK AS RETURNED (COMPLETE) ---
if (isset($_POST['complete_booking'])) {
    $booking_id = $_POST['booking_id'];
    $vehicle_id = $_POST['vehicle_id'];
    
    // 1. Mark as Completed
    $conn->query("UPDATE bookings SET booking_status='Completed' WHERE booking_id=$booking_id");
    
    // 2. Mark Vehicle as Available
    $conn->query("UPDATE vehicles SET status='Available' WHERE vehicle_id=$vehicle_id");
    
    header("Location: manage_bookings.php?msg=completed");
}

// FETCH BOOKINGS
$sql = "SELECT bookings.*, users.full_name, users.phone_number, vehicles.brand, vehicles.model, vehicles.license_plate 
        FROM bookings 
        JOIN users ON bookings.user_id = users.user_id 
        JOIN vehicles ON bookings.vehicle_id = vehicles.vehicle_id 
        ORDER BY bookings.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">&larr; Back to Dashboard</a>
    </div>
</nav>

<div class="container-fluid mt-4">
    <h2 class="mb-4">Booking Management</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Vehicle</th>
                    <th>Schedule | Est. Km </th>
                    <th>Logistics (Time/Loc)</th> <th>Driver</th>
                    <th>Status
                    <th>Actions</th>
                    <th>..</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php 
                        // Logic for Overdue Highlighting
                        $is_overdue = ($row['end_date'] < date('Y-m-d') && $row['booking_status'] == 'Confirmed');
                        $row_class = $is_overdue ? 'table-danger border-danger' : '';
                    ?>
                    
                    <tr class="<?php echo $row_class; ?>">
                        <td>#<?php echo $row['booking_id']; ?></td>
                        
                        <td>
                            <strong><?php echo $row['full_name']; ?></strong><br>
                            <small><?php echo $row['phone_number']; ?></small>
                        </td>
                        
                        <td>
                            <?php echo $row['brand'] . " " . $row['model']; ?><br>
                            <span class="badge bg-secondary"><?php echo $row['license_plate']; ?></span>
                        </td>
                        
                        <td>
                            From: <?php echo $row['start_date']; ?><br>
                            To: <?php echo $row['end_date']; ?><br>
                            <span class="badge bg-info text-dark">Est: <?php echo $row['estimated_km']; ?> km</span>
                            <?php if($is_overdue) echo '<br><span class="badge bg-danger">OVERDUE!</span>'; ?>
                        </td>

                        <td style="font-size: 0.9em;">
                            <strong>Pickup:</strong> <?php echo date('h:i A', strtotime($row['pickup_time'])); ?><br>
                            <span class="text-muted"><?php echo $row['pickup_location']; ?></span>
                            <hr class="my-1">
                            <strong>Dropoff:</strong> <?php echo date('h:i A', strtotime($row['dropoff_time'])); ?><br>
                            <span class="text-muted"><?php echo $row['dropoff_location']; ?></span>
                        </td>

                        <td>
                            <?php if ($row['is_with_driver']): ?>
                                <span class="badge bg-info text-dark">Required</span>
                                <?php if($row['driver_name']): ?>
                                    <br><small>Assigned: <?php echo $row['driver_name']; ?></small>
                                <?php else: ?>
                                    <br><small class="text-danger">Not Assigned</small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Self Drive</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <span class="badge bg-<?php 
                                echo match($row['booking_status']) {
                                    'Pending' => 'warning text-dark',
                                    'Confirmed' => 'primary',
                                    'Completed' => 'success',
                                    'Cancelled' => 'danger',
                                    default => 'secondary'
                                };
                            ?>">
                                <?php echo $row['booking_status']; ?>
                            </span>
                        </td>

                        <td>
                            <?php if ($row['booking_status'] == 'Pending'): ?>
                                <button class="btn btn-success btn-sm w-100 mb-1" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo $row['booking_id']; ?>">
                                    <i class="fa-solid fa-check"></i> Approve
                                </button>

                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                    <input type="hidden" name="vehicle_id" value="<?php echo $row['vehicle_id']; ?>">
                                    <button type="submit" name="reject_booking" class="btn btn-danger btn-sm w-100" onclick="return confirm('Reject this booking?');">
                                        <i class="fa-solid fa-xmark"></i> Reject
                                    </button>
                                </form>

                            <?php elseif ($row['booking_status'] == 'Confirmed'): ?>
                                <a href="return_vehicle.php?booking_id=<?php echo $row['booking_id']; ?>" class="btn btn-primary btn-sm w-100">
                                    <i class="fa-solid fa-rotate-left"></i> Process Return
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Closed</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <div class="modal fade" id="approveModal<?php echo $row['booking_id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title">Approve Booking #<?php echo $row['booking_id']; ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                        
                                        <p><strong>Customer:</strong> <?php echo $row['full_name']; ?></p>
                                        <p><strong>Vehicle:</strong> <?php echo $row['brand'] . " " . $row['model']; ?></p>

                                        <?php if ($row['is_with_driver']): ?>
                                            <div class="alert alert-warning">
                                                <i class="fa-solid fa-user-tie"></i> 
                                                <strong>Driver Requested!</strong><br>
                                                You must assign a driver to approve this booking.
                                            </div>
                                            <div class="mb-3">
                                                <label>Driver Name</label>
                                                <input type="text" name="driver_name" class="form-control" required placeholder="e.g. John Doe">
                                            </div>
                                            <div class="mb-3">
                                                <label>Driver Phone</label>
                                                <input type="text" name="driver_phone" class="form-control" required placeholder="e.g. 077-1234567">
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info">
                                                Customer selected <strong>Self Drive</strong>. No driver needed.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" name="approve_booking" class="btn btn-success">Confirm Approval</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>