<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// Filter Logic: Check if a category is clicked
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';

// Build SQL Query
$sql = "SELECT vehicles.*, categories.category_name 
        FROM vehicles 
        JOIN categories ON vehicles.category_id = categories.category_id 
        WHERE vehicles.status != 'Maintenance'"; // <--- I added the missing "; here

if ($category_filter != 'all') {
    $sql .= " AND categories.category_name = '$category_filter'";
}

$result = $conn->query($sql);
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Our Fleet</h2>

    <div class="d-flex justify-content-center mb-4">
        <a href="vehicle_list.php?category=all" class="btn btn-outline-dark mx-1 <?php echo ($category_filter=='all')?'active':''; ?>">All</a>
        <a href="vehicle_list.php?category=Car" class="btn btn-outline-primary mx-1 <?php echo ($category_filter=='Car')?'active':''; ?>">Cars</a>
        <a href="vehicle_list.php?category=Van" class="btn btn-outline-success mx-1 <?php echo ($category_filter=='Van')?'active':''; ?>">Vans</a>
        <a href="vehicle_list.php?category=Motorbike" class="btn btn-outline-warning mx-1 <?php echo ($category_filter=='Motorbike')?'active':''; ?>">Bikes</a>
    </div>

    <div class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo $row['image_url']; ?>" class="card-img-top" alt="Vehicle Image" style="height: 200px; object-fit: cover;">
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['brand'] . " " . $row['model']; ?></h5>
                            <p class="card-text text-muted">
                                <i class="fa-solid fa-tag"></i> <?php echo $row['category_name']; ?> <br>
                                <i class="fa-solid fa-money-bill"></i> $<?php echo $row['daily_rate']; ?> / day
                            </p>
                        </div>
                        
                        <div class="card-footer bg-white border-top-0">
                            <a href="booking.php?vehicle_id=<?php echo $row['vehicle_id']; ?>" class="btn btn-primary w-100">
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <p class="text-muted">No vehicles available in this category currently.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>