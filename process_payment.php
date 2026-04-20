<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (!isset($_POST['booking_id']) || empty($_POST['booking_id'])) {
        die("Error: Invalid Payment Request. No Booking ID found.");
    }

    $booking_id = (int)$_POST['booking_id'];
    $payment_type = $_POST['type'] ?? 'final';
    
    // Determining logic based on payment type
    if ($payment_type == 'deposit') {
        // Deposit Paid -> Send to Admin for Approval
        $sql = "UPDATE bookings SET booking_status='Pending' WHERE booking_id=$booking_id";
        $alert_title = "Deposit Paid!";
        $alert_text = "Your $15 deposit was received. The Admin will review your booking shortly.";
    } else {
        // Final Bill Paid -> Close the loop
        $sql = "UPDATE bookings SET booking_status='Completed' WHERE booking_id=$booking_id";
        $alert_title = "Payment Successful!";
        $alert_text = "Thank you! Your booking is now officially completed.";
    }
    
    if ($conn->query($sql) === TRUE) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <style>body { background-color: #f4f7f6; }</style>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: '$alert_title',
                    text: '$alert_text',
                    icon: 'success',
                    confirmButtonColor: '#198754'
                }).then(() => {
                    window.location.href = 'my_bookings.php';
                });
            </script>
        </body>
        </html>";
    } else {
        echo "Database Error: " . $conn->error;
    }
}
?>