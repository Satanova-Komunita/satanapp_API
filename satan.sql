-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: innodb.endora.cz:3306
-- Generation Time: Mar 27, 2020 at 01:43 PM
-- Server version: 5.6.45-86.1
-- PHP Version: 7.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `satan`
--

-- --------------------------------------------------------

--
-- Table structure for table `Members`
--

CREATE TABLE `Members` (
  `ID` int(10) UNSIGNED NOT NULL,
  `member_number` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(40) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `last_name` varchar(40) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(50) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `phone` varchar(19) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `is_authorized` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Member_regional_cells`
--

CREATE TABLE `Member_regional_cells` (
  `ID` int(10) UNSIGNED NOT NULL,
  `member_ID` int(10) UNSIGNED NOT NULL,
  `regional_cell_ID` int(10) UNSIGNED NOT NULL,
  `from_date` datetime NOT NULL,
  `to_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Member_roles`
--

CREATE TABLE `Member_roles` (
  `ID` int(10) UNSIGNED NOT NULL,
  `member_ID` int(10) UNSIGNED NOT NULL,
  `role_ID` int(10) UNSIGNED NOT NULL,
  `regional_cell_ID` int(10) UNSIGNED DEFAULT NULL,
  `from_date` datetime NOT NULL,
  `to_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Member_role_candidates`
--

CREATE TABLE `Member_role_candidates` (
  `ID` int(10) UNSIGNED NOT NULL,
  `member_ID` int(10) UNSIGNED NOT NULL,
  `role_ID` int(10) UNSIGNED NOT NULL,
  `sabat_ID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Member_role_votes`
--

CREATE TABLE `Member_role_votes` (
  `ID` int(10) UNSIGNED NOT NULL,
  `member_ID` int(10) UNSIGNED NOT NULL,
  `candidate_ID` int(10) UNSIGNED NOT NULL,
  `value` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Regional_cells`
--

CREATE TABLE `Regional_cells` (
  `ID` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Roles`
--

CREATE TABLE `Roles` (
  `ID` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Sabats`
--

CREATE TABLE `Sabats` (
  `ID` int(10) UNSIGNED NOT NULL,
  `regional_cell_ID` int(10) UNSIGNED NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Sabat_proposals`
--

CREATE TABLE `Sabat_proposals` (
  `ID` int(10) UNSIGNED NOT NULL,
  `sabat_ID` int(10) UNSIGNED NOT NULL,
  `proposed_by_member_ID` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Sabat_proposal_votes`
--

CREATE TABLE `Sabat_proposal_votes` (
  `ID` int(10) UNSIGNED NOT NULL,
  `member_ID` int(10) UNSIGNED NOT NULL,
  `sabat_proposal_ID` int(10) UNSIGNED NOT NULL,
  `value` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Members`
--
ALTER TABLE `Members`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `Member_regional_cells`
--
ALTER TABLE `Member_regional_cells`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `memberID_in_member_regional_cell` (`member_ID`),
  ADD KEY `regionalcellID_in_member_regional_cell` (`regional_cell_ID`);

--
-- Indexes for table `Member_roles`
--
ALTER TABLE `Member_roles`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `memberID_in_member_role` (`member_ID`),
  ADD KEY `roleID_in_member_role` (`role_ID`),
  ADD KEY `regionalcellID_in_member_role` (`regional_cell_ID`);

--
-- Indexes for table `Member_role_candidates`
--
ALTER TABLE `Member_role_candidates`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `memberID_in_role_candidater` (`member_ID`),
  ADD KEY `roleID_in_role_candidates` (`role_ID`),
  ADD KEY `sabat_ID` (`sabat_ID`);

--
-- Indexes for table `Member_role_votes`
--
ALTER TABLE `Member_role_votes`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `memberID_in_role_votes` (`member_ID`),
  ADD KEY `candidateID_in_role_votes` (`candidate_ID`);

--
-- Indexes for table `Regional_cells`
--
ALTER TABLE `Regional_cells`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `Roles`
--
ALTER TABLE `Roles`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `Sabats`
--
ALTER TABLE `Sabats`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `regionalcellID_in_sabat` (`regional_cell_ID`);

--
-- Indexes for table `Sabat_proposals`
--
ALTER TABLE `Sabat_proposals`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `sabatID_in_sabat_proposal` (`sabat_ID`),
  ADD KEY `memberID_in_sabat_proposal` (`proposed_by_member_ID`);

--
-- Indexes for table `Sabat_proposal_votes`
--
ALTER TABLE `Sabat_proposal_votes`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `memberID_in_proposal_vote` (`member_ID`),
  ADD KEY `proposalID_in_proposal_vote` (`sabat_proposal_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Members`
--
ALTER TABLE `Members`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Member_regional_cells`
--
ALTER TABLE `Member_regional_cells`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Member_roles`
--
ALTER TABLE `Member_roles`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Member_role_candidates`
--
ALTER TABLE `Member_role_candidates`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Member_role_votes`
--
ALTER TABLE `Member_role_votes`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Regional_cells`
--
ALTER TABLE `Regional_cells`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Roles`
--
ALTER TABLE `Roles`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Sabats`
--
ALTER TABLE `Sabats`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Sabat_proposals`
--
ALTER TABLE `Sabat_proposals`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Sabat_proposal_votes`
--
ALTER TABLE `Sabat_proposal_votes`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Member_regional_cells`
--
ALTER TABLE `Member_regional_cells`
  ADD CONSTRAINT `memberID_in_member_regional_cell` FOREIGN KEY (`member_ID`) REFERENCES `Members` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `regionalcellID_in_member_regional_cell` FOREIGN KEY (`regional_cell_ID`) REFERENCES `Regional_cells` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Member_roles`
--
ALTER TABLE `Member_roles`
  ADD CONSTRAINT `memberID_in_member_role` FOREIGN KEY (`member_ID`) REFERENCES `Members` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `regionalcellID_in_member_role` FOREIGN KEY (`regional_cell_ID`) REFERENCES `Regional_cells` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `roleID_in_member_role` FOREIGN KEY (`role_ID`) REFERENCES `Roles` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Member_role_candidates`
--
ALTER TABLE `Member_role_candidates`
  ADD CONSTRAINT `memberID_in_role_candidater` FOREIGN KEY (`member_ID`) REFERENCES `Members` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `roleID_in_role_candidates` FOREIGN KEY (`role_ID`) REFERENCES `Roles` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sabat_ID` FOREIGN KEY (`sabat_ID`) REFERENCES `Sabats` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Member_role_votes`
--
ALTER TABLE `Member_role_votes`
  ADD CONSTRAINT `candidateID_in_role_votes` FOREIGN KEY (`candidate_ID`) REFERENCES `Members` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `memberID_in_role_votes` FOREIGN KEY (`member_ID`) REFERENCES `Members` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Sabats`
--
ALTER TABLE `Sabats`
  ADD CONSTRAINT `regionalcellID_in_sabat` FOREIGN KEY (`regional_cell_ID`) REFERENCES `Regional_cells` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Sabat_proposals`
--
ALTER TABLE `Sabat_proposals`
  ADD CONSTRAINT `memberID_in_sabat_proposal` FOREIGN KEY (`proposed_by_member_ID`) REFERENCES `Members` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sabatID_in_sabat_proposal` FOREIGN KEY (`sabat_ID`) REFERENCES `Sabats` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Sabat_proposal_votes`
--
ALTER TABLE `Sabat_proposal_votes`
  ADD CONSTRAINT `memberID_in_proposal_vote` FOREIGN KEY (`member_ID`) REFERENCES `Members` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `proposalID_in_proposal_vote` FOREIGN KEY (`sabat_proposal_ID`) REFERENCES `Sabat_proposals` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
