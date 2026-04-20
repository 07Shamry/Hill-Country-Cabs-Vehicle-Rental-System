<?php
session_start();
include '../includes/db_connect.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";

// --- HANDLE FORM SUBMISSION (ADD VEHICLE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_vehicle'])) {
    
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $license_plate = $_POST['license_plate'];
    $daily_rate = $_POST['daily_rate'];
    $category_id = $_POST['category_id'];
    
    // Image Upload Logic
    $target_dir = "../uploads/vehicles/";
    $image_name = basename($_FILES["vehicle_image"]["name"]);
    $target_file = $target_dir . time() . "_" . $image_name; // Rename file with timestamp to avoid duplicates
    
    if (move_uploaded_file($_FILES["vehicle_image"]["tmp_name"], $target_file)) {
        // File upload success, now insert into DB
        // Note: We store the path relative to the website root, so remove the first "../"
        $db_image_path = "uploads/vehicles/" . time() . "_" . $image_name;
        
        $sql = "INSERT INTO vehicles (brand, model, license_plate, daily_rate, category_id, image_url, status) 
                VALUES ('$brand', '$model', '$license_plate', '$daily_rate', '$category_id', '$db_image_path', 'Available')";
        
        if ($conn->query($sql) === TRUE) {
            $message = "Vehicle added successfully!";
        } else {
            $message = "Database Error: " . $conn->error;
        }
    } else {
        $message = "Error uploading image.";
    }
}

// --- HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM vehicles WHERE vehicle_id=$id");
    header("Location: manage_vehicles.php"); // Refresh page
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Vehicles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Manage Vehicles</h2>
    <a href="dashboard.php" class="btn btn-secondary mb-3">&larr; Back to Dashboard</a>

    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="card mb-5">
        <div class="card-header bg-dark text-white">Add New Vehicle</div>
        <div class="card-body">
            <form action="manage_vehicles.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-3">
                        <label>Brand (e.g. Toyota)</label>
                        <input type="text" name="brand" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Model (e.g. Corolla)</label>
                        <input type="text" name="model" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>License Plate</label>
                        <input type="text" name="license_plate" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Daily Rate ($)</label>
                        <input type="number" name="daily_rate" class="form-control" required>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label>Category</label>
                        <select name="category_id" class="form-control">
                            <option value="1">Car</option>
                            <option value="2">Van</option>
                            <option value="3">Motorbike</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Vehicle Image</label>
                        <input type="file" name="vehicle_image" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <br>
                        <button type="submit" name="add_vehicle" class="btn btn-success w-100">Add Vehicle</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <h4>Current Fleet</h4>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Image</th>
                <th>Vehicle</th>
                <th>License</th>
                <th>Rate/Day</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM vehicles");
            while ($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td><img src="../<?php echo $row['image_url']; ?>" width="80"></td>
                <td><?php echo $row['brand'] . " " . $row['model']; ?></td>
                <td><?php echo $row['license_plate']; ?></td>
                <td>$<?php echo $row['daily_rate']; ?></td>
                <td>
                    <?php if($row['status'] == 'Available') echo '<span class="badge bg-success">Available</span>'; ?>
                    <?php if($row['status'] == 'Rented') echo '<span class="badge bg-danger">Rented</span>'; ?>
                </td>
                <td>
                    <a href="manage_vehicles.php?delete=<?php echo $row['vehicle_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>