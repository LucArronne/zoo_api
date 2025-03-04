-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 03, 2025 at 03:25 PM
-- Server version: 10.11.8-MariaDB-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zoodb`
--

-- --------------------------------------------------------

--
-- Table structure for table `animal`
--

CREATE TABLE `animal` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `race_id` int(11) NOT NULL,
  `habitat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `animal_food`
--

CREATE TABLE `animal_food` (
  `id` int(11) NOT NULL,
  `animal_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `quantity` double NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `animal_image`
--

CREATE TABLE `animal_image` (
  `id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `animal_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `animal_rapport`
--

CREATE TABLE `animal_rapport` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `animal_id` int(11) DEFAULT NULL,
  `state` longtext NOT NULL,
  `food` varchar(255) NOT NULL,
  `quantity` double NOT NULL,
  `date` date NOT NULL COMMENT '(DC2Type:date_immutable)',
  `details` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `id` int(11) NOT NULL,
  `pseudo` varchar(50) NOT NULL,
  `text` longtext NOT NULL,
  `is_visible` tinyint(1) DEFAULT NULL,
  `created_at` date NOT NULL COMMENT '(DC2Type:date_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment`
--

INSERT INTO `comment` (`id`, `pseudo`, `text`, `is_visible`, `created_at`) VALUES
(329, 'John Doe', 'Cool website', 1, '2024-11-15'),
(330, 'John Doe', 'Cool website', 1, '2024-11-15'),
(331, 'John Doe', 'Cool website', 0, '2024-11-15'),
(332, 'John Doe', 'Cool website', 0, '2024-11-15');

-- --------------------------------------------------------

--
-- Table structure for table `email`
--

CREATE TABLE `email` (
  `id` int(11) NOT NULL,
  `adress` varchar(50) NOT NULL,
  `subject` varchar(50) NOT NULL,
  `text` longtext NOT NULL,
  `name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Table structure for table `habitat`
--

CREATE TABLE `habitat` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `habitat`
--

INSERT INTO `habitat` (`id`, `name`, `description`) VALUES
(159, 'Habitat 1', 'Description 1');

-- --------------------------------------------------------

--
-- Table structure for table `habitat_habitat_image`
--

CREATE TABLE `habitat_habitat_image` (
  `habitat_id` int(11) NOT NULL,
  `habitat_image_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Table structure for table `habitat_image`
--

CREATE TABLE `habitat_image` (
  `id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;


--
-- Table structure for table `race`
--

CREATE TABLE `race` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `race`
--

INSERT INTO `race` (`id`, `name`) VALUES
(109, 'Race 1');

-- --------------------------------------------------------

--
-- Table structure for table `refresh_tokens`
--

CREATE TABLE `refresh_tokens` (
  `id` int(11) NOT NULL,
  `refresh_token` varchar(128) NOT NULL,
  `username` varchar(255) NOT NULL,
  `valid` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id` int(11) NOT NULL,
  `value` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `value`, `name`) VALUES
(77, 'ROLE_ADMIN', 'Administateur'),
(78, 'ROLE_EMPLOYEE', 'Employé'),
(79, 'ROLE_VETERNARY', 'Vétérnaire');

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `email`, `password`, `name`, `role_id`) VALUES
(65, 'admin@zoo.org', '$2y$13$WuCFdUNqx2BLKoKimeVBReHvSxvSk7/XvYHC6kbxIYkxX2iAOVtv.', NULL, 77),
(75, 'demo1@zoo.org', '$2y$13$2rQbhRRKzLNkvOhgJckgue0bNvOzlFf9ekyg1LApfZhUF8DG4oQ6i', 'Demo 1', 78),
(76, 'demo2@zoo.org', '$2y$13$US1el7Ddr/h4NzZ02CYwv.kSPtxzrD.9mRheLVlJkjlcFKIO4jk8e', 'Demo 2', 79);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `animal`
--
ALTER TABLE `animal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6AAB231F6E59D40D` (`race_id`),
  ADD KEY `IDX_6AAB231FAFFE2D26` (`habitat_id`);

--
-- Indexes for table `animal_food`
--
ALTER TABLE `animal_food`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_931568C38E962C16` (`animal_id`),
  ADD KEY `IDX_931568C3A76ED395` (`user_id`);

--
-- Indexes for table `animal_image`
--
ALTER TABLE `animal_image`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_E4CEDDABB548B0F` (`path`),
  ADD KEY `IDX_E4CEDDAB8E962C16` (`animal_id`);

--
-- Indexes for table `animal_rapport`
--
ALTER TABLE `animal_rapport`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_31EBCFA6A76ED395` (`user_id`),
  ADD KEY `IDX_31EBCFA68E962C16` (`animal_id`);

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email`
--
ALTER TABLE `email`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `habitat`
--
ALTER TABLE `habitat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_3B37B2E85E237E06` (`name`);

--
-- Indexes for table `habitat_habitat_image`
--
ALTER TABLE `habitat_habitat_image`
  ADD PRIMARY KEY (`habitat_id`,`habitat_image_id`),
  ADD KEY `IDX_A5C5B042AFFE2D26` (`habitat_id`),
  ADD KEY `IDX_A5C5B042521FE96` (`habitat_image_id`);

--
-- Indexes for table `habitat_image`
--
ALTER TABLE `habitat_image`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_9AD7E031B548B0F` (`path`);

--
-- Indexes for table `race`
--
ALTER TABLE `race`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_DA6FBBAF5E237E06` (`name`);

--
-- Indexes for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_9BACE7E1C74F2195` (`refresh_token`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_E19D9AD25E237E06` (`name`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`),
  ADD KEY `IDX_8D93D649D60322AC` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `animal`
--
ALTER TABLE `animal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=237;

--
-- AUTO_INCREMENT for table `animal_food`
--
ALTER TABLE `animal_food`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `animal_image`
--
ALTER TABLE `animal_image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=412;

--
-- AUTO_INCREMENT for table `animal_rapport`
--
ALTER TABLE `animal_rapport`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=333;

--
-- AUTO_INCREMENT for table `email`
--
ALTER TABLE `email`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `habitat`
--
ALTER TABLE `habitat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT for table `habitat_image`
--
ALTER TABLE `habitat_image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=185;

--
-- AUTO_INCREMENT for table `race`
--
ALTER TABLE `race`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `animal`
--
ALTER TABLE `animal`
  ADD CONSTRAINT `FK_6AAB231FAFFE2D26` FOREIGN KEY (`habitat_id`) REFERENCES `habitat` (`id`),
  ADD CONSTRAINT `animal_ibfk_1` FOREIGN KEY (`race_id`) REFERENCES `race` (`id`);

--
-- Constraints for table `animal_food`
--
ALTER TABLE `animal_food`
  ADD CONSTRAINT `FK_931568C38E962C16` FOREIGN KEY (`animal_id`) REFERENCES `animal` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_931568C3A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `animal_image`
--
ALTER TABLE `animal_image`
  ADD CONSTRAINT `FK_E4CEDDAB8E962C16` FOREIGN KEY (`animal_id`) REFERENCES `animal` (`id`);

--
-- Constraints for table `animal_rapport`
--
ALTER TABLE `animal_rapport`
  ADD CONSTRAINT `FK_31EBCFA68E962C16` FOREIGN KEY (`animal_id`) REFERENCES `animal` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_31EBCFA6A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `habitat_habitat_image`
--
ALTER TABLE `habitat_habitat_image`
  ADD CONSTRAINT `FK_A5C5B042521FE96` FOREIGN KEY (`habitat_image_id`) REFERENCES `habitat_image` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_A5C5B042AFFE2D26` FOREIGN KEY (`habitat_id`) REFERENCES `habitat` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_8D93D649D60322AC` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
