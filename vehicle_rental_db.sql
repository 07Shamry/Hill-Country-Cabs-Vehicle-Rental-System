-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2026 at 06:54 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vehicle_rental_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password_hash`, `created_at`) VALUES
(2, 'admin', '$2y$10$.xg3TFoaH7l3xseG6kIfbu11DH97cqgA5VNC3sOS15yudeb90dfoG', '2026-01-27 05:50:05');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `security_deposit_held` decimal(10,2) DEFAULT 0.00,
  `booking_status` enum('Pending','Confirmed','Active','Completed','Cancelled','Overdue','Payment Due') DEFAULT 'Pending',
  `is_with_driver` tinyint(1) DEFAULT 0,
  `driver_name` varchar(100) DEFAULT NULL,
  `driver_phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `actual_km` int(11) DEFAULT 0,
  `estimated_km` int(11) DEFAULT 0,
  `damage_reason` varchar(255) DEFAULT NULL,
  `pickup_time` time DEFAULT NULL,
  `dropoff_time` time DEFAULT NULL,
  `pickup_location` varchar(255) DEFAULT 'Showroom',
  `dropoff_location` varchar(255) DEFAULT 'Showroom'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `vehicle_id`, `start_date`, `end_date`, `total_price`, `security_deposit_held`, `booking_status`, `is_with_driver`, `driver_name`, `driver_phone`, `created_at`, `actual_km`, `estimated_km`, `damage_reason`, `pickup_time`, `dropoff_time`, `pickup_location`, `dropoff_location`) VALUES
(1, 1, 1, '2026-01-28', '2026-01-29', 2937.00, 0.00, 'Payment Due', 1, 'Jackie Chan', '0777654321', '2026-01-27 06:09:59', 150, 0, NULL, NULL, NULL, 'Showroom', 'Showroom'),
(2, 2, 1, '2026-01-30', '2026-01-31', 150.00, 0.00, 'Cancelled', 0, NULL, NULL, '2026-01-27 06:58:58', 0, 0, NULL, NULL, NULL, 'Showroom', 'Showroom'),
(3, 2, 1, '2026-01-27', '2026-01-27', 2937.00, 0.00, 'Completed', 0, NULL, NULL, '2026-01-27 07:00:53', 150, 0, NULL, NULL, NULL, 'Showroom', 'Showroom'),
(4, 2, 1, '2026-02-07', '2026-02-02', 6957.50, 0.00, '', 1, 'Bruce Lee', '0778885232', '2026-01-29 04:22:26', 900, 0, NULL, NULL, NULL, 'Showroom', 'Showroom'),
(5, 2, 1, '2026-03-01', '2026-02-02', 4605.70, 0.00, 'Completed', 0, NULL, NULL, '2026-01-29 05:10:52', 260, 250, 'Scratch', NULL, NULL, 'Showroom', 'Showroom'),
(6, 3, 1, '2026-04-08', '2026-04-11', 750.20, 0.00, 'Completed', 1, 'Bruce Lee', '0777654321', '2026-01-29 05:36:14', 320, 320, 'scratch', NULL, NULL, 'Showroom', 'Showroom'),
(7, 3, 1, '2026-02-17', '2026-02-18', 330.00, 0.00, 'Completed', 0, NULL, NULL, '2026-01-29 05:54:01', 200, 200, '', NULL, NULL, 'Showroom', 'Showroom'),
(8, 3, 1, '2026-01-31', '2026-01-31', 150.00, 0.00, 'Cancelled', 0, NULL, NULL, '2026-01-30 02:16:07', 0, 300, NULL, '09:45:00', '07:47:00', 'Showroom', 'Showroom'),
(9, 3, 1, '2026-01-31', '2026-02-01', 150.00, 0.00, 'Cancelled', 0, NULL, NULL, '2026-01-30 02:38:55', 0, 200, NULL, '11:08:00', '08:10:00', 'Showroom', 'Showroom'),
(10, 3, 1, '2026-02-01', '2026-02-01', 250.00, 0.00, 'Cancelled', 0, NULL, NULL, '2026-01-30 02:50:56', 0, 102, NULL, '09:20:00', '12:20:00', 'Showroom', 'Showroom'),
(11, 3, 1, '2026-01-31', '2026-01-31', 150.00, 0.00, 'Cancelled', 0, NULL, NULL, '2026-01-30 02:51:59', 0, 100, NULL, '10:21:00', '08:21:00', 'Showroom', 'Showroom');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `base_driver_fee` decimal(10,2) DEFAULT 0.00,
  `free_km_per_day` int(11) DEFAULT 100,
  `extra_km_price` decimal(10,2) DEFAULT 50.00,
  `tax_rate` decimal(5,2) DEFAULT 0.10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `base_driver_fee`, `free_km_per_day`, `extra_km_price`, `tax_rate`) VALUES
(1, 'Car', 20.00, 100, 50.00, 0.10),
(2, 'Van', 30.00, 100, 50.00, 0.10),
(3, 'MotorBike', 0.00, 100, 50.00, 0.10);

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_responses`
--

CREATE TABLE `chatbot_responses` (
  `response_id` int(11) NOT NULL,
  `keyword` varchar(50) NOT NULL,
  `response_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chatbot_responses`
--

INSERT INTO `chatbot_responses` (`response_id`, `keyword`, `response_text`) VALUES
(1, 'hello', 'Welcome to EcoRide! How can I help you today?'),
(2, 'price', 'Our daily rates vary by vehicle. Bikes start at $10, Cars at $45.'),
(3, 'driver', 'Yes! We offer professional drivers. You can select \"With Driver\" during booking.'),
(4, 'deposit', 'A refundable security deposit is required for all rentals.'),
(5, 'contact', 'You can reach our 24/7 support at +94 77 123 4567.');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_type` enum('Deposit','Rental Fee','Late Fine','Damage Fee') NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `loyalty_points` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_type` enum('Local','Foreign') NOT NULL DEFAULT 'Local',
  `identity_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `phone_number`, `role`, `loyalty_points`, `created_at`, `customer_type`, `identity_no`) VALUES
(1, 'Bruce Wayne', 'batsy@gmail.com', '$2y$10$XOgfOfwYn5064JOLSvkZqOl1SvWo9S7cTpb/8prb7lCciKXbjjwVq', '0771234567', 'customer', 0, '2026-01-27 06:01:56', 'Local', NULL),
(2, 'Clark Kent', 'sup@gmail.com', '$2y$10$r7C2C.G2O80PIbyIVUgy9.NDh16gTkT0bay6jP2qtimTtVJoIpfZS', '0777894561', 'customer', 0, '2026-01-27 06:58:15', 'Local', '200465451232'),
(3, 'Flash', 'flash@gmail.com', '$2y$10$SjnqXEy5xOGs/nvU4Ye7MuEZ4LUktVAS0uUog4ELABt15z2ox47Ie', '0114445555', 'customer', 0, '2026-01-29 05:35:03', 'Foreign', 'N1234586');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `vehicle_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `brand` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `license_plate` varchar(20) NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `security_deposit` decimal(10,2) DEFAULT 0.00,
  `status` enum('Available','Rented','Maintenance') DEFAULT 'Available',
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `category_id`, `brand`, `model`, `license_plate`, `daily_rate`, `security_deposit`, `status`, `image_url`) VALUES
(1, 1, 'Toyota', 'Corolla 2020', '123456', 150.00, 0.00, 'Available', 'uploads/vehicles/1769493954_toyota corolla 2020.png'),
(3, 2, 'Toyota', 'Hiace KDH', '5564681', 40.00, 0.00, 'Available', 'uploads/vehicles/1769743482_Toyota Hiace KDH.jpg'),
(4, 3, 'Suzuki', 'Gixxer AF', '98765431', 30.00, 0.00, 'Available', 'uploads/vehicles/1769743523_suzuki-gixxer-sf-250.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_damages`
--

CREATE TABLE `vehicle_damages` (
  `report_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `reported_by` enum('Admin','Customer') NOT NULL,
  `description` text NOT NULL,
  `evidence_image_url` varchar(255) DEFAULT NULL,
  `repair_cost` decimal(10,2) DEFAULT 0.00,
  `is_paid` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_damages`
--

INSERT INTO `vehicle_damages` (`report_id`, `booking_id`, `reported_by`, `description`, `evidence_image_url`, `repair_cost`, `is_paid`, `created_at`) VALUES
(1, 5, 'Admin', 'Scratch', NULL, 2.00, 0, '2026-01-29 05:12:47'),
(2, 6, 'Admin', 'scratch', NULL, 2.00, 0, '2026-01-29 05:37:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `chatbot_responses`
--
ALTER TABLE `chatbot_responses`
  ADD PRIMARY KEY (`response_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `identity_no` (`identity_no`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`vehicle_id`),
  ADD UNIQUE KEY `license_plate` (`license_plate`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `vehicle_damages`
--
ALTER TABLE `vehicle_damages`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `chatbot_responses`
--
ALTER TABLE `chatbot_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `vehicle_damages`
--
ALTER TABLE `vehicle_damages`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`);

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `vehicle_damages`
--
ALTER TABLE `vehicle_damages`
  ADD CONSTRAINT `vehicle_damages_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
