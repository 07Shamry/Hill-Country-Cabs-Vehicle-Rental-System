<?php
include 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) { echo "<script>window.location.href='login.php';</script>"; exit(); }
if (!isset($_GET['vehicle_id'])) { echo "<script>window.location.href='vehicle_list.php';</script>"; exit(); }

$vehicle_id = $_GET['vehicle_id'];
$user_id = $_SESSION['user_id'];


$sql = "SELECT vehicles.*, categories.category_name, categories.base_driver_fee, 
               categories.free_km_per_day, categories.extra_km_price 
        FROM vehicles 
        JOIN categories ON vehicles.category_id = categories.category_id 
        WHERE vehicle_id = $vehicle_id";
$result = $conn->query($sql);
$vehicle = $result->fetch_assoc();

$DEPOSIT_AMOUNT = 15.00; 


echo "<script>
    const dailyRate = {$vehicle['daily_rate']};
    const driverFee = {$vehicle['base_driver_fee']};
    const deposit = {$DEPOSIT_AMOUNT};
    const freeKmPerDay = {$vehicle['free_km_per_day']};
    const extraKmPrice = {$vehicle['extra_km_price']};
</script>";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $estimated_km = $_POST['estimated_km'];
    $pickup_time = $_POST['pickup_time'];
    $dropoff_time = $_POST['dropoff_time'];
    $with_driver = isset($_POST['with_driver']) ? 1 : 0;
    
    
    $pickup_location = $with_driver ? $conn->real_escape_string($_POST['pickup_location']) : 'Showroom';
    $dropoff_location = $with_driver ? $conn->real_escape_string($_POST['dropoff_location']) : 'Showroom';

    // --- NEW VALIDATION LOGIC ---
    $errors = [];
    
    // 1. Combine Date & Time for accurate calculations
    $booking_start_datetime = strtotime("$start_date $pickup_time");
    $booking_end_datetime = strtotime("$end_date $dropoff_time");
    $current_time = time();
    $min_booking_time = $current_time + (24 * 60 * 60); // Now + 24 Hours

    // 2. Validate 24-Hour Notice Rule
    if ($booking_start_datetime < $min_booking_time) {
        $errors[] = "Bookings must be made at least 24 hours in advance from now.";
    }

    // 3. Validate End Date/Time vs Start Date/Time
    if ($booking_end_datetime <= $booking_start_datetime) {
        $errors[] = "Drop-off time must be after the Pickup time.";
    }

    // 4. Validate Single Day Logic specific check (Redundant but safe)
    if ($start_date == $end_date && strtotime($dropoff_time) <= strtotime($pickup_time)) {
        $errors[] = "For same-day rentals, drop-off time must be later than pickup time.";
    }

    if(!empty($errors)) {
        echo "<script>Swal.fire('Validation Error', '" . implode("<br>", $errors) . "', 'error');</script>";
    } else {
        // Availability Check
        // Note: We check if dates overlap
        $check_sql = "SELECT * FROM bookings 
                      WHERE vehicle_id = '$vehicle_id' 
                      AND booking_status IN ('Confirmed', 'Pending', 'Active')
                      AND (start_date <= '$end_date' AND end_date >= '$start_date')";
        
        if ($conn->query($check_sql)->num_rows > 0) {
            echo "<script>Swal.fire('Unavailable', 'Vehicle is booked for these dates.', 'error');</script>";
        } else {
            // Calculation
            $diff = strtotime($end_date) - strtotime($start_date);
            $days = max(1, round($diff / (60 * 60 * 24)));
            
            // Rent
            $rent_cost = ($days * $vehicle['daily_rate']) + ($with_driver ? ($days * $vehicle['base_driver_fee']) : 0);
            
            // Excess Km Charge (Estimated)
            $total_free_km = $days * $vehicle['free_km_per_day'];
            $excess_km = max(0, $estimated_km - $total_free_km);
            $excess_charge = $excess_km * $vehicle['extra_km_price'];
            
            // Total
            $total_price = $rent_cost + $excess_charge; 
            
            
            $sql = "INSERT INTO bookings (user_id, vehicle_id, start_date, end_date, estimated_km, total_price, is_with_driver, booking_status, pickup_time, dropoff_time, pickup_location, dropoff_location) 
                    VALUES ('$user_id', '$vehicle_id', '$start_date', '$end_date', '$estimated_km', '$total_price', '$with_driver', 'Awaiting Deposit', '$pickup_time', '$dropoff_time', '$pickup_location', '$dropoff_location')";
            
            if ($conn->query($sql)) {
                $new_booking_id = $conn->insert_id;
                
                
                echo "<script>
                    Swal.fire({
                        title: 'Booking Saved!',
                        text: 'Your dates are reserved. Please pay the $$DEPOSIT_AMOUNT deposit to send the request to our Admin.',
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonColor: '#198754', // Green
                        cancelButtonColor: '#6c757d',  // Gray
                        confirmButtonText: '<i class=\"fa-solid fa-credit-card\"></i> Pay Now',
                        cancelButtonText: '<i class=\"fa-solid fa-clock\"></i> Pay Later',
                        reverseButtons: true // Puts 'Pay Now' on the right side
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Clicked 'Pay Now'
                            window.location.href = 'payment_gateway.php?booking_id=$new_booking_id&amount=$DEPOSIT_AMOUNT&type=deposit';
                        } else {
                            // Clicked 'Pay Later' or closed the popup
                            window.location.href = 'my_bookings.php';
                        }
                    });
                </script>";
            } else {
                echo "<script>Swal.fire('Error', '" . $conn->error . "', 'error');</script>";
            }
        }
    }
}
?>

<div class="container mt-5 mb-5">
    
    <div class="alert alert-warning text-center fw-bold shadow-sm">
        <i class="fa-solid fa-location-dot"></i> NOTICE: Services available ONLY for Customers based in Kandy region.
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-lg mb-3">
                <img src="<?php echo $vehicle['image_url']; ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                <div class="card-body">
                    <h4><?php echo $vehicle['brand'] . " " . $vehicle['model']; ?></h4>
                    <span class="badge bg-secondary"><?php echo $vehicle['category_name']; ?></span>
                    <hr>
                    <ul class="list-unstyled small">
                        <li class="mb-2 d-flex justify-content-between"><span>Daily Rate:</span> <strong>$<?php echo $vehicle['daily_rate']; ?></strong></li>
                        <li class="mb-2 d-flex justify-content-between"><span>Deposit:</span> <strong>$<?php echo number_format($DEPOSIT_AMOUNT, 2); ?></strong></li>
                        <li class="mb-2 d-flex justify-content-between"><span>Free Limit:</span> <strong><?php echo $vehicle['free_km_per_day']; ?> km/day</strong></li>
                        <li class="mb-2 d-flex justify-content-between text-danger"><span>Extra Charge:</span> <strong>$<?php echo $vehicle['extra_km_price']; ?> / km</strong></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Configure Your Trip</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" id="bookingForm" onsubmit="return validateForm()">
                        
                        <h6 class="text-primary mb-3">1. Schedule & Distance</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="small text-muted">Start Date (Min: 24h Notice)</label>
                                <input type="date" id="start_date" name="start_date" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted">Est. Total Km</label>
                                <input type="number" id="estimated_km" name="estimated_km" class="form-control border-primary" placeholder="e.g. 250" required min="1">
                                <small class="text-danger" id="excess_msg" style="display:none; font-size:0.8em;"></small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="small fw-bold">Pickup Time</label>
                                <input type="time" name="pickup_time" id="pickup_time" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold">Dropoff Time</label>
                                <input type="time" name="dropoff_time" id="dropoff_time" class="form-control" required>
                            </div>
                        </div>

                        <h6 class="text-primary mt-4 mb-3">2. Driver & Logistics</h6>
                        <div class="form-check form-switch p-3 bg-light rounded mb-3 border">
                            <input class="form-check-input" type="checkbox" id="with_driver" name="with_driver" onchange="toggleLocationFields()">
                            <label class="form-check-label fw-bold ms-3" for="with_driver">
                                I need a Driver (+$<?php echo $vehicle['base_driver_fee']; ?>/day)
                            </label>
                        </div>

                        <div id="logistics_section">
                            <div id="self_drive_msg" class="alert alert-info small">
                                <i class="fa-solid fa-info-circle"></i> <strong>Self-Drive:</strong> Collect/Return at Showroom.
                            </div>
                            <div id="driver_locations" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="small fw-bold">Pickup Location</label>
                                        <input type="text" id="pickup_loc" name="pickup_location" class="form-control" placeholder="e.g. Kandy Hotel">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="small fw-bold">Dropoff Location</label>
                                        <input type="text" id="dropoff_loc" name="dropoff_location" class="form-control" placeholder="e.g. Kandy Station">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-light border mt-4">
                            <div class="d-flex justify-content-between"><span>Base Rent:</span> <strong id="rent_cost">$0.00</strong></div>
                            <div class="d-flex justify-content-between text-danger"><span>Excess Mileage Charge:</span> <strong id="excess_cost">$0.00</strong></div>
                            <div class="d-flex justify-content-between text-muted"><span>+ Security Deposit:</span> <strong>$<?php echo number_format($DEPOSIT_AMOUNT, 2); ?></strong></div>
                            <hr>
                            <div class="d-flex justify-content-between fs-4 text-success"><span>Total Due:</span> <strong id="total_due">$0.00</strong></div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm">CONFIRM & BOOK</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');
    const kmInput = document.getElementById('estimated_km');
    const driverCheck = document.getElementById('with_driver');
    const pickupTimeInput = document.getElementById('pickup_time');
    const dropoffTimeInput = document.getElementById('dropoff_time');

    // Toggle Logistics
    function toggleLocationFields() {
        const isDriver = driverCheck.checked;
        const driverDiv = document.getElementById('driver_locations');
        const selfMsg = document.getElementById('self_drive_msg');
        const locInputs = driverDiv.querySelectorAll('input');

        if (isDriver) {
            driverDiv.style.display = 'block';
            selfMsg.style.display = 'none';
            locInputs.forEach(i => i.setAttribute('required', 'true'));
        } else {
            driverDiv.style.display = 'none';
            selfMsg.style.display = 'block';
            locInputs.forEach(i => { i.removeAttribute('required'); i.value = ''; });
        }
        calculate();
    }

    // MAIN CALCULATION ENGINE
    function calculate() {
        if (startInput.value && endInput.value) {
            const start = new Date(startInput.value);
            const end = new Date(endInput.value);
            
            if(end < start) return; 

            let days = Math.ceil((end - start) / (1000 * 3600 * 24));
            if(days < 1) days = 1;

            // 1. Rent + Driver
            let rent = (days * dailyRate) + (driverCheck.checked ? (days * driverFee) : 0);
            
            // 2. Excess Mileage Logic
            let totalFreeKm = days * freeKmPerDay;
            let estKm = parseInt(kmInput.value) || 0;
            let excessKm = Math.max(0, estKm - totalFreeKm);
            let excessCost = excessKm * extraKmPrice;

            // 3. UI Updates
            if(excessKm > 0) {
                document.getElementById('excess_msg').style.display = 'block';
                document.getElementById('excess_msg').innerText = `Exceeds limit by ${excessKm}km (+ $${excessCost})`;
            } else {
                document.getElementById('excess_msg').style.display = 'none';
            }

            let total = rent + excessCost + deposit;

            document.getElementById('rent_cost').innerText = "$" + rent.toFixed(2);
            document.getElementById('excess_cost').innerText = "$" + excessCost.toFixed(2);
            document.getElementById('total_due').innerText = "$" + total.toFixed(2);
        }
    }

    // Client-Side Time Validation (Instant Feedback)
    function validateForm() {
        // 1. Same Day Logic
        if (startInput.value === endInput.value) {
            if (dropoffTimeInput.value <= pickupTimeInput.value) {
                Swal.fire('Time Error', 'For same-day rentals, Drop-off time must be after Pickup time.', 'warning');
                return false;
            }
        }
        return true;
    }

    startInput.addEventListener('change', calculate);
    endInput.addEventListener('change', calculate);
    kmInput.addEventListener('keyup', calculate);
    driverCheck.addEventListener('change', calculate);
    toggleLocationFields();
</script>

<?php include 'includes/footer.php'; ?>