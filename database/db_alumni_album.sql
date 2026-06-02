-- phpMyAdmin SQL Dump
-- Alumni Album database
-- Run this in phpMyAdmin (Import) to create the schema with sample data.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `alumni_album`
--
CREATE DATABASE IF NOT EXISTS `alumni_album` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `alumni_album`;

-- --------------------------------------------------------

--
-- Table structure for table `users` (алумни профили)
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(128) NOT NULL,
  `fn` varchar(16) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(32) NOT NULL,
  `password` varchar(255) NOT NULL,
  `specialty` varchar(64) DEFAULT NULL,
  `stream` varchar(32) DEFAULT NULL,
  `graduation_year` int(11) DEFAULT NULL,
  `degree` enum('bachelor','master','doctor') DEFAULT 'bachelor',
  `position` varchar(128) DEFAULT NULL,
  `location` varchar(128) DEFAULT NULL,
  `profile_pic` varchar(256) DEFAULT NULL,
  `is_teacher` tinyint(1) NOT NULL DEFAULT 0,
  `validated` tinyint(1) NOT NULL DEFAULT 0,
  `visibility` enum('public','club_only','private') NOT NULL DEFAULT 'public',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `fullname`, `fn`, `email`, `username`, `password`, `specialty`, `stream`, `graduation_year`, `degree`, `position`, `location`, `profile_pic`, `is_teacher`, `validated`, `visibility`) VALUES
(1, 'Иван Петров Георгиев', '81234', 'ivan@fmi.bg', 'ivan', 'parola123', 'Информатика', 'A', 2018, 'master', 'Software Engineer', 'София', NULL, 0, 1, 'public'),
(2, 'Мария Стоянова Димитрова', '81250', 'maria@fmi.bg', 'maria', 'parola123', 'Информатика', 'A', 2018, 'bachelor', 'Frontend Developer', 'Пловдив', NULL, 0, 1, 'public'),
(3, 'Александър Николов Тодоров', '81277', 'aleks@fmi.bg', 'aleks', 'parola123', 'Софтуерно инженерство', 'B', 2019, 'master', 'Backend Developer', 'София', NULL, 0, 0, 'public'),
(4, 'Проф. Милен Иванов', '00001', 'milen@fmi.bg', 'milen', 'parola123', 'Информатика', NULL, NULL, 'doctor', 'Преподавател по Web Technologies', 'София', NULL, 1, 1, 'public'),
(5, 'Даница Костова', '82001', 'danitsa@fmi.bg', 'danitsa', 'parola123', 'Информатика', 'A', 2025, 'bachelor', 'Student', 'София', NULL, 0, 0, 'public');

-- --------------------------------------------------------

--
-- Table structure for table `clubs` (алумни клубове)
--

CREATE TABLE `clubs` (
  `id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `type` enum('specialty','stream','year','teacher','degree') NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `clubs` (`id`, `name`, `type`, `description`, `owner_id`) VALUES
(1, 'Информатика — Випуск 2018', 'year', 'Випуск 2018, спец. Информатика', 1),
(2, 'Софтуерно инженерство', 'specialty', 'Всички завършили специалност СИ', 3),
(3, 'Поток A', 'stream', 'Поток A — всички випуски', 1),
(4, 'Клуб на проф. Милен Иванов', 'teacher', 'Студенти на проф. Милен Иванов', 4),
(5, 'Магистри ФМИ', 'degree', 'Завършили магистратура във ФМИ', 1);

-- --------------------------------------------------------

--
-- Table structure for table `club_members` (членство в клубове)
--

CREATE TABLE `club_members` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `club_members` (`id`, `club_id`, `user_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 2, 3),
(4, 3, 1),
(5, 3, 2),
(6, 4, 1),
(7, 5, 1),
(8, 5, 3);

-- --------------------------------------------------------

--
-- Table structure for table `validations` (валидиране на алумни профил)
-- Бивш студент качва 'доказателство' или текст и 2-3 колеги/преподавател потвърждават.
--

CREATE TABLE `validations` (
  `id` int(11) NOT NULL,
  `alumni_id` int(11) NOT NULL,
  `validator_id` int(11) NOT NULL,
  `evidence_text` text DEFAULT NULL,
  `evidence_photo` varchar(256) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `note` varchar(256) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `validations` (`id`, `alumni_id`, `validator_id`, `evidence_text`, `status`, `note`) VALUES
(1, 1, 2, 'Колега от випуск 2018, помня го от лекциите по DSA.', 'approved', 'Потвърдено от колега'),
(2, 1, 4, 'Беше мой студент по Web Technologies.', 'approved', 'Потвърдено от преподавател'),
(3, 3, 1, 'Бяхме в един поток.', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `photo_sessions` (фото сесии)
--

CREATE TABLE `photo_sessions` (
  `id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `photographer_id` int(11) DEFAULT NULL,
  `session_date` date DEFAULT NULL,
  `location` varchar(128) DEFAULT NULL,
  `is_group` tinyint(1) NOT NULL DEFAULT 0,
  `description` varchar(256) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `photo_sessions` (`id`, `name`, `photographer_id`, `session_date`, `location`, `is_group`, `description`) VALUES
(1, 'Випуск 2018 - дипломиране', 4, '2018-06-30', 'Аула на ФМИ', 1, 'Групова снимка на випуск 2018'),
(2, 'Индивидуални портрети 2018', 4, '2018-06-15', 'Студио ФМИ', 0, 'Лични снимки за албума');

-- --------------------------------------------------------

--
-- Table structure for table `photos`
--

CREATE TABLE `photos` (
  `id` int(11) NOT NULL,
  `uploader_id` int(11) NOT NULL,
  `alumni_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `image_dir` varchar(256) NOT NULL,
  `name` varchar(256) NOT NULL,
  `caption` varchar(256) DEFAULT NULL,
  `source` enum('personal','photographer','other') NOT NULL DEFAULT 'personal',
  `size` int(11) NOT NULL,
  `type` varchar(64) NOT NULL,
  `date_taken` date DEFAULT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `photos` (`id`, `uploader_id`, `alumni_id`, `session_id`, `image_dir`, `name`, `caption`, `source`, `size`, `type`, `date_taken`) VALUES
(1, 4, 1, 2, 'models/uploads/sample.jpg', 'sample.jpg', 'Иван - портрет', 'photographer', 100000, 'image/jpeg', '2018-06-15'),
(2, 1, 1, 1, 'models/uploads/sample.jpg', 'sample.jpg', 'Групова снимка', 'photographer', 200000, 'image/jpeg', '2018-06-30');

-- --------------------------------------------------------

--
-- Table structure for table `business_cards` (визитки)
--

CREATE TABLE `business_cards` (
  `id` int(11) NOT NULL,
  `alumni_id` int(11) NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `company` varchar(128) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(128) DEFAULT NULL,
  `visibility` enum('public','club_only') NOT NULL DEFAULT 'club_only',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `business_cards` (`id`, `alumni_id`, `title`, `company`, `phone`, `email`, `website`, `visibility`) VALUES
(1, 1, 'Senior Software Engineer', 'SoftCo', '+359 88 123 4567', 'ivan@softco.bg', 'https://softco.bg', 'public'),
(2, 2, 'Frontend Developer', 'WebStudio', '+359 88 765 4321', 'maria@webstudio.bg', NULL, 'club_only');

-- --------------------------------------------------------

--
-- Table structure for table `print_orders` (заявки за печат - албуми, картички, календари, чаши, карти за бридж)
--

CREATE TABLE `print_orders` (
  `id` int(11) NOT NULL,
  `alumni_id` int(11) NOT NULL,
  `product_type` enum('album','postcard','calendar','mug','bridge_cards') NOT NULL,
  `photo_ids` varchar(512) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `note` varchar(256) DEFAULT NULL,
  `status` enum('new','in_progress','done','cancelled') NOT NULL DEFAULT 'new',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `print_orders` (`id`, `alumni_id`, `product_type`, `photo_ids`, `quantity`, `note`, `status`) VALUES
(1, 1, 'album', '[1,2]', 1, 'Албум за випуск 2018', 'new'),
(2, 2, 'mug', '[1]', 2, 'Чаша с групова снимка', 'in_progress');

--
-- Indexes
--
ALTER TABLE `users` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email` (`email`), ADD UNIQUE KEY `fn` (`fn`);
ALTER TABLE `clubs` ADD PRIMARY KEY (`id`);
ALTER TABLE `club_members` ADD PRIMARY KEY (`id`);
ALTER TABLE `validations` ADD PRIMARY KEY (`id`);
ALTER TABLE `photo_sessions` ADD PRIMARY KEY (`id`);
ALTER TABLE `photos` ADD PRIMARY KEY (`id`);
ALTER TABLE `business_cards` ADD PRIMARY KEY (`id`);
ALTER TABLE `print_orders` ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT
--
ALTER TABLE `users` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `clubs` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `club_members` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
ALTER TABLE `validations` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `photo_sessions` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `photos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `business_cards` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `print_orders` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
