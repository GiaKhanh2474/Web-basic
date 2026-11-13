-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 13, 2026 lúc 07:25 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu `laptopstore`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `laptopstore`
--

CREATE TABLE `laptopstore` (
  `Id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `laptopstore`
--

INSERT INTO `laptopstore` (`Id`, `name`, `brand`, `price`, `description`, `image`, `quantity`) VALUES
(1, 'ASUS Vivobook 15', 'ASUS', 13490000.00, '', 'images/laptop-asus.jpg', 10),
(2, 'Laptop Lenovo IdeaPad Slim 3', 'Lenovo', 12990000.00, '', 'images/laptop-lenovo.jpg', 8),
(3, 'Laptop Acer Aspire 5', 'Acer', 10990000.00, '', 'images/laptop-acer.jpg', 14),
(4, 'MacBook Pro 14 inch', 'MacBook', 53000000.00, '', 'images/MacBook-Pro-14-inch.png', 11),
(5, 'Laptop MSI Modern 15', 'MSI', 18590000.00, '', 'images/Laptop-MSI-Modern-15.png', 6),
(6, 'Laptop HP Pavilion 15', 'HP', 12490000.00, '', 'images/laptop-hp.jpg', 16),
(7, 'Dell Inspiron 15 3530', 'DELL', 14990000.00, '', 'images/laptop-dell.jpg', 8),
(8, 'Gigabyte Gaming A16', 'GIGABYTE', 22400000.00, '', 'images/Gigabyte-Gaming-A16.png', 9),
(9, 'Laptop LG gram Book 15', 'LG', 26990000.00, '', 'images/Laptop-LG gram-Book-15.png', 8),
(10, 'Microsoft Surface Laptop 4', 'MICROSOFT SURFACE', 16000000.00, '', 'images/Microsoft-Surface-Laptop-4.png', 10),
(11, 'Thunderobot T-Book 15A', 'THUNDETOBOT', 12390000.00, '', 'images/Thunderobot-T-Book-15A.png', 15),
(12, 'Colorful X15 XS 22', 'COLORFUL', 13440000.00, '', 'images/Colorful-X15-XS-22.png', 13);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `laptopstore`
--
ALTER TABLE `laptopstore`
  ADD PRIMARY KEY (`Id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `laptopstore`
--
ALTER TABLE `laptopstore`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
