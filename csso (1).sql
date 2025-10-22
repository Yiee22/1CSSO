-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2025 at 10:23 AM
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
-- Database: `csso`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `students_id` int(11) NOT NULL,
  `registration_no` varchar(50) NOT NULL,
  `event_name` varchar(100) NOT NULL,
  `event_date` date NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `amLogin` time DEFAULT NULL,
  `amLogout` time DEFAULT NULL,
  `pmLogin` time DEFAULT NULL,
  `pmLogout` time DEFAULT NULL,
  `ExcuseLetter` enum('Yes','No') DEFAULT 'No',
  `TotalPenalty` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comservice`
--

CREATE TABLE `comservice` (
  `service_id` int(11) NOT NULL,
  `fines_id` int(11) NOT NULL,
  `students_id` int(11) NOT NULL,
  `penalty_amount` decimal(10,2) DEFAULT NULL,
  `total_hours` int(11) DEFAULT NULL,
  `hours_completed` int(11) DEFAULT NULL,
  `service_date` date DEFAULT NULL,
  `status` enum('Ongoing','Completed') DEFAULT 'Ongoing',
  `balance` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `educational_background`
--

CREATE TABLE `educational_background` (
  `edu_id` int(11) NOT NULL,
  `students_id` int(11) DEFAULT NULL,
  `elementary` varchar(150) DEFAULT NULL,
  `elem_year_grad` date DEFAULT NULL,
  `elem_received` varchar(100) DEFAULT NULL,
  `junior_high` varchar(150) DEFAULT NULL,
  `jr_high_grad` date DEFAULT NULL,
  `jr_received` varchar(100) DEFAULT NULL,
  `senior_high` varchar(150) DEFAULT NULL,
  `sr_high_grad` date DEFAULT NULL,
  `sr_received` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `event_Name` varchar(100) NOT NULL,
  `event_Date` date NOT NULL,
  `location` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`event_Name`, `event_Date`, `location`) VALUES
('Randy', '2025-10-21', 'CPSC');

-- --------------------------------------------------------

--
-- Table structure for table `family_background`
--

CREATE TABLE `family_background` (
  `fam_id` int(11) NOT NULL,
  `students_id` int(11) NOT NULL,
  `father_name` varchar(150) DEFAULT NULL,
  `father_occupation` varchar(150) DEFAULT NULL,
  `mother_name` varchar(150) DEFAULT NULL,
  `mother_occupation` varchar(150) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `siblings_count` int(11) DEFAULT NULL,
  `guardian_name` varchar(150) DEFAULT NULL,
  `guardian_occupation` varchar(150) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `street` varchar(150) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `municipality` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `zipcode` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fines`
--

CREATE TABLE `fines` (
  `fines_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `students_id` int(11) NOT NULL,
  `registration_no` varchar(50) NOT NULL,
  `attendance_id` int(11) NOT NULL,
  `event_name` varchar(100) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `PenaltyAmount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fines_payments`
--

CREATE TABLE `fines_payments` (
  `payment_id` int(11) NOT NULL,
  `fines_id` int(11) NOT NULL,
  `students_id` int(11) NOT NULL,
  `event_name` varchar(100) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `penalty_amount` decimal(10,2) DEFAULT NULL,
  `payment_type` enum('Cash','Gcash','Other') DEFAULT 'Cash',
  `balance` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('Paid','Unpaid','Partial Paid') DEFAULT 'Unpaid',
  `payment_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration`
--

CREATE TABLE `registration` (
  `registration_no` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `students_id` int(11) NOT NULL,
  `registration_date` date NOT NULL,
  `semester` enum('First Semester','Second Semester') NOT NULL,
  `membership_fee` decimal(10,2) DEFAULT 100.00,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_type` enum('Cash','Gcash','Other') DEFAULT 'Cash',
  `payment_status` enum('Paid','Unpaid','Partial Paid') DEFAULT 'Unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_profile`
--

CREATE TABLE `student_profile` (
  `students_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `FirstName` varchar(100) DEFAULT NULL,
  `LastName` varchar(100) DEFAULT NULL,
  `MI` varchar(5) DEFAULT NULL,
  `Suffix` enum('Sr','Jr','III','IV','V') DEFAULT NULL,
  `Course` enum('BSIT','BSCS') DEFAULT NULL,
  `YearLevel` enum('1stYear','2ndYear','3rdYear','4thYear') DEFAULT NULL,
  `Section` enum('BSIT 1A','BSIT 1B','BSIT 2A','BSIT 2B','BSIT 3A','BSIT 3B','BSIT 4A','BSIT 4B','BSCS 1A','BSCS 1B','BSCS 2A','BSCS 2B','BSCS 3A','BSCS 3B','BSCS 4A','BSCS 4B') NOT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `Gender` enum('Male','Female','Other') DEFAULT NULL,
  `DOB` date DEFAULT NULL,
  `Age` int(11) DEFAULT NULL,
  `Religion` varchar(100) DEFAULT NULL,
  `EmailAddress` varchar(150) DEFAULT NULL,
  `Street` varchar(150) DEFAULT NULL,
  `Barangay` varchar(100) DEFAULT NULL,
  `Municipality` varchar(100) DEFAULT NULL,
  `Province` varchar(100) DEFAULT NULL,
  `Zipcode` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `usertype` enum('Governor','Vice Governor','Secretary','Auditor','Treasurer','Social Manager','Senator') NOT NULL,
  `date_in` date DEFAULT curdate(),
  `time_in` time DEFAULT curtime(),
  `status` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `username`, `password`, `usertype`, `date_in`, `time_in`, `status`) VALUES
(35, 'way', 'way', 'way', '$2y$10$bwF5.GrYFDIGt5r77ailxuR7y2R3mxGCALqgYKofo/rQbx9VJ18eC', 'Governor', '2025-10-22', '16:09:53', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `fk_user_att` (`UserID`),
  ADD KEY `fk_student_att` (`students_id`),
  ADD KEY `fk_reg_att` (`registration_no`);

--
-- Indexes for table `comservice`
--
ALTER TABLE `comservice`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `fines_id` (`fines_id`),
  ADD KEY `students_id` (`students_id`);

--
-- Indexes for table `educational_background`
--
ALTER TABLE `educational_background`
  ADD PRIMARY KEY (`edu_id`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`event_Name`);

--
-- Indexes for table `family_background`
--
ALTER TABLE `family_background`
  ADD PRIMARY KEY (`fam_id`),
  ADD KEY `fk_student_family` (`students_id`);

--
-- Indexes for table `fines`
--
ALTER TABLE `fines`
  ADD PRIMARY KEY (`fines_id`),
  ADD KEY `fk_user_fine` (`user_id`),
  ADD KEY `fk_student_fine` (`students_id`),
  ADD KEY `fk_reg_fine` (`registration_no`),
  ADD KEY `fk_att_fine` (`attendance_id`);

--
-- Indexes for table `fines_payments`
--
ALTER TABLE `fines_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fines_id` (`fines_id`),
  ADD KEY `students_id` (`students_id`);

--
-- Indexes for table `registration`
--
ALTER TABLE `registration`
  ADD PRIMARY KEY (`registration_no`),
  ADD KEY `fk_user_reg` (`user_id`),
  ADD KEY `fk_student_reg` (`students_id`);

--
-- Indexes for table `student_profile`
--
ALTER TABLE `student_profile`
  ADD PRIMARY KEY (`students_id`),
  ADD KEY `fk_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comservice`
--
ALTER TABLE `comservice`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `educational_background`
--
ALTER TABLE `educational_background`
  MODIFY `edu_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `family_background`
--
ALTER TABLE `family_background`
  MODIFY `fam_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fines`
--
ALTER TABLE `fines`
  MODIFY `fines_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fines_payments`
--
ALTER TABLE `fines_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_profile`
--
ALTER TABLE `student_profile`
  MODIFY `students_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=333334;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_reg_att` FOREIGN KEY (`registration_no`) REFERENCES `registration` (`registration_no`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student_att` FOREIGN KEY (`students_id`) REFERENCES `student_profile` (`students_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_att` FOREIGN KEY (`UserID`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `comservice`
--
ALTER TABLE `comservice`
  ADD CONSTRAINT `comservice_ibfk_1` FOREIGN KEY (`fines_id`) REFERENCES `fines` (`fines_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comservice_ibfk_2` FOREIGN KEY (`students_id`) REFERENCES `student_profile` (`students_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `family_background`
--
ALTER TABLE `family_background`
  ADD CONSTRAINT `fk_student_family` FOREIGN KEY (`students_id`) REFERENCES `student_profile` (`students_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `fines`
--
ALTER TABLE `fines`
  ADD CONSTRAINT `fk_att_fine` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`attendance_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reg_fine` FOREIGN KEY (`registration_no`) REFERENCES `registration` (`registration_no`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student_fine` FOREIGN KEY (`students_id`) REFERENCES `student_profile` (`students_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_fine` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `fines_payments`
--
ALTER TABLE `fines_payments`
  ADD CONSTRAINT `fines_payments_ibfk_1` FOREIGN KEY (`fines_id`) REFERENCES `fines` (`fines_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fines_payments_ibfk_2` FOREIGN KEY (`students_id`) REFERENCES `fines` (`students_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `registration`
--
ALTER TABLE `registration`
  ADD CONSTRAINT `fk_student_reg` FOREIGN KEY (`students_id`) REFERENCES `student_profile` (`students_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_reg` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_profile`
--
ALTER TABLE `student_profile`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
