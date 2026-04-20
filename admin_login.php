<?php
session_start();
include 'includes/db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    
    $sql = "SELECT * FROM admins WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        
        if (password_verify($password, $admin['password_hash'])) {
            
            
            $_SESSION['user_id'] = $admin['admin_id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role'] = 'admin'; 

            header("Location: admin/dashboard.php");
            exit();
        } else {
            $error = "Invalid Admin Password!";
        }
    } else {
        $error = "Admin Username not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login - Vehicle Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #212529; color: white; }
        .card { background-color: #343a40; border: 1px solid #495057; }
        .form-control { background-color: #495057; border-color: #6c757d; color: white; }
        .form-control:focus { background-color: #495057; color: white; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center" style="height: 100vh;">

    <div class="col-md-4">
        <div class="card shadow-lg">
            <div class="card-header text-center bg-danger text-white">
                <h3><i class="fa-solid fa-lock"></i> ADMINISTRATOR ACCESS</h3>
            </div>
            <div class="card-body p-5">
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="admin_login.php" method="POST">
                    <div class="mb-4">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" placeholder="admin" required>
                    </div>
                    <div class="mb-4">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••" required>
                    </div>
                    <button type="submit" class="btn btn-danger w-100 py-2">SECURE LOGIN</button>
                </form>
            </div>
            <div class="card-footer text-center text-muted">
                <a href="index.php" class="text-secondary text-decoration-none">&larr; Back to Website</a>
            </div>
        </div>
    </div>

</body>
</html>