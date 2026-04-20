<?php
session_start();
include 'includes/db_connect.php';

$message = "";
$success = false; // NEW: Added a flag to track successful registration

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $customer_type = $_POST['customer_type'];
    $identity_no = $conn->real_escape_string($_POST['identity_no']);

    // --- 1. SERVER-SIDE STRICT VALIDATION ---
    $valid_id = false;
    if ($customer_type == 'Local') {
        // Regex: 9 Digits + V/X  OR  12 Digits
        if (preg_match('/^(\d{9}[VvXx]|\d{12})$/', $identity_no)) {
            $valid_id = true;
        } else {
            $message = "Invalid NIC Format! Use 123456789V or 199012345678.";
        }
    } else {
        // Regex: Letter + 6 or more digits
        if (preg_match('/^[A-Za-z]\d{6,}$/', $identity_no)) {
            $valid_id = true;
        } else {
            $message = "Invalid Passport Format! Must start with a letter followed by numbers.";
        }
    }

    if ($valid_id) {
        if ($password !== $confirm_password) {
            $message = "Passwords do not match!";
        } else {
            // Check for duplicate Email OR Identity Number
            $check = $conn->query("SELECT * FROM users WHERE email='$email' OR identity_no='$identity_no'");
            if ($check->num_rows > 0) {
                $message = "Error: Email or ID Number already registered!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO users (full_name, email, password_hash, phone_number, role, customer_type, identity_no) 
                        VALUES ('$full_name', '$email', '$hashed_password', '$phone', 'customer', '$customer_type', '$identity_no')";

                if ($conn->query($sql) === TRUE) {
                    $success = true; // Set flag to true instead of echoing script early
                } else {
                    $message = "Database Error: " . $conn->error;
                }
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<?php if ($success): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Registration Successful!',
                text: 'Your account has been created. Please log in to continue.',
                icon: 'success',
                confirmButtonColor: '#198754',
                confirmButtonText: 'Go to Login'
            }).then(() => {
                // Redirects to login page and triggers the green alert bar you already have there
                window.location.href = 'login.php?registered=true';
            });
        });
    </script>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mt-5 shadow-lg border-0">
            <div class="card-header bg-dark text-white text-center">
                <h3><i class="fa-solid fa-user-plus"></i> Join Hill Country Cabs Vehicle Rental</h3>
            </div>
            <div class="card-body p-4">
                
                <?php if($message != ""): ?>
                    <div class="alert alert-danger"><?php echo $message; ?></div>
                <?php endif; ?>

                <form action="register.php" method="POST" id="regForm">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control" required placeholder="John Doe">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="form-control" required placeholder="0771234567" pattern="\d{10}" title="10 digit number">
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded mb-3 border">
                        <label class="fw-bold mb-2">Identity Verification</label>
                        <div class="row">
                            <div class="col-md-4">
                                <select name="customer_type" id="customer_type" class="form-select" onchange="updateIdField()">
                                    <option value="Local">Local (NIC)</option>
                                    <option value="Foreign">Foreign (Passport)</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="identity_no" id="identity_no" class="form-control" required placeholder="Enter NIC (e.g. 123456789V)">
                                <small class="text-muted" id="id_hint">Format: 9 digits+V or 12 digits</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">CREATE ACCOUNT</button>
                </form>
            </div>
            <div class="card-footer text-center">
                Already have an account? <a href="login.php" class="fw-bold">Login here</a>
            </div>
        </div>
    </div>
</div>

<script>
    function updateIdField() {
        const type = document.getElementById("customer_type").value;
        const input = document.getElementById("identity_no");
        const hint = document.getElementById("id_hint");

        if (type === "Local") {
            input.placeholder = "Enter NIC (e.g. 199012345678)";
            hint.innerText = "Format: 9 digits+V or 12 digits";
            input.setAttribute("pattern", "(\\d{9}[VvXx]|\\d{12})");
        } else {
            input.placeholder = "Enter Passport No (e.g. N123456)";
            hint.innerText = "Format: Starts with letter + numbers";
            input.setAttribute("pattern", "[A-Za-z]\\d{6,}");
        }
    }
</script>

<?php include 'includes/footer.php'; ?>