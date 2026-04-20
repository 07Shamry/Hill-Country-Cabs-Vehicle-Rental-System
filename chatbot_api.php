<?php
// chatbot_api.php
header('Content-Type: application/json');

// Get the user's message
$data = json_decode(file_get_contents('php://input'), true);
$message = strtolower(trim($data['message'] ?? ''));

// Default fallback response
$response = "I'm a virtual assistant! I can answer questions about our **vehicles**, **deposits**, **drivers**, **mileage limits**, or **documents needed**. What would you like to know?";

// 1. Greetings
if (preg_match("/\b(hi|hello|hey|greetings)\b/i", $message)) {
    $response = "Hello there! 👋 Welcome to Premium Vehicle Rentals. How can I help you today?";
} 
// 2. Deposit & Payments
elseif (preg_match("/\b(deposit|pay|cost|fee|price|card)\b/i", $message)) {
    $response = "To secure a booking, we require a small **$15 security deposit** upfront. The remaining balance (including rent and excess mileage) is calculated when you return the vehicle.";
} 
// 3. Location & Kandy Rule
elseif (preg_match("/\b(kandy|colombo|location|where|address|showroom)\b/i", $message)) {
    $response = "Currently, our premium rental services are **exclusively available in the Kandy region**. You can collect vehicles from our main showroom at 123 Main Street, Kandy.";
} 
// 4. Driver vs Self-Drive
elseif (preg_match("/\b(driver|drive|chauffeur|self)\b/i", $message)) {
    $response = "Yes! You can choose a **Self-Drive** option, or you can hire one of our professional drivers for an additional daily fee. You can select this during checkout.";
} 
// 5. Time, Notice, & Operating Hours
elseif (preg_match("/\b(time|hours|notice|when|open|close)\b/i", $message)) {
    $response = "Our pickup and drop-off hours are between **8 AM and 8 PM**. Please note that all bookings require at least **24 hours advance notice**.";
}
// 6. Cancellations
elseif (preg_match("/\b(cancel|refund)\b/i", $message)) {
    $response = "You can cancel a 'Pending' request directly from your 'My Bookings' page. Please contact our showroom directly for inquiries about deposit refunds.";
}
// 7. NEW: Document Requirements (Matches your database logic!)
elseif (preg_match("/\b(document|id|nic|passport|license|verification)\b/i", $message)) {
    $response = "Local customers must provide a valid **NIC** (9 or 12 digits), and foreign customers need a valid **Passport**. A valid driving license is also required if you choose the Self-Drive option.";
}
// 8. NEW: Fleet / Vehicle Types
elseif (preg_match("/\b(cars|vans|bikes|motorbikes|fleet|vehicles|models)\b/i", $message)) {
    $response = "We offer a diverse fleet including **Luxury Cars**, **Vans**, and **Motorbikes**. Click on the 'Fleet' tab in the navigation bar to browse our available vehicles and daily rates!";
}
// 9. NEW: Mileage Rules
elseif (preg_match("/\b(mileage|limit|km|distance|extra|excess)\b/i", $message)) {
    $response = "Each vehicle category has a **free daily mileage limit** (e.g., 100km/day). If you exceed this limit during your trip, an 'Extra Mileage Charge' will be applied per additional kilometer.";
}
// 10. NEW: Damage Policy
elseif (preg_match("/\b(damage|scratch|accident|repair|crash)\b/i", $message)) {
    $response = "All vehicles are inspected upon return. Any damage repair costs will be fairly evaluated by our Admin and added to your final invoice.";
}
// 11. NEW: Contact / Support
elseif (preg_match("/\b(contact|phone|call|email|number|help)\b/i", $message)) {
    $response = "You can reach our Kandy showroom support team at **+94 77 123 4567** or drop by our office during working hours.";
}

// Send response back to the chat widget
echo json_encode(['reply' => $response]);
?>