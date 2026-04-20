<?php
session_start();
include 'includes/db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // 1. Check if user exists
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // 2. Verify Password
        if (password_verify($password, $user['password_hash'])) {
            // 3. Set Session Variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            // 4. Redirect based on Role
            if ($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid Password!";
        }
    } else {
        $error = "User not found with that email!";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        
        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success text-center">Account created! Please login.</div>
        <?php endif; ?>

        <div class="card mt-5">
            <div class="card-header bg-success text-white text-center">
                <h3>Login</h3>
            </div>
            <div class="card-body">
                
                <?php if($error != ""): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Login</button>
                </form>
            </div>
            <div class="card-footer text-center">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>