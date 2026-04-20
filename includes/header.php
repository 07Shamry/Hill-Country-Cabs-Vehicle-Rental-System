<?php
// Prevent session start errors
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Rental System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; }
        .navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); transition: transform 0.3s; }
        .card:hover { transform: translateY(-5px); }
        .btn-primary { background: linear-gradient(45deg, #0d6efd, #0043a8); border: none; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">
        <i class="fa-solid fa-car-side text-warning"></i> Hill Country Cabs Vehicle Rental
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="vehicle_list.php">Fleet</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <li class="nav-item"><a class="nav-link text-warning fw-bold" href="admin/dashboard.php">Admin Panel</a></li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="my_bookings.php">My Bookings</a></li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="btn btn-danger btn-sm rounded-pill ms-3 px-3" href="logout.php">Logout</a>
            </li>

        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
            <li class="nav-item">
                <a class="btn btn-warning btn-sm rounded-pill ms-2 px-3 text-dark fw-bold" href="register.php">Register</a>
            </li>
            
            <li class="nav-item ms-2">
                <a class="btn btn-outline-secondary btn-sm rounded-pill px-3" href="admin_login.php">
                    <i class="fa-solid fa-user-shield"></i> Admin
                </a>
            </li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>
<?php 
    // Only wrap the page in a container if it is NOT the homepage
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page !== 'index.php'): 
?>

<div class="container mt-4" style="min-height: 80vh;">
<?php endif; ?>