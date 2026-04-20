<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

$booking_id = $_GET['booking_id'] ?? 0;

// 1. Fetch Booking & Vehicle Data
$sql = "SELECT bookings.*, 
               vehicles.brand, vehicles.model, vehicles.daily_rate, vehicles.image_url, 
               users.full_name, users.phone_number,
               categories.category_name, categories.free_km_per_day, categories.extra_km_price, categories.tax_rate, categories.base_driver_fee
        FROM bookings 
        JOIN vehicles ON bookings.vehicle_id = vehicles.vehicle_id 
        JOIN categories ON vehicles.category_id = categories.category_id
        JOIN users ON bookings.user_id = users.user_id 
        WHERE booking_id = $booking_id";

$result = $conn->query($sql);
if ($result->num_rows == 0) die("Booking not found.");
$data = $result->fetch_assoc();

// 2. Pre-Calculate Defaults
$start = new DateTime($data['start_date']);
$end_booked = new DateTime($data['end_date']);
$today = new DateTime(); 

// Calculate Booked Days vs Actual Days
$booked_days = max(1, $start->diff($end_booked)->days + 1); // +1 to include start date
$actual_days = max(1, $start->diff($today)->days + 1);

// THE DEPOSIT PAID BY CUSTOMER
$DEPOSIT_AMOUNT = 15.00;

// 3. Handle Invoice Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Admin Inputs
    $final_days = $_POST['final_days']; // Admin can override days
    $km_driven = $_POST['km_driven'];
    $damage_cost = $_POST['damage_cost'];
    $damage_reason = $conn->real_escape_string($_POST['damage_reason']);
    
    // --- Calculations ---
    
    // A. Base Cost (Rent for actual days used)
    $base_cost = $final_days * $data['daily_rate'];
    
    // B. Driver Cost
    $driver_cost = 0;
    if ($data['is_with_driver']) {
        $driver_cost = $final_days * $data['base_driver_fee'];
    }

    // C. Mileage Logic
    $free_km_limit = $final_days * $data['free_km_per_day'];
    $excess_km = max(0, $km_driven - $free_km_limit);
    $excess_charge = $excess_km * $data['extra_km_price'];

    // D. Discount (10% if >= 7 days)
    $discount = 0;
    if ($final_days >= 7) {
        $discount = $base_cost * 0.10;
    }

    // E. Tax & Total
    $subtotal = ($base_cost - $discount) + $driver_cost + $excess_charge + $damage_cost;
    $tax_amount = $subtotal * $data['tax_rate'];
    
    // --- DEPOSIT DEDUCTION LOGIC ---
    $gross_total = $subtotal + $tax_amount;
    $net_payable = $gross_total - $DEPOSIT_AMOUNT; // Subtract Deposit
    
    // If net_payable is <= 0, the customer owes nothing!
    $new_status = ($net_payable > 0) ? 'Payment Due' : 'Completed';

    // --- Database Updates ---
    
    // 1. Update Booking
    $update_sql = "UPDATE bookings SET 
                   booking_status = '$new_status', 
                   total_price = '$net_payable',
                   actual_km = '$km_driven',
                   damage_reason = '$damage_reason' 
                   WHERE booking_id = $booking_id";
    $conn->query($update_sql);

    // 2. Record Damage in History (If cost > 0)
    if ($damage_cost > 0) {
        $conn->query("INSERT INTO vehicle_damages (booking_id, reported_by, description, repair_cost) 
                      VALUES ($booking_id, 'Admin', '$damage_reason', '$damage_cost')");
    }

    // 3. Free up Vehicle
    $conn->query("UPDATE vehicles SET status='Available' WHERE vehicle_id={$data['vehicle_id']}");

    // --- Success Response (JSON for SweetAlert) ---
    $alert_title = ($net_payable > 0) ? "Invoice Generated: Payment Due" : "Invoice Generated: Fully Paid";
    $alert_color = ($net_payable > 0) ? "red" : "green";

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '$alert_title',
                html: `
                    <div style='text-align: left; font-size: 0.9em;'>
                        <p><strong>Days Charged:</strong> $final_days days</p>
                        <p><strong>Mileage:</strong> $km_driven km (Excess: $excess_km km)</p>
                        <hr>
                        <table style='width:100%'>
                            <tr><td>Base Rent:</td><td style='text-align:right'>$".number_format($base_cost,2)."</td></tr>
                            <tr><td>Driver Fee:</td><td style='text-align:right'>$".number_format($driver_cost,2)."</td></tr>
                            <tr><td>Excess Km:</td><td style='text-align:right'>$".number_format($excess_charge,2)."</td></tr>
                            <tr><td>Damage:</td><td style='text-align:right; color:red'>$".number_format($damage_cost,2)."</td></tr>
                            <tr><td>Discount:</td><td style='text-align:right; color:green'>-$".number_format($discount,2)."</td></tr>
                            <tr><td>Tax:</td><td style='text-align:right'>$".number_format($tax_amount,2)."</td></tr>
                            <tr><td>Gross Total:</td><td style='text-align:right'>$".number_format($gross_total,2)."</td></tr>
                            <tr class='text-success'><td>Less Deposit:</td><td style='text-align:right'>-$".number_format($DEPOSIT_AMOUNT,2)."</td></tr>
                            <tr style='font-weight:bold; font-size:1.2em; color:$alert_color'><td>BALANCE DUE:</td><td style='text-align:right'>$".number_format($net_payable,2)."</td></tr>
                        </table>
                    </div>
                `,
                icon: 'success',
                confirmButtonText: 'Back to Bookings',
                confirmButtonColor: '#0d6efd'
            }).then((result) => {
                window.location.href = 'manage_bookings.php';
            });
        });
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Process Return</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { background-color: #f8f9fa; }</style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="card shadow border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Return Vehicle & Finalize Bill</h4>
            <a href="manage_bookings.php" class="btn btn-outline-secondary btn-sm text-white">Cancel</a>
        </div>
        <div class="card-body p-4">

            <div class="row mb-4 bg-light p-3 rounded mx-0">
                <div class="col-md-2 text-center">
                    <img src="../<?php echo $data['image_url']; ?>" class="img-fluid rounded" style="max-height: 80px;">
                </div>
                <div class="col-md-5">
                    <h5 class="mb-1"><?php echo $data['brand'] . " " . $data['model']; ?></h5>
                    <span class="badge bg-secondary"><?php echo $data['category_name']; ?></span>
                    <div class="mt-2 text-muted small">
                        Daily Rate: $<?php echo $data['daily_rate']; ?> | 
                        Limit: <?php echo $data['free_km_per_day']; ?>km/day | 
                        Extra: $<?php echo $data['extra_km_price']; ?>/km
                    </div>
                </div>
                <div class="col-md-5 border-start">
                    <h6>Customer: <?php echo $data['full_name']; ?></h6>
                    <p class="mb-0 text-muted"><i class="fa fa-phone"></i> <?php echo $data['phone_number']; ?></p>
                    <small>Booking ID: #<?php echo $booking_id; ?></small>
                </div>
            </div>

            <form method="POST">
                
                <h5 class="text-primary border-bottom pb-2">1. Duration Check</h5>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="text-muted">Booked Dates</label>
                        <input type="text" class="form-control bg-light" value="<?php echo $data['start_date'] . ' to ' . $data['end_date']; ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted">Booked Days (Planned)</label>
                        <input type="text" class="form-control bg-light" value="<?php echo $booked_days; ?> Days" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold text-dark">Total Days to Charge</label>
                        <input type="number" name="final_days" class="form-control border-primary fw-bold" value="<?php echo $actual_days; ?>" min="1" required>
                        <small class="text-muted">Edit this if returned early/late.</small>
                    </div>
                </div>

                <h5 class="text-primary border-bottom pb-2">2. Mileage Check</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-muted">Free Allowance (Based on Days)</label>
                        <input type="text" class="form-control bg-light" value="Calculated automatically..." readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold text-dark">Total Km Driven (Odometer)</label>
                        <input type="number" name="km_driven" class="form-control border-primary fw-bold" placeholder="Enter Total Km" required>
                        <small class="text-muted">Enter the total kilometers driven during the trip.</small>
                    </div>
                </div>

                <h5 class="text-danger border-bottom pb-2">3. Damage Inspection</h5>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="fw-bold">Damage Cost ($)</label>
                        <input type="number" name="damage_cost" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-md-8">
                        <label class="fw-bold">Reason / Description</label>
                        <textarea name="damage_reason" class="form-control" rows="1" placeholder="e.g. Scratch on left bumper..."></textarea>
                    </div>
                </div>

                <div class="alert alert-warning border mt-3 mb-4">
                    <i class="fa-solid fa-money-bill"></i> <strong>Note:</strong> The Security Deposit of <strong>$<?php echo number_format($DEPOSIT_AMOUNT, 2); ?></strong> paid by the customer will automatically be deducted from the final calculated bill.
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg py-3 fw-bold shadow-sm">
                        CALCULATE INVOICE & CLOSE BOOKING
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

</body>
</html>