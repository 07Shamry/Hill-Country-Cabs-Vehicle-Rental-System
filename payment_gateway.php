<?php
$booking_id = $_GET['booking_id'];
$amount = $_GET['amount'];
$type = isset($_GET['type']) ? $_GET['type'] : 'final'; // 'deposit' or 'final'
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Secure Payment Gateway</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .payment-card { width: 400px; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .cc-icon { color: #0d6efd; font-size: 2em; margin-right: 10px; }
    </style>
</head>
<body>

<div class="payment-card">
    <div class="text-center mb-4">
        <i class="fa-brands fa-cc-visa cc-icon"></i>
        <i class="fa-brands fa-cc-mastercard cc-icon text-danger"></i>
        <i class="fa-brands fa-cc-amex cc-icon text-primary"></i>
        <h4 class="mt-3"><?php echo ($type == 'deposit') ? 'Deposit Payment' : 'Final Invoice Payment'; ?></h4>
        <p class="text-muted">Paying <strong class="text-dark">$<?php echo number_format($amount, 2); ?></strong></p>
    </div>

    <form action="process_payment.php" method="POST" id="payForm">
        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
        <input type="hidden" name="type" value="<?php echo $type; ?>"> <div class="mb-3">
            <label class="form-label small text-muted">CARD NUMBER</label>
            <input type="text" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19" required>
        </div>

        <div class="row">
            <div class="col-6 mb-3">
                <label class="form-label small text-muted">EXPIRY</label>
                <input type="text" class="form-control" placeholder="MM/YY" maxlength="5" required>
            </div>
            <div class="col-6 mb-3">
                <label class="form-label small text-muted">CVC</label>
                <input type="password" class="form-control" placeholder="123" maxlength="3" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small text-muted">CARDHOLDER NAME</label>
            <input type="text" class="form-control" placeholder="JOHN DOE" required>
        </div>

        <button type="submit" class="btn btn-dark w-100 py-2 fw-bold" id="payBtn">
            PAY $<?php echo $amount; ?> NOW
        </button>
    </form>
</div>

<script>
    document.getElementById('payForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('payBtn');
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Processing...';
        btn.disabled = true;
    });
</script>

</body>
</html>