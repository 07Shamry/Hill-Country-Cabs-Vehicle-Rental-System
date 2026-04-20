<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db_connect.php';
include 'includes/header.php';

// Fetch top 3 available vehicles for the "Featured" section
$sql = "SELECT vehicles.*, categories.category_name 
        FROM vehicles 
        JOIN categories ON vehicles.category_id = categories.category_id 
        WHERE vehicles.status != 'Maintenance' 
        LIMIT 3";
$featured_vehicles = $conn->query($sql);
?>

<div class="position-relative text-white text-center d-flex align-items-center justify-content-center" style="min-height: 80vh; overflow: hidden;">
    
    <video autoplay loop muted playsinline style="position: absolute; top: 50%; left: 50%; min-width: 100%; min-height: 100%; transform: translate(-50%, -50%); z-index: 0; object-fit: cover;">
        <source src="assets/images/vehicle.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.65); z-index: 1;"></div>

    <div class="container py-5 position-relative" style="z-index: 2;">
        <h1 class="display-2 fw-bold mb-3 animate__animated animate__fadeInDown" style="color: #FFD700; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">Hill Country Cabs</h1>
        <h2 class="display-5 fw-bold mb-3 animate__animated animate__fadeInDown">Premium Vehicle Rentals in Kandy</h2>
        <p class="lead mb-5 animate__animated animate__fadeInUp">Experience luxury, comfort, and reliability. Book your perfect ride today.</p>
        
        <div class="card p-3 shadow-lg mx-auto animate__animated animate__fadeInUp" style="max-width: 800px; border-radius: 50px; background: rgba(255, 255, 255, 0.9);">
            <form action="vehicle_list.php" method="GET" class="row g-2 align-items-center">
                <div class="col-md-8">
                    <select name="category" class="form-select form-select-lg border-0 bg-transparent" style="border-radius: 30px;">
                        <option value="all">What type of vehicle are you looking for?</option>
                        <option value="Car">Cars</option>
                        <option value="Van">Vans</option>
                        <option value="Motorbike">Motorbikes</option>
                    </select>
                </div>
                <div class="col-md-4 d-grid">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold" style="border-radius: 30px;">
                        <i class="fa-solid fa-magnifying-glass"></i> Find a Vehicle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="container py-5 mt-4">
    <div class="text-center mb-5">
        <h2 class="fw-bold">How It Works</h2>
        <p class="text-muted">Rent a vehicle in three simple steps</p>
    </div>
    <div class="row text-center g-4">
        <div class="col-md-4">
            <div class="p-4 bg-light rounded shadow-sm h-100 border-top border-primary border-4">
                <i class="fa-solid fa-car-side fa-3x text-primary mb-3"></i>
                <h5>1. Choose a Vehicle</h5>
                <p class="text-muted small">Browse our diverse fleet of premium cars, vans, and bikes.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 bg-light rounded shadow-sm h-100 border-top border-success border-4">
                <i class="fa-solid fa-calendar-check fa-3x text-success mb-3"></i>
                <h5>2. Pick Your Dates & Logistics</h5>
                <p class="text-muted small">Select your dates, add a driver if needed, and confirm locations.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 bg-light rounded shadow-sm h-100 border-top border-danger border-4">
                <i class="fa-solid fa-key fa-3x text-danger mb-3"></i>
                <h5>3. Pay Deposit & Drive</h5>
                <p class="text-muted small">Secure your booking with a $15 deposit and enjoy the ride.</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-light py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="fw-bold mb-0">Featured Fleet</h2>
                <p class="text-muted mb-0">Our most popular rides</p>
            </div>
            <a href="vehicle_list.php" class="btn btn-outline-dark">View All Vehicles <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="row g-4">
            <?php if ($featured_vehicles && $featured_vehicles->num_rows > 0): ?>
                <?php while($row = $featured_vehicles->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm border-0 vehicle-card transition-hover">
                            <img src="<?php echo $row['image_url']; ?>" class="card-img-top" alt="Vehicle Image" style="height: 220px; object-fit: cover;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title fw-bold mb-0"><?php echo $row['brand'] . " " . $row['model']; ?></h5>
                                    <span class="badge bg-secondary"><?php echo $row['category_name']; ?></span>
                                </div>
                                <hr>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <h4 class="text-dark mb-0 fw-bold">$<?php echo $row['daily_rate']; ?><span class="fs-6 text-muted fw-normal">/day</span></h4>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0 p-3">
                                <a href="booking.php?vehicle_id=<?php echo $row['vehicle_id']; ?>" class="btn btn-dark w-100 py-2 fw-bold">Book Now</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No featured vehicles available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container py-5 my-4">
    <div class="row align-items-center">
        <div class="col-md-6 mb-4 mb-md-0">
            <h2 class="fw-bold mb-4">Why Choose Hill Country Cabs?</h2>
            <ul class="list-group list-group-flush">
                <li class="list-group-item bg-transparent border-0 px-0 mb-2">
                    <i class="fa-solid fa-check-circle text-success me-2"></i> <strong>Transparent Pricing:</strong> No hidden fees. What you see is what you pay.
                </li>
                <li class="list-group-item bg-transparent border-0 px-0 mb-2">
                    <i class="fa-solid fa-check-circle text-success me-2"></i> <strong>Professional Drivers:</strong> Option to add a vetted, professional driver to any booking.
                </li>
                <li class="list-group-item bg-transparent border-0 px-0 mb-2">
                    <i class="fa-solid fa-check-circle text-success me-2"></i> <strong>Strict Quality Control:</strong> Every vehicle is inspected and sanitized before pickup.
                </li>
                <li class="list-group-item bg-transparent border-0 px-0">
                    <i class="fa-solid fa-check-circle text-success me-2"></i> <strong>Flexible Logistics:</strong> Convenient showroom pickup or custom locations for driver-assisted trips.
                </li>
            </ul>
        </div>
        <div class="col-md-6">
            <img src="https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&q=80&w=800" class="img-fluid rounded-4 shadow-lg" alt="Luxury Car">
        </div>
    </div>
</div>

<style>
    .transition-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .transition-hover:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important; }
</style>

<?php include 'includes/footer.php'; ?>