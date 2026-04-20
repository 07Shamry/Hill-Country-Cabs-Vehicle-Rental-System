<?php
include 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['booking_id'])) {
    echo "<script>window.location.href='my_bookings.php';</script>";
    exit();
}

$booking_id = $_GET['booking_id'];
$user_id = $_SESSION['user_id'];

// 1. Fetch Full Booking Details + Category Rules + Damage Cost
$sql = "SELECT b.*, 
               v.brand, v.model, v.image_url, v.daily_rate,
               c.category_name, c.free_km_per_day, c.extra_km_price, c.tax_rate, c.base_driver_fee,
               (SELECT IFNULL(SUM(repair_cost), 0) FROM vehicle_damages WHERE booking_id = b.booking_id) as repair_cost
        FROM bookings b 
        JOIN vehicles v ON b.vehicle_id = v.vehicle_id 
        JOIN categories c ON v.category_id = c.category_id
        WHERE b.booking_id = $booking_id AND b.user_id = $user_id";

$result = $conn->query($sql);

if($result->num_rows == 0) die("Booking not found.");
$data = $result->fetch_assoc();

// --- 2. RECONSTRUCT THE INVOICE BREAKDOWN ---

// A. Calculate Days
$start = new DateTime($data['start_date']);
$end = new DateTime($data['end_date']);
$days_booked = max(1, $start->diff($end)->days + 1);

// B. Base Costs
$base_rent = $days_booked * $data['daily_rate'];
$driver_fee = $data['is_with_driver'] ? ($days_booked * $data['base_driver_fee']) : 0;

// C. Mileage Costs
$free_limit = $days_booked * $data['free_km_per_day'];
$excess_km = max(0, $data['actual_km'] - $free_limit);
$excess_charge = $excess_km * $data['extra_km_price'];

// D. Damage
$damage_cost = $data['repair_cost'];

// E. Discount
$discount = ($days_booked >= 7) ? ($base_rent * 0.10) : 0;

// F. Tax
$subtotal_calc = ($base_rent + $driver_fee + $excess_charge + $damage_cost) - $discount;
$tax_calc = $subtotal_calc * $data['tax_rate'];

// --- NEW DEPOSIT LOGIC ---
$DEPOSIT_AMOUNT = 15.00;

// G. Adjustment (Factoring in Deposit)
$calculated_gross_total = $subtotal_calc + $tax_calc;
$calculated_net_total = $calculated_gross_total - $DEPOSIT_AMOUNT;
$adjustment = $data['total_price'] - $calculated_net_total;

?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                    <h4 class="mb-0"><i class="fa-solid fa-file-invoice"></i> INVOICE #<?php echo $booking_id; ?></h4>
                    <?php if ($data['total_price'] > 0): ?>
                        <span class="badge bg-danger">UNPAID</span>
                    <?php else: ?>
                        <span class="badge bg-success">PAID</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-5">
                    
                    <div class="row mb-4 border-bottom pb-4">
                        <div class="col-md-6">
                            <h5 class="text-primary mb-0">Premium Vehicle Rental</h5>
                            <small class="text-muted">123 Main Street, Kandy</small><br>
                            <small class="text-muted">+94 77 123 4567</small>
                        </div>
                        <div class="col-md-6 text-end">
                            <h5 class="mb-1"><?php echo $data['brand'] . " " . $data['model']; ?></h5>
                            <span class="badge bg-secondary"><?php echo $data['category_name']; ?></span>
                            <div class="mt-2">
                                <img src="<?php echo $data['image_url']; ?>" class="rounded" width="100">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4 bg-light p-3 rounded mx-0">
                        <div class="col-4 border-end text-center">
                            <small class="text-muted d-block">Dates</small>
                            <strong><?php echo $data['start_date']; ?> <br> to <?php echo $data['end_date']; ?></strong>
                        </div>
                        <div class="col-4 border-end text-center">
                            <small class="text-muted d-block">Mileage</small>
                            <strong><?php echo $data['actual_km']; ?> km</strong>
                            <br><small class="text-danger">(Excess: <?php echo $excess_km; ?> km)</small>
                        </div>
                        <div class="col-4 text-center">
                            <small class="text-muted d-block">Total Days</small>
                            <strong><?php echo $days_booked; ?> Days</strong>
                        </div>
                    </div>

                    <table class="table table-borderless">
                        <thead>
                            <tr class="border-bottom">
                                <th>Description</th>
                                <th class="text-end">Amount (LKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Base Rental (<?php echo $days_booked; ?> Days x $<?php echo $data['daily_rate']; ?>)</td>
                                <td class="text-end">$<?php echo number_format($base_rent, 2); ?></td>
                            </tr>
                            
                            <?php if($driver_fee > 0): ?>
                            <tr>
                                <td>Driver Fee</td>
                                <td class="text-end">$<?php echo number_format($driver_fee, 2); ?></td>
                            </tr>
                            <?php endif; ?>

                            <?php if($excess_charge > 0): ?>
                            <tr>
                                <td>Excess Mileage Charge (<?php echo $excess_km; ?> km x $<?php echo $data['extra_km_price']; ?>)</td>
                                <td class="text-end">$<?php echo number_format($excess_charge, 2); ?></td>
                            </tr>
                            <?php endif; ?>

                            <?php if($damage_cost > 0): ?>
                            <tr class="text-danger bg-danger bg-opacity-10 mt-3">
                                <td>
                                    <strong><i class="fa-solid fa-triangle-exclamation"></i> Damage Repair</strong><br>
                                    <small class="text-dark">Reason: <?php echo $data['damage_reason']; ?></small>
                                </td>
                                <td class="text-end fw-bold">$<?php echo number_format($damage_cost, 2); ?></td>
                            </tr>
                            <?php endif; ?>

                            <?php if($adjustment != 0): ?>
                            <tr>
                                <td>Overdue / Date Adjustments</td>
                                <td class="text-end">$<?php echo number_format($adjustment, 2); ?></td>
                            </tr>
                            <?php endif; ?>

                            <?php if($discount > 0): ?>
                            <tr class="text-success">
                                <td>Long Trip Discount (10%)</td>
                                <td class="text-end">-$<?php echo number_format($discount, 2); ?></td>
                            </tr>
                            <?php endif; ?>

                            <tr>
                                <td>Tax (<?php echo $data['tax_rate'] * 100; ?>%)</td>
                                <td class="text-end">$<?php echo number_format($tax_calc + ($adjustment * $data['tax_rate']), 2); ?></td>
                            </tr>

                            <tr class="border-top bg-light">
                                <td class="pt-2"><strong>Gross Charges</strong></td>
                                <td class="text-end pt-2"><strong>$<?php echo number_format($calculated_gross_total + $adjustment, 2); ?></strong></td>
                            </tr>

                            <tr class="text-success border-bottom">
                                <td class="pb-3"><strong>Less: Security Deposit (Already Paid)</strong></td>
                                <td class="text-end pb-3 fs-5"><strong>-$<?php echo number_format($DEPOSIT_AMOUNT, 2); ?></strong></td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-dark text-white">
                            <tr>
                                <td class="pt-3"><strong>FINAL BALANCE DUE</strong></td>
                                <td class="text-end pt-3 fs-3">
                                    <strong>
                                        <?php 
                                            if ($data['total_price'] <= 0) {
                                                echo "REFUND DUE: $" . number_format(abs($data['total_price']), 2);
                                            } else {
                                                echo "$" . number_format($data['total_price'], 2); 
                                            }
                                        ?>
                                    </strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <?php if ($data['total_price'] > 0): ?>
                        <form action="payment_gateway.php" method="GET" class="mt-4">
                            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                            <input type="hidden" name="amount" value="<?php echo $data['total_price']; ?>">
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg fw-bold shadow">
                                    <i class="fa-solid fa-credit-card"></i> PROCEED TO PAYMENT
                                </button>
                                <a href="my_bookings.php" class="btn btn-outline-secondary">Back to My Bookings</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-success mt-4 text-center">
                            <i class="fa-solid fa-check-circle"></i> Balance settled automatically by deposit. No payment required.
                        </div>
                        <div class="d-grid"><a href="my_bookings.php" class="btn btn-outline-secondary">Back to My Bookings</a></div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>