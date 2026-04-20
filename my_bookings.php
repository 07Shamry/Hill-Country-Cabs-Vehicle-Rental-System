<?php
include 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

// Fetch Booking History
$sql = "SELECT bookings.*, vehicles.brand, vehicles.model, vehicles.image_url 
        FROM bookings 
        JOIN vehicles ON bookings.vehicle_id = vehicles.vehicle_id 
        WHERE bookings.user_id = $user_id 
        ORDER BY bookings.created_at DESC";
$result = $conn->query($sql);

// Handle Cancel
if (isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];
    $vehicle_id = $_POST['vehicle_id'];
    $conn->query("UPDATE bookings SET booking_status = 'Cancelled' WHERE booking_id = $booking_id");
    $conn->query("UPDATE vehicles SET status = 'Available' WHERE vehicle_id = $vehicle_id");
    echo "<script>window.location.href='my_bookings.php';</script>";
}
?>

<div class="container mt-5">
    <h2 class="mb-4">My Bookings</h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Vehicle</th>
                        <th>Dates</th>
                        <th>Total Cost</th>
                        <th>Driver Info</th>
                        <th>Status</th> <th>Action</th> </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo $row['image_url']; ?>" class="rounded" width="80" height="50" style="object-fit: cover; margin-right: 10px;">
                                    <strong><?php echo $row['brand'] . " " . $row['model']; ?></strong>
                                </div>
                            </td>

                            <td>
                                <small>From:</small> <?php echo $row['start_date']; ?><br>
                                <small>To:</small> <?php echo $row['end_date']; ?>
                            </td>

                            <td>
                                <span class="badge bg-success fs-6">$<?php echo $row['total_price']; ?></span>
                            </td>

                            <td>
                                <?php if($row['driver_name']): ?>
                                    <strong><?php echo $row['driver_name']; ?></strong><br>
                                    <small><?php echo $row['driver_phone']; ?></small>
                                <?php elseif($row['is_with_driver']): ?>
                                    <span class="text-warning">Assigning...</span>
                                <?php else: ?>
                                    <span class="text-muted">Self Drive</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php 
                                    $s = $row['booking_status'];
                                    $color = match($s) {
                                        'Awaiting Deposit' => 'info text-dark', // Added this line
                                        'Pending' => 'warning text-dark',
                                        'Confirmed' => 'primary',
                                        'Payment Due' => 'danger animate__animated animate__flash',
                                        'Completed' => 'success',
                                        'Cancelled' => 'secondary',
                                        default => 'secondary'
                                    };
                                ?>
                                <span class="badge bg-<?php echo $color; ?>"><?php echo $s; ?></span>
                            </td>

                            <td>
                                <?php if($s == 'Awaiting Deposit'): ?>
                                    <a href="payment_gateway.php?booking_id=<?php echo $row['booking_id']; ?>&amount=15&type=deposit" class="btn btn-success btn-sm fw-bold w-100 mb-1">
                                        <i class="fa-solid fa-credit-card"></i> Pay $15 Deposit
                                    </a>
                                    <form method="POST" onsubmit="return confirm('Cancel this request?');">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                        <input type="hidden" name="vehicle_id" value="<?php echo $row['vehicle_id']; ?>">
                                        <button type="submit" name="cancel_booking" class="btn btn-outline-danger btn-sm w-100">Cancel</button>
                                    </form>

                                <?php elseif($s == 'Pending'): ?>
                                    <form method="POST" onsubmit="return confirm('Cancel?');">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                        <input type="hidden" name="vehicle_id" value="<?php echo $row['vehicle_id']; ?>">
                                        <button type="submit" name="cancel_booking" class="btn btn-outline-danger btn-sm w-100">Cancel</button>
                                    </form>
                                
                                <?php elseif($s == 'Payment Due'): ?>
                                    <a href="invoice_summary.php?booking_id=<?php echo $row['booking_id']; ?>" class="btn btn-danger btn-sm fw-bold">
                                        <i class="fa-solid fa-file-invoice-dollar"></i> View Bill & Pay
                                    </a>
                                
                                <?php elseif($s == 'Completed'): ?>
                                    <button class="btn btn-outline-success btn-sm" disabled>Paid <i class="fa-solid fa-check"></i></button>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted text-center">No bookings found.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>