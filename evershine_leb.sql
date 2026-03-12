-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 12, 2026 at 12:57 PM
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
-- Database: `evershine_leb`
--

-- --------------------------------------------------------

--
-- Table structure for table `income_expense_items`
--

CREATE TABLE `income_expense_items` (
  `id` int(11) NOT NULL,
  `date_submitted` datetime NOT NULL DEFAULT current_timestamp(),
  `portfolio_name` varchar(200) DEFAULT NULL,
  `Month` varchar(200) NOT NULL,
  `year` int(11) NOT NULL,
  `Title` text NOT NULL,
  `expense_income` varchar(100) DEFAULT NULL,
  `cost_profit` int(11) DEFAULT NULL,
  `money_left` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `income_expense_items`
--

INSERT INTO `income_expense_items` (`id`, `date_submitted`, `portfolio_name`, `Month`, `year`, `Title`, `expense_income`, `cost_profit`, `money_left`) VALUES
(21, '2026-03-12 02:39:56', 'other', 'January', 2026, 'Starting The Finance', 'income', 20252, 20252.00),
(33, '2026-03-12 02:39:56', 'Parwaaz', 'February', 2026, 'Parwaaz Closing Finance, Cake etc', 'expense', 18342, 1910.00),
(34, '2026-03-12 02:39:56', 'Ipd', 'March', 2026, 'USB + HUB', 'expense', 600, 1310.00),
(36, '2026-03-12 02:39:56', 'Donation', 'March', 2026, 'Recieved', 'income', 100, 1410.00),
(39, '2026-03-12 02:39:56', 'Ipd', 'March', 2026, 'USB + HUB', 'expense', 400, 1010.00),
(43, '2026-03-12 05:22:55', 'Ipd', 'March', 2026, 'Gaming', 'income', 3300, 4310.00);

-- --------------------------------------------------------

--
-- Table structure for table `member_login`
--

CREATE TABLE `member_login` (
  `id` int(11) NOT NULL,
  `member_username` varchar(200) DEFAULT NULL,
  `member_password` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member_login`
--

INSERT INTO `member_login` (`id`, `member_username`, `member_password`) VALUES
(1, 'Ali Muhammad', 'Alimuhammad8!'),
(2, 'Haris Asim', 'haris786!'),
(3, 'Samreen Wali', 'samreen789!'),
(4, 'English Language Leap', 'elp764!'),
(5, 'Parwaaz', 'parwaaz493!'),
(6, 'Institute of Professional Development', 'ipd456!'),
(7, 'Quality Schooling Program', 'qsp789!'),
(8, 'Career And Scholarship Program', 'csp249!'),
(9, 'Moinuddin', 'moin239!'),
(10, 'Stem and Robotics', 'robotics246!');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `income_expense_items`
--
ALTER TABLE `income_expense_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `member_login`
--
ALTER TABLE `member_login`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `income_expense_items`
--
ALTER TABLE `income_expense_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `member_login`
--
ALTER TABLE `member_login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
