-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 16, 2025 at 05:21 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hardwaredeals`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `uEmail` varchar(100) NOT NULL,
  `productID` int NOT NULL,
  `qty` int NOT NULL,
  PRIMARY KEY (`productID`,`uEmail`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`uEmail`, `productID`, `qty`) VALUES
('saman@gmail.com', 8, 1),
('mmm@mm.com', 9, 1),
('jayas@jayahardwaress.com', 1, 2),
('behan@ahawala.com', 1, 5),
('jaya@jayahardwares.com', 7, 1),
('mmm@mm.com', 8, 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `categoryID` int NOT NULL AUTO_INCREMENT,
  `categoryName` varchar(100) NOT NULL,
  PRIMARY KEY (`categoryID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`categoryID`, `categoryName`) VALUES
(1, 'General');

-- --------------------------------------------------------

--
-- Table structure for table `ordercommited`
--

DROP TABLE IF EXISTS `ordercommited`;
CREATE TABLE IF NOT EXISTS `ordercommited` (
  `uEmail` varchar(100) NOT NULL,
  `orderID` int NOT NULL,
  `productID` int NOT NULL,
  `qty` int NOT NULL,
  `status` varchar(100) NOT NULL,
  PRIMARY KEY (`orderID`,`productID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ordercommited`
--

INSERT INTO `ordercommited` (`uEmail`, `orderID`, `productID`, `qty`, `status`) VALUES
('nimalhardwares@comcom.com', 2, 9, 2, 'pending'),
('nimalhardwares@comcom.com', 1, 11, 1, 'shipped'),
('nimalhardwares@comcom.com', 3, 11, 1, 'pending'),
('kamal@gmail.com', 4, 9, 1, 'pending'),
('kamal@gmail.com', 4, 11, 1, 'pending'),
('kamal@gmail.com', 4, 1, 1, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `orderID` int NOT NULL AUTO_INCREMENT,
  `productID` int NOT NULL,
  `subtotal` int NOT NULL,
  `uEmail` varchar(100) NOT NULL,
  `shippingAddress` varchar(500) NOT NULL,
  `status` varchar(100) NOT NULL,
  `orderDate` date NOT NULL,
  PRIMARY KEY (`orderID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `productimgs`
--

DROP TABLE IF EXISTS `productimgs`;
CREATE TABLE IF NOT EXISTS `productimgs` (
  `productID` int NOT NULL,
  `imgURL` varchar(500) NOT NULL,
  `imgID` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`imgID`,`productID`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `productimgs`
--

INSERT INTO `productimgs` (`productID`, `imgURL`, `imgID`) VALUES
(1, 'drilldemo/2.jpg', 1),
(1, 'drilldemo/3.jpg', 2),
(1, 'drilldemo/4.jpg', 3),
(7, 'uploads/1765882076_img_0_694138dc66a94.webp', 4),
(7, 'uploads/1765882076_img_1_694138dc66c6f.webp', 5),
(7, 'uploads/1765882076_img_2_694138dc66e22.webp', 6),
(8, 'uploads/1765884698_img_0_6941431ab7e7a.jpg', 7),
(8, 'uploads/1765884698_img_1_6941431ab819d.jpg', 8),
(8, 'uploads/1765884698_img_2_6941431ab8436.jpg', 9),
(8, 'uploads/1765884698_img_3_6941431ab85c5.jpg', 10),
(9, 'uploads/1765885377_img_0_694145c10c81e.jpg', 11),
(9, 'uploads/1765885377_img_1_694145c10c9f7.jpg', 12),
(9, 'uploads/1765885377_img_2_694145c10cd86.jpg', 13),
(9, 'uploads/1765885377_img_3_694145c10d04b.jpg', 14),
(10, 'uploads/1765886985_img_0_69414c09c500c.jpg', 15),
(10, 'uploads/1765886985_img_1_69414c09c517d.jpg', 16),
(10, 'uploads/1765886985_img_2_69414c09c53b8.jpg', 17),
(11, 'uploads/1765887139_img_0_69414ca3d8d62.jpg', 18),
(11, 'uploads/1765887139_img_1_69414ca3d8ec2.jpg', 19),
(11, 'uploads/1765887139_img_2_69414ca3d9019.jpg', 20),
(11, 'uploads/1765887139_img_3_69414ca3d925b.jpg', 21),
(11, 'uploads/1765887139_img_4_69414ca3d94f4.jpg', 22),
(12, 'uploads/1765888289_img_0_69415121a7c1a.jpg', 23),
(12, 'uploads/1765888289_img_1_69415121aaf78.jpg', 24),
(12, 'uploads/1765888289_img_2_69415121ab14e.jpg', 25),
(12, 'uploads/1765888289_img_3_69415121ab45f.jpg', 26),
(12, 'uploads/1765888289_img_4_69415121ab556.jpg', 27),
(12, 'uploads/1765888289_img_5_69415121ae2c5.jpg', 28),
(12, 'uploads/1765888289_img_6_69415121ae60e.jpg', 29);

-- --------------------------------------------------------

--
-- Table structure for table `productreviews`
--

DROP TABLE IF EXISTS `productreviews`;
CREATE TABLE IF NOT EXISTS `productreviews` (
  `productID` int NOT NULL,
  `uEmail` varchar(100) NOT NULL,
  `reviewText` varchar(500) NOT NULL,
  `reviewID` int NOT NULL AUTO_INCREMENT,
  `rating` int NOT NULL,
  PRIMARY KEY (`reviewID`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `productreviews`
--

INSERT INTO `productreviews` (`productID`, `uEmail`, `reviewText`, `reviewID`, `rating`) VALUES
(1, 'behan@ahawala.com', 'aaaaa', 1, 0),
(1, 'behan@ahawala.com', 'aaaaa', 2, 0),
(1, 'behan@ahawala.com', 'defwaea', 3, 0),
(1, 'behan@ahawala.com', 'mmmmm', 4, 1),
(3, 'mmm@mm.com', 'fqewqdf', 5, 1),
(1, 'mmm@mm.com', '1234', 6, 1),
(12, 'nimalhardwares@comcom.com', 'aaa', 7, 1),
(11, 'kamal@gmail.com', 'fewdew', 8, 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `productID` int NOT NULL AUTO_INCREMENT,
  `imgURL` varchar(100) NOT NULL,
  `offTagDescription` varchar(20) DEFAULT NULL,
  `oldPrice` double DEFAULT NULL,
  `newPrice` double DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL,
  `soldByStoreID` int NOT NULL,
  `reviewCount` int DEFAULT NULL,
  `rating` float DEFAULT NULL,
  `deliveryAvailable` tinyint(1) NOT NULL DEFAULT '1',
  `callToAction` int DEFAULT NULL,
  `pickup` tinyint(1) NOT NULL DEFAULT '1',
  `inStock` tinyint(1) NOT NULL DEFAULT '1',
  `forRent` tinyint(1) NOT NULL DEFAULT '0',
  `wholesale` tinyint(1) NOT NULL DEFAULT '1',
  `searchTags` varchar(500) DEFAULT NULL,
  `categoryID` int NOT NULL,
  `buyerCount` int NOT NULL DEFAULT '0',
  `stockQty` int DEFAULT NULL,
  PRIMARY KEY (`productID`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`productID`, `imgURL`, `offTagDescription`, `oldPrice`, `newPrice`, `title`, `description`, `soldByStoreID`, `reviewCount`, `rating`, `deliveryAvailable`, `callToAction`, `pickup`, `inStock`, `forRent`, `wholesale`, `searchTags`, `categoryID`, `buyerCount`, `stockQty`) VALUES
(1, 'drilldemo/drill1.jpg', '25% OFF', 8600, 7500, 'Cordless Drill with bits', 'A powerful cordless drill for all your home and professional needs. Includes battery, charger, and a bits set with a 90 degree bend. for home use', 1, NULL, 4, 1, NULL, 1, 1, 0, 1, 'drill', 1, 0, NULL),
(8, 'uploads/1765884698_main_6941431ab7b47.webp', '10% OFF', NULL, 15000, 'Angle Grinder', 'DCK Angle Grinder, 4-1/2-Inch, 6.7Amp Cut off Tool Grinder, 11,800 RPM with 2 Safety Guards, 6-Piece Discs (2 Cutting/2 Grinding/2 Flap Discs)', 1, NULL, NULL, 1, 712345678, 1, 1, 0, 1, '', 1, 0, NULL),
(9, 'uploads/1765885377_main_694145c10c5cc.jpg', 'NEW', NULL, 4500, 'Drill bits', 'Drill Bit Set, 3-Flats Shank,14-Piece, 135 Degree Split Point, for Plastic, Wood and Metal (DWA1184)', 10, NULL, NULL, 1, 2147483647, 1, 1, 0, 1, '', 1, 0, NULL),
(10, 'uploads/1765886985_main_69414c09c4dcc.jpg', '15% OFF', NULL, 300, 'School glue', 'glue', 8, NULL, NULL, 1, 112567890, 1, 1, 0, 1, '', 1, 0, NULL),
(11, 'uploads/1765887139_main_69414ca3d8b87.jpg', 'Retail', NULL, 150, 'Glue sticks', 'glue sticks', 8, NULL, NULL, 1, 11256789, 1, 1, 0, 1, 'glue', 1, 0, NULL),
(12, 'uploads/1765888289_main_69415121a7a05.jpg', 'Retail', NULL, NULL, 'Rebar', 'Rebar', 11, NULL, NULL, 0, 111234567, 1, 1, 0, 1, '', 1, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reviewimgs`
--

DROP TABLE IF EXISTS `reviewimgs`;
CREATE TABLE IF NOT EXISTS `reviewimgs` (
  `reviewID` int NOT NULL,
  `imgURL` varchar(500) NOT NULL,
  `imgID` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`imgID`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reviewimgs`
--

INSERT INTO `reviewimgs` (`reviewID`, `imgURL`, `imgID`) VALUES
(2, 'uploads/reviews/rev_1765836145_3578e166d816.webp', 1),
(2, 'uploads/reviews/rev_1765836145_13d06b03c8c5.webp', 2),
(3, 'uploads/reviews/rev_1765836739_12a37cdef59e.webp', 3),
(3, 'uploads/reviews/rev_1765836739_55d475fda4cc.webp', 4),
(3, 'uploads/reviews/rev_1765836739_c579e1cd1528.webp', 5),
(4, 'uploads/reviews/rev_1765838264_98f9e3756fbb.webp', 6),
(5, 'uploads/reviews/rev_1765839576_9bbe309b0783.webp', 7),
(5, 'uploads/reviews/rev_1765839576_bba7f6863743.webp', 8),
(6, 'uploads/reviews/rev_1765839642_d37a327634c5.webp', 9),
(6, 'uploads/reviews/rev_1765839642_3bddfd768710.webp', 10),
(7, 'uploads/reviews/rev_1765889805_b63f170d6d77.jpg', 11);

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

DROP TABLE IF EXISTS `stores`;
CREATE TABLE IF NOT EXISTS `stores` (
  `storeID` int NOT NULL AUTO_INCREMENT,
  `storeName` varchar(200) NOT NULL,
  `storeContactUEmail` varchar(100) NOT NULL,
  `storeBio` varchar(500) NOT NULL,
  `storeLocation` varchar(100) NOT NULL,
  PRIMARY KEY (`storeID`,`storeContactUEmail`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`storeID`, `storeName`, `storeContactUEmail`, `storeBio`, `storeLocation`) VALUES
(1, 'Jaya Stores', 'jaya@jayahardwares.com', 'Jaya hardwares! the all in one place for large collection of hardware items that you can never find on anywhere else', 'Colombo'),
(10, 'saman hardwares', 'saman@gmail.com', 'Welcome to saman hardwares\'s store!', ''),
(9, 'jayaa', 'jaya@jayahardwares.com', 'Welcome to jayaa\'s store!', ''),
(8, 'esfgsefesf', 'mmm@mm.com', 'Welcome to esfgsefesf\'s store!', ''),
(7, 'esfgsefesf', 'mmm@mm.com', 'Welcome to esfgsefesf\'s store!', ''),
(11, 'nimal', 'nimalhardwares@comcom.com', 'Welcome to nimal\'s store!', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `uEmail` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `address` varchar(200) NOT NULL,
  `contact` int NOT NULL,
  `profilePic` varchar(500) NOT NULL,
  `isSeller` tinyint(1) NOT NULL,
  `ordersCompleted` int NOT NULL,
  PRIMARY KEY (`uEmail`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uEmail`, `name`, `password`, `address`, `contact`, `profilePic`, `isSeller`, `ordersCompleted`) VALUES
('jaya@jayahardwares.com', 'jayaa', '1234', 'abcabcabcabcabc', 123456789, '', 1, 0),
('mmm@mm.com', 'esfgsefesf', '1234', 'esfefsdghrgrd', 1212123125, 'uploads/profile_1765819398_82676427.png', 1, 0),
('jayas@jayahardwaress.com', 'dsaf', '1234', 'fasddfas', 12313132, '', 0, 0),
('behan@ahawala.com', 'ahawala', '1234', 'hmmmmm', 123456789, '', 0, 0),
('saman@gmail.com', 'saman hardwares', '1234', 'abc at abc', 712345678, 'uploads/profile_1765885181_23fd3e7f.jpg', 1, 0),
('nimalhardwares@comcom.com', 'nimal', '1234', 'Colombo', 114567891, 'uploads/profile_1765902883_24ea9057.jpeg', 1, 0),
('kamal@gmail.com', 'kamal', '1234', 'Colombo', 112456789, 'uploads/profile_1765903005_b4a02bb3.jpeg', 0, 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
