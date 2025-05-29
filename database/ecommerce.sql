-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2025 at 04:57 PM
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
-- Database: `ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `role` varchar(20) DEFAULT 'admin',
  `status` enum('active','disabled') DEFAULT 'active',
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `created_at`, `role`, `status`, `email`) VALUES
(1, 'kothroni863@gmail.com', '$2y$10$NPPvJu/sdx5cXVcHOqa01OjS.7NWxhyW4Br5nGKbxUaAT/4C5IjZG', '2025-05-17 19:33:35', 'superadmin', 'active', NULL),
(2, 'okothroni863@gmail.com', '$2y$10$fgSWM/4AwYCXkcPGVaK1YunD9Ixf2GIgbnD7XH6CF66.i4.7NUHUi', '2025-05-17 20:17:14', 'admin', 'active', NULL),
(3, 'kothroni864@gmail.com', '$2y$10$mDBOfMGAAtVEztlbfL/dk.YZGU8S5HiXcu2EiI5nRAg1gWur1yRsq', '2025-05-22 17:53:52', 'admin', 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `action`, `details`, `created_at`) VALUES
(1, 1, 'toggle_admin', 'Set admin ID 2 to disabled', '2025-05-17 22:31:58'),
(2, 1, 'toggle_admin', 'Set admin ID 2 to active', '2025-05-17 22:32:06'),
(3, 1, 'edit_username', 'Changed username for admin ID 2', '2025-05-17 22:42:27'),
(4, 1, 'change_password', 'Changed own password', '2025-05-17 22:49:47'),
(5, 1, 'change_password', 'Changed own password', '2025-05-17 22:50:02'),
(6, 1, 'change_password', 'Changed own password', '2025-05-17 22:50:16'),
(7, 1, 'toggle_admin', 'Set admin ID 3 to disabled', '2025-05-24 20:53:37'),
(8, 1, 'toggle_admin', 'Set admin ID 3 to active', '2025-05-24 20:53:45');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `category`, `image`, `price`, `stock`, `created_at`) VALUES
(42, 'Bamboo Toothbrush', 'A sustainable bamboo toothbrush designed to reduce plastic waste. Its biodegradable handle is perfect for eco-conscious individuals.', 'bamboo', 'bamboo-toothbrush.jpg', 260.00, 100, '2025-05-17 16:57:26'),
(43, 'Bamboo Cutlery Set', 'A reusable bamboo cutlery set that includes a fork, knife, spoon, and chopsticks. Ideal for travel, picnics, or everyday use.', 'bamboo', 'Bamboo Cutlery Set.jpg', 400.00, 50, '2025-05-17 16:57:26'),
(44, 'Bamboo Toothpaste Tube', 'A biodegradable bamboo toothpaste tube that is eco-friendly and helps reduce plastic waste.', 'bamboo', 'bamboo-toothpaste-tube.jpg', 350.00, 80, '2025-05-17 16:57:26'),
(45, 'Bamboo Drinking Straws', 'Reusable bamboo drinking straws that are biodegradable and perfect for reducing single-use plastics. Comes with a cleaning brush.', 'bamboo', 'bamboo-tube-straws.png', 200.00, 120, '2025-05-17 16:57:26'),
(46, 'Bamboo Cotton Buds', 'Bamboo cotton buds made from sustainable materials. Perfect for personal care and gentle on the environment.', 'bamboo', 'bamboo-cotton-buds.png', 180.00, 200, '2025-05-17 16:57:26'),
(47, 'Bamboo Hairbrush', 'A durable bamboo hairbrush that is gentle on your hair and scalp. Made from sustainable materials.', 'bamboo', 'bamboo-hairbrush.jpg', 600.00, 60, '2025-05-17 16:57:26'),
(48, 'Bamboo Soap Dish', 'A stylish bamboo soap dish that keeps your soap dry and helps it last longer. Perfect for eco-friendly bathrooms.', 'bamboo', 'bamboo-soap-dish.png', 300.00, 90, '2025-05-17 16:57:26'),
(49, 'Bamboo Charcoal powder Air Purifier', 'A natural bamboo charcoal air purifier that absorbs odors and keeps your home fresh. Ideal for closets, cars, and small spaces.', 'bamboo', 'bamboo-charcoal-air-purifier.jpeg', 750.00, 40, '2025-05-17 16:57:26'),
(50, 'Bamboo Phone Case', 'Eco-friendly bamboo phone case for sustainable protection.', 'bamboo', 'Bamboo Phone Case.jpeg', 800.00, 30, '2025-05-17 16:57:26'),
(51, 'Bamboo Serving Tray', 'A stylish bamboo serving tray for your kitchen or dining.', 'bamboo', 'Bamboo Serving Tray.jpeg', 900.00, 25, '2025-05-17 16:57:26'),
(52, 'Beeswax Food Wraps', 'Reusable beeswax food wraps for eco-friendly food storage.', 'beeswax', 'Beeswax Food Wraps.jpeg', 550.00, 70, '2025-05-17 16:57:26'),
(53, 'Beeswax Candles', 'Natural beeswax candles for a clean and long-lasting burn.', 'beeswax', 'Beeswax Candles.png', 800.00, 40, '2025-05-17 16:57:26'),
(54, 'Beeswax Lip Balm', 'Moisturizing beeswax lip balm for soft and healthy lips.', 'beeswax', 'Beeswax Lip Balm.png', 350.00, 100, '2025-05-17 16:57:26'),
(55, 'Beeswax Wooden Comb', 'Handcrafted wooden comb infused with beeswax for smooth hair.', 'beeswax', 'Beeswax Wooden Comb.png', 400.00, 60, '2025-05-17 16:57:26'),
(56, 'Beeswax Lotion Bars', 'Solid lotion bars made with beeswax for deep hydration.', 'beeswax', 'Beeswax Lotion Bars.png', 500.00, 80, '2025-05-17 16:57:26'),
(57, 'Beeswax Firestarter', 'Eco-friendly firestarter made from beeswax.', 'beeswax', 'Beeswax Firestarter.png', 700.00, 50, '2025-05-17 16:57:26'),
(58, 'Beeswax Reusable Sandwich Bags', 'Reusable sandwich bags coated with beeswax.', 'beeswax', 'Beeswax Reusable Sandwich Bags.png', 600.00, 60, '2025-05-17 16:57:26'),
(59, 'Beeswax Wrap Starter Pack', 'Starter pack of beeswax wraps for all your needs.', 'beeswax', 'Beeswax Wrap Starter Pack.png', 900.00, 30, '2025-05-17 16:57:26'),
(60, 'Beeswax Conditioner Bars', 'Solid conditioner bars made with beeswax.', 'beeswax', 'Beeswax Conditioner Bars.png', 450.00, 70, '2025-05-17 16:57:26'),
(61, 'Reusable Shopping Bags', 'Durable and eco-friendly reusable shopping bags.', 'reusable', 'Reusable Shopping Bags.png', 300.00, 150, '2025-05-17 16:57:26'),
(62, 'Reusable Produce Bags', 'Mesh produce bags for fruits and vegetables.', 'reusable', '1747491134_Reusable produce bags.jpg', 280.00, 120, '2025-05-17 16:57:26'),
(63, 'Reusable Water Bottles (Stainless Steel)', 'Stainless steel water bottles for everyday hydration.', 'reusable', '1748110353_top-view-floating-water.jpg', 940.00, 80, '2025-05-17 16:57:26'),
(64, 'Reusable Coffee Cups (Bamboo, Stainless Steel)', 'Reusable coffee cups made from bamboo and stainless steel.', 'reusable', '1748110503_Reusable Coffee Cups (Bamboo, Stainless Steel).jpg', 550.00, 90, '2025-05-17 16:57:26'),
(65, 'Reusable Lunch Boxes', 'Eco-friendly lunch boxes for meals on the go.', 'reusable', '1748110573_Reusable Lunch Boxes.jpg', 600.00, 60, '2025-05-17 16:57:26'),
(66, 'Silicone Bags', 'Silicone bags for food storage and organization.', 'reusable', '1748110648_Reusable Silicone Bags.jpg', 450.00, 100, '2025-05-17 16:57:26'),
(67, 'Cotton Pads', 'Washable cotton pads for makeup removal.', 'reusable', '1748110727_pads.jpg', 320.00, 110, '2025-05-17 16:57:26'),
(68, 'Coffee Filters', 'Reusable coffee filters for zero waste brewing.', 'reusable', '1748110768_12.png', 400.00, 70, '2025-05-17 16:57:26'),
(69, 'Tea Infuser', 'Stainless steel tea infuser for loose leaf tea.', 'reusable', '1748110819_Tea.jpg', 280.00, 90, '2025-05-17 16:57:26'),
(70, 'Face Masks (Cotton)', 'Cotton face masks for everyday protection.', 'reusable', '1748110874_facemask.jpg', 150.00, 200, '2025-05-17 16:57:26'),
(71, 'Phone Case', 'A compostable phone case made from biodegradable materials. Protect your phone while being eco-friendly.', 'compostable', '1748110944_phone case.jpg', 850.00, 40, '2025-05-17 16:57:26'),
(72, 'Trash Bags', 'Compostable trash bags that break down naturally, reducing waste in landfills.', 'compostable', '1748111015_trash bag.png', 400.00, 100, '2025-05-17 16:57:26'),
(73, 'Cutlery Set', 'Compostable cutlery set including forks, knives, and spoons. Perfect for eco-friendly dining.', 'compostable', '1748111185_cutleryset.png', 300.00, 120, '2025-05-17 16:57:26'),
(74, 'Plates and Bowls', 'Compostable plates and bowls made from biodegradable materials. Ideal for parties and picnics.', 'compostable', '1748111235_plates and bowls.png', 350.00, 80, '2025-05-17 16:57:26'),
(75, 'Straws', 'Compostable straws that are biodegradable and eco-friendly. A great alternative to plastic straws.', 'compostable', '1748111279_strws.png', 200.00, 150, '2025-05-17 16:57:26'),
(76, 'Cups', 'Compostable cups made from biodegradable materials. Perfect for hot and cold beverages.', 'compostable', '1748111571_compostable cups.png', 300.00, 90, '2025-05-17 16:57:26'),
(77, 'Trays', 'Compostable trays for serving food. Made from eco-friendly materials.', 'compostable', '1748112079_comp tray.png', 350.00, 70, '2025-05-17 16:57:26'),
(78, 'Soap Dispensers', 'Compostable soap dispensers made from biodegradable materials. Perfect for eco-conscious homes.', 'compostable', '1748112112_soap dispenser.png', 500.00, 60, '2025-05-17 16:57:26'),
(79, 'Compostable Toothbrushes', 'Compostable toothbrushes with biodegradable handles. A sustainable alternative to plastic toothbrushes.', 'compostable', '1748112634_toothbrushes.png', 300.00, 100, '2025-05-17 16:57:26'),
(80, 'Compostable Tissues', 'Compostable tissues made from recycled paper. Soft, absorbent, and eco-friendly.', 'compostable', '1748112666_tissues.png', 150.00, 120, '2025-05-17 16:57:26'),
(81, 'Upcycled Denim Tote Bags', 'Tote bags made from upcycled denim jeans. Durable, stylish, and eco-friendly.', 'upcycled', 'upcycled-denim-tote-bags.jpg', 750.00, 30, '2025-05-17 16:57:26'),
(82, 'Upcycled Bottle Caps Earrings', 'Unique earrings crafted from recycled bottle caps.', 'upcycled', 'upcycled-bottle-caps-earrings.jpg', 400.00, 50, '2025-05-17 16:57:26'),
(83, 'Upcycled Plastic Furniture', 'Furniture made from recycled plastic waste. Strong and sustainable.', 'upcycled', 'upcycled-plastic-furniture.jpg', 3500.00, 10, '2025-05-17 16:57:26'),
(84, 'Upcycled Wood Planters', 'Planters made from reclaimed wood for your garden or home.', 'upcycled', 'upcycled-wood-planters.jpg', 900.00, 25, '2025-05-17 16:57:26'),
(85, 'Upcycled CD Clock', 'Wall clock made from old CDs. A creative way to reduce waste.', 'upcycled', 'upcycled-cd-clock.jpg', 450.00, 40, '2025-05-17 16:57:26'),
(86, 'Upcycled Fabric Coasters', 'Coasters made from upcycled fabric scraps.', 'upcycled', 'upcycled-fabric-coasters.jpg', 300.00, 60, '2025-05-17 16:57:26'),
(87, 'Upcycled Tire Sandals', 'Sandals made from upcycled tires for durable wear.', 'upcycled', 'upcycled-tire-sandals.jpg', 850.00, 20, '2025-05-17 16:57:26'),
(88, 'Upcycled Glass Jar Lamp', 'Lamps made from upcycled glass jars.', 'upcycled', 'upcycled-glass-jar-lamp.jpg', 1200.00, 15, '2025-05-17 16:57:26'),
(89, 'Upcycled Paper Notebooks', 'Notebooks made from upcycled paper.', 'upcycled', 'upcycled-paper-notebooks.jpg', 150.00, 80, '2025-05-17 16:57:26'),
(90, 'Upcycled Glassware', 'Drinkware made from upcycled glass.', 'upcycled', 'upcycled-glassware.jpg', 800.00, 30, '2025-05-17 16:57:26'),
(91, 'Organic Face Cream', 'Moisturizing face cream made with organic ingredients for healthy skin.', 'skincare', 'organic-face-cream.jpg', 800.00, 40, '2025-05-17 16:57:26'),
(92, 'Natural Soap Bars', 'Handmade soap bars with natural oils and scents.', 'skincare', 'natural-soap-bars.jpg', 350.00, 60, '2025-05-17 16:57:26'),
(93, 'Organic Shampoo Bar', 'Plastic-free shampoo bar for gentle hair cleansing.', 'skincare', 'organic-shampoo-bar.jpg', 400.00, 50, '2025-05-17 16:57:26'),
(94, 'Vegan Deodorant Stick', 'Natural deodorant stick with no harsh chemicals.', 'skincare', 'vegan-deodorant-stick.jpg', 550.00, 30, '2025-05-17 16:57:26'),
(95, 'Organic Sunscreen', 'Eco-friendly sunscreen with natural SPF protection.', 'skincare', 'organic-sunscreen.jpg', 900.00, 20, '2025-05-17 16:57:26'),
(96, 'Essential Oils (Lavender, Peppermint, etc.)', 'Aromatic essential oils for relaxation and wellness.', 'skincare', 'essential-oils.jpg', 600.00, 25, '2025-05-17 16:57:26'),
(97, 'Organic Beard Oil', 'Nourishing beard oil made from organic ingredients.', 'skincare', 'organic-beard-oil.jpg', 700.00, 20, '2025-05-17 16:57:26'),
(98, 'Natural Hand Lotion', 'Hand lotion made from natural ingredients for soft skin.', 'skincare', 'natural-hand-lotion.jpg', 450.00, 30, '2025-05-17 16:57:26'),
(99, 'Organic Lip Balm', 'Organic lip balm for soft, hydrated lips.', 'skincare', 'organic-lip-balm.jpg', 350.00, 40, '2025-05-17 16:57:26'),
(100, 'Natural Face Masks (Clay, Charcoal, etc.)', 'Face masks made from natural clays and charcoal.', 'skincare', 'natural-face-masks.jpg', 600.00, 30, '2025-05-17 16:57:26'),
(101, 'Eco-Friendly Laundry Detergent', 'Biodegradable laundry detergent for a greener wash.', 'cleaning', 'eco-friendly-laundry-detergent.jpg', 800.00, 35, '2025-05-17 16:57:26'),
(102, 'Natural Cleaning Sprays (All-Purpose)', 'All-purpose cleaning sprays made from plant-based ingredients.', 'cleaning', 'natural-cleaning-sprays.jpg', 450.00, 60, '2025-05-17 16:57:26'),
(103, 'Eco-Friendly Dish Soap', 'Gentle dish soap that is tough on grease but kind to the planet.', 'cleaning', 'eco-friendly-dish-soap.jpg', 350.00, 50, '2025-05-17 16:57:26'),
(104, 'Reusable Microfiber Cleaning Cloths', 'Washable microfiber cloths for all your cleaning needs.', 'cleaning', 'reusable-microfiber-cleaning-cloths.jpg', 400.00, 80, '2025-05-17 16:57:26'),
(105, 'Bamboo Toilet Brushes', 'Toilet brushes made from sustainable bamboo.', 'cleaning', 'bamboo-toilet-brushes.jpg', 500.00, 40, '2025-05-17 16:57:26'),
(106, 'Eco-Friendly Sponges', 'Sponges made from natural materials for eco cleaning.', 'cleaning', 'eco-friendly-sponges.jpg', 280.00, 60, '2025-05-17 16:57:26'),
(107, 'Eco-Friendly Toilet Paper (Recycled Paper)', 'Toilet paper made from recycled paper.', 'cleaning', 'eco-friendly-toilet-paper.jpg', 350.00, 100, '2025-05-17 16:57:26'),
(108, 'Compostable Cleaning Wipes', 'Compostable wipes for quick and eco-friendly cleaning.', 'cleaning', 'compostable-cleaning-wipes.jpg', 300.00, 70, '2025-05-17 16:57:26'),
(109, 'Eco-Friendly Glass Cleaner', 'Glass cleaner made from eco-friendly ingredients.', 'cleaning', 'eco-friendly-glass-cleaner.jpg', 280.00, 50, '2025-05-17 16:57:26'),
(110, 'Natural Carpet Cleaner', 'Carpet cleaner made from natural ingredients.', 'cleaning', 'natural-carpet-cleaner.jpg', 400.00, 40, '2025-05-17 16:57:26'),
(111, 'Organic Cotton T-Shirts', 'Soft, breathable t-shirts made from 100% organic cotton.', 'fashion', 'organic-cotton-tshirts.jpg', 900.00, 50, '2025-05-17 16:57:26'),
(112, 'Hemp Jackets', 'Durable jackets made from eco-friendly hemp fibers.', 'fashion', 'hemp-jackets.jpg', 2200.00, 20, '2025-05-17 16:57:26'),
(113, 'Recycled Polyester Jackets', 'Jackets made from recycled polyester bottles.', 'fashion', 'recycled-polyester-jackets.jpg', 1800.00, 15, '2025-05-17 16:57:26'),
(114, 'Bamboo Socks', 'Comfortable socks made from bamboo fibers.', 'fashion', 'bamboo-socks.jpg', 450.00, 60, '2025-05-17 16:57:26'),
(115, 'Vegan Leather Wallets', 'Stylish wallets made from cruelty-free vegan leather.', 'fashion', 'vegan-leather-wallets.jpg', 1500.00, 25, '2025-05-17 16:57:26'),
(116, 'Eco-Friendly Sneakers', 'Sneakers made from sustainable materials.', 'fashion', 'eco-friendly-sneakers.jpg', 3000.00, 10, '2025-05-17 16:57:26'),
(117, 'Organic Cotton Dresses', 'Dresses made from organic cotton.', 'fashion', 'organic-cotton-dresses.jpg', 1700.00, 20, '2025-05-17 16:57:26'),
(118, 'Eco-Friendly Sunglasses (Wooden or Recycled Materials)', 'Sunglasses made from wood or recycled materials.', 'fashion', 'eco-friendly-sunglasses.jpg', 1300.00, 15, '2025-05-17 16:57:26'),
(119, 'Recycled Plastic Belts', 'Belts made from recycled plastic.', 'fashion', 'recycled-plastic-belts.jpg', 700.00, 30, '2025-05-17 16:57:26'),
(120, 'Recycled Plastic Hats', 'Hats made from recycled plastic.', 'fashion', 'recycled-plastic-hats.jpg', 900.00, 20, '2025-05-17 16:57:26'),
(121, 'Eco-Friendly Plant Pots (Biodegradable)', 'Biodegradable plant pots for your garden.', 'gardening', 'eco-friendly-plant-pots.jpg', 400.00, 40, '2025-05-17 16:57:26'),
(122, 'Organic Seed Kits', 'Grow your own herbs and veggies with these organic seed kits.', 'gardening', 'organic-seed-kits.jpg', 450.00, 30, '2025-05-17 16:57:26'),
(123, 'Eco-Friendly Watering Can', 'Watering can made from recycled materials.', 'gardening', 'eco-friendly-watering-can.jpg', 900.00, 15, '2025-05-17 16:57:26'),
(124, 'Compost Bin', 'Compost bin for turning kitchen waste into garden gold.', 'gardening', 'compost-bin.jpg', 1500.00, 10, '2025-05-17 16:57:26'),
(125, 'Solar Garden Lights', 'Solar-powered lights for your garden or patio.', 'gardening', 'solar-garden-lights.jpg', 1700.00, 20, '2025-05-17 16:57:26'),
(126, 'Eco-Friendly Garden Tools (Stainless Steel)', 'Garden tools made from stainless steel and eco materials.', 'gardening', 'eco-friendly-garden-tools.jpg', 1900.00, 10, '2025-05-17 16:57:26'),
(127, 'Organic Fertilizers', 'Fertilizers made from organic materials.', 'gardening', 'organic-fertilizers.jpg', 600.00, 30, '2025-05-17 16:57:26'),
(128, 'Biodegradable Planter Bags', 'Planter bags that biodegrade after use.', 'gardening', 'biodegradable-planter-bags.jpg', 400.00, 25, '2025-05-17 16:57:26'),
(129, 'Recycled Plant Markers', 'Plant markers made from recycled materials.', 'gardening', 'recycled-plant-markers.jpg', 240.00, 40, '2025-05-17 16:57:26'),
(130, 'Sustainable Garden Kneeler', 'Garden kneeler made from sustainable materials.', 'gardening', 'sustainable-garden-kneeler.jpg', 1300.00, 10, '2025-05-17 16:57:26'),
(131, 'Organic Cotton Towels', 'Super soft towels made from organic cotton.', 'home', 'organic-cotton-towels.jpg', 1200.00, 25, '2025-05-17 16:57:26'),
(132, 'Recycled Glassware', 'Drinkware made from recycled glass.', 'home', 'recycled-glassware.jpg', 1100.00, 30, '2025-05-17 16:57:26'),
(133, 'Bamboo Serving Boards', 'Serving boards crafted from sustainable bamboo.', 'home', 'bamboo-serving-boards.jpg', 1000.00, 20, '2025-05-17 16:57:26'),
(134, 'Organic Cotton Bedding Sets', 'Luxurious bedding sets made from organic cotton.', 'home', 'organic-cotton-bedding-sets.jpg', 3500.00, 10, '2025-05-17 16:57:26'),
(135, 'Eco-Friendly Dish Brushes', 'Dish brushes made from natural materials.', 'home', 'eco-friendly-dish-brushes.jpg', 450.00, 40, '2025-05-17 16:57:26'),
(136, 'Reusable Beeswax Food Wraps', 'Reusable food wraps made from beeswax.', 'home', 'reusable-beeswax-food-wraps.jpg', 600.00, 30, '2025-05-17 16:57:26'),
(137, 'Eco-Friendly Storage Containers (Glass, Stainless Steel)', 'Storage containers made from glass or stainless steel.', 'home', 'eco-friendly-storage-containers.jpg', 900.00, 20, '2025-05-17 16:57:26'),
(138, 'Sustainable Cutting Boards (Bamboo, Wood)', 'Cutting boards made from bamboo or wood.', 'home', 'sustainable-cutting-boards.jpg', 1200.00, 15, '2025-05-17 16:57:26'),
(139, 'Recycled Plastic Furniture', 'Furniture made from recycled plastic.', 'home', 'recycled-plastic-furniture.jpg', 5000.00, 5, '2025-05-17 16:57:26'),
(140, 'Eco-Friendly Candles (Soy Wax, Recycled Containers)', 'Candles made from soy wax and recycled containers.', 'home', 'eco-friendly-candles.jpg', 700.00, 20, '2025-05-17 16:57:26');

-- --------------------------------------------------------

--
-- Table structure for table `product_loves`
--

CREATE TABLE `product_loves` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_loves`
--

INSERT INTO `product_loves` (`id`, `product_id`, `user_id`, `created_at`) VALUES
(1, 42, NULL, '2025-05-27 19:35:33'),
(2, 46, NULL, '2025-05-27 19:36:00'),
(3, 47, NULL, '2025-05-27 19:36:04'),
(4, 51, NULL, '2025-05-27 19:36:32'),
(5, 43, NULL, '2025-05-27 20:45:06');

-- --------------------------------------------------------

--
-- Table structure for table `product_ratings`
--

CREATE TABLE `product_ratings` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_ratings`
--

INSERT INTO `product_ratings` (`id`, `product_id`, `user_id`, `rating`, `created_at`) VALUES
(1, 42, NULL, 5, '2025-05-27 19:35:38'),
(2, 42, NULL, 4, '2025-05-27 19:35:42'),
(3, 42, NULL, 4, '2025-05-27 19:35:46'),
(4, 42, NULL, 4, '2025-05-27 19:35:50'),
(5, 42, NULL, 3, '2025-05-27 19:35:54'),
(6, 49, NULL, 1, '2025-05-27 19:36:24'),
(7, 48, NULL, 5, '2025-05-27 19:36:26'),
(8, 43, NULL, 2, '2025-05-27 19:36:44'),
(9, 46, NULL, 1, '2025-05-27 19:37:34'),
(10, 42, NULL, 5, '2025-05-27 20:06:08'),
(11, 42, NULL, 4, '2025-05-27 20:06:12'),
(12, 42, NULL, 5, '2025-05-27 20:06:16'),
(13, 42, NULL, 5, '2025-05-27 20:06:19'),
(14, 42, NULL, 3, '2025-05-27 20:06:23'),
(15, 43, NULL, 3, '2025-05-27 20:06:27'),
(16, 43, NULL, 2, '2025-05-27 20:06:31');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `mpesa_number` varchar(10) NOT NULL,
  `response` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `product_color` varchar(50) NOT NULL,
  `product_quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_status` varchar(32) DEFAULT 'pending',
  `delivery_address` varchar(255) DEFAULT NULL,
  `checkout_request_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `product_name`, `product_price`, `full_name`, `mpesa_number`, `response`, `created_at`, `product_color`, `product_quantity`, `total_price`, `user_id`, `order_status`, `delivery_address`, `checkout_request_id`) VALUES
(91, 'Bamboo Toothbrush', 250.00, 'Steve Okoth', '0799422635', 'No MPESA push sent', '2025-05-16 08:54:17', 'Blue', 10, 2500.00, 1, 'delivered', NULL, NULL),
(93, 'Bamboo Charcoal powder Air Purifier', 750.00, 'diana', '0799422635', 'No MPESA push sent', '2025-05-16 10:45:36', 'Black', 17, 12750.00, 3, 'delivered', '3900, Eldoret (0799422635)', NULL),
(95, 'Bamboo Cutlery Set', 400.00, 'Steve Okoth', '0799422635', 'No MPESA push sent', '2025-05-16 14:57:58', 'Default', 1, 400.00, 1, 'delivered', '3900, Eldoret (0799422635)', NULL),
(97, 'Bamboo Cutlery Set', 400.00, 'steve', '0799422635', 'Cart order', '2025-05-16 15:31:08', 'Black', 1, 400.00, 1, 'delivered', '3900, Eldoret (0799422635)', NULL),
(99, 'Bamboo Toothpaste Tube', 350.00, 'diana', '0799422635', 'Cart order', '2025-05-16 15:53:50', 'Default', 1, 350.00, 1, 'delivered', '3900, Eldoret (0799422635)', NULL),
(100, 'Bamboo Serving Tray', 900.00, 'diana', '0799422635', 'Cart order', '2025-05-16 16:26:19', 'Default', 10, 9000.00, 1, 'delivered', '3900, Eldoret (0799422635)', NULL),
(102, 'Bamboo Cutlery Set', 400.00, 'Steve Okoth', '0799422635', 'No MPESA push sent', '2025-05-17 14:07:19', 'Blue', 19, 7600.00, 1, 'delivered', '3900, Eldoret (0799422635)', NULL),
(103, 'Bamboo Cutlery Set', 400.00, 'Steve Okoth', '0799422635', 'No MPESA push sent', '2025-05-17 14:09:17', 'Blue', 19, 7600.00, 1, 'delivered', '3900, Eldoret (0799422635)', NULL),
(104, 'Bamboo Toothbrush', 250.00, 'Steve Okoth', '0799422635', 'No MPESA push sent', '2025-05-17 14:15:27', 'Default', 10, 2500.00, 1, 'delivered', '3900, Eldoret (0799422635)', NULL),
(105, 'Bamboo Hairbrush', 600.00, 'Steve Ronald Okoth ', '0799422635', 'No MPESA push sent', '2025-05-17 14:15:57', 'Black', 10, 6000.00, 1, 'in delivery', '3900, Eldoret (0799422635)', NULL),
(106, 'Bamboo Drinking Straws', 200.00, 'diana', '0799422635', 'No MPESA push sent', '2025-05-17 14:50:29', 'Default', 1, 200.00, 1, 'in delivery', '3900, Eldoret (0799422635)', NULL),
(109, 'Bamboo Toothbrush', 1.00, 'steve okoth', '0799422635', 'Cart order', '2025-05-17 21:42:22', 'Red', 1, 1.00, 1, 'received', '3900, Eldoret (0799422635)', NULL),
(110, 'Bamboo Cutlery Set', 400.00, 'Steve Okoth', '0799422635', 'No MPESA push sent', '2025-05-17 21:42:38', 'Default', 1, 400.00, 1, 'received', '3900, Eldoret (0799422635)', NULL),
(111, 'Bamboo Toothbrush', 250.00, 'Steve Okoth', '0799422635', 'No MPESA push sent', '2025-05-18 12:34:45', 'Default', 1, 250.00, 1, 'received', '3900, Eldoret (0799422635)', NULL),
(112, 'Bamboo Cutlery Set', 400.00, 'steve okoth', '0799422635', 'Cart order', '2025-05-18 12:35:07', 'Default', 1, 400.00, 1, 'received', '3900, Eldoret (0799422635)', NULL),
(113, 'Bamboo Toothbrush', 1.00, 'Steve Okoth', '0799422635', 'No MPESA push sent', '2025-05-19 17:34:06', 'Default', 1, 1.00, 1, 'received', '3900, Eldoret (0799422635)', NULL),
(115, 'Bamboo Toothbrush', 260.00, 'Steve Okoth', '0799422635', 'No MPESA push sent', '2025-05-23 14:50:31', 'Default', 1, 260.00, 1, 'received', '3900, Eldoret (0799422635)', NULL),
(122, 'Bamboo Toothbrush', 260.00, 'Steve Okoth', '0799422635', 'No MPESA push sent', '2025-05-27 19:14:09', 'Default', 1, 260.00, 1, 'received', '77MP+59 Changach, Kenya, Changach (0799422635)', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `created_at`, `profile_picture`, `address`, `city`, `phone`) VALUES
(1, 'Steve Okoth', 'okothroni863@gmail.com', '$2y$10$nbtPkoW9NXu3lGHvVw.jvu/wzmpWOjW.eMm9dPFR0htLTVq0y8.kq', '2025-05-16 08:41:16', '../uploads/user_1_1748375489.jpg', '77MP+59 Changach, Kenya', 'Changach', '0799422635'),
(2, 'Steve Okoth', 'kothroni863@gmail.com', '$2y$10$l0LzHdeJABa8uN/2ta1qjehI2JteqANli6UvReettxvRtqEcJsp6G', '2025-05-16 10:30:18', NULL, '3900', 'Eldoret', '0799422635'),
(3, 'Diana anyango', 'akothdiana863@gmail.com', '$2y$10$jBlJIrpwNSPpa4lIyznx4OnOHZfoTRD5Nq3dOEGwhLN8koDODxiaO', '2025-05-16 10:31:19', 'uploads/user_3_1747392631.jpg', '3900', 'Eldoret', '0799422635'),
(4, 'Steve Okoth', 'okothroni8@gmail.com', '$2y$10$9SsUT3RmdlQJEtbECRTQMOOTTfFjlTXinM0PRvqZgeebIZUUIV7Cu', '2025-05-27 19:45:40', '../uploads/user_4_1748375553.jpg', '77JM+PG8, Changach, Kenya', 'Changach', '0799422635');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_loves`
--
ALTER TABLE `product_loves`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_ratings`
--
ALTER TABLE `product_ratings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `product_loves`
--
ALTER TABLE `product_loves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_ratings`
--
ALTER TABLE `product_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
