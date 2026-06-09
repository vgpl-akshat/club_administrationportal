-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 10. Jun 2026 um 00:18
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `ag_verwaltung`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `admin`
--

CREATE TABLE `admin` (
  `Name` varchar(255) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Daten für Tabelle `admin`
--

INSERT INTO `admin` (`Name`, `PasswordHash`) VALUES
('Admin', '$argon2id$v=19$m=131072,t=4,p=2$Li9aanV2SmFBZHdaMTRDSQ$iLEyLYc7kUcvnwszXzECLWa+RTIsSjTYzrdsBGCOj4A');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ag`
--

CREATE TABLE `ag` (
  `Name` varchar(255) NOT NULL,
  `Leitung` varchar(4) DEFAULT NULL,
  `Raum` varchar(6) DEFAULT NULL,
  `Wochentag` varchar(12) DEFAULT NULL,
  `FindetStatt` tinyint(1) DEFAULT NULL,
  `Beschreibung` varchar(1024) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Daten für Tabelle `ag`
--

INSERT INTO `ag` (`Name`, `Leitung`, `Raum`, `Wochentag`, `FindetStatt`, `Beschreibung`) VALUES
('Basketball-AG', 'STRI', 'THG2', 'Montag', 0, 'Basketball spielen und Training'),
('Fair Trade-AG', 'SHUT', 'E15', 'Dienstag', 0, 'Beschäftigung mit Fair Trade und Nachhaltigkeit'),
('Fußball-AG', 'GOEL', 'THG1', 'Donnerstag', 0, 'Fußballtraining und Turniere'),
('Garten-AG', 'GOTT', 'K3', 'Freitag', 0, 'Arbeit im Schulgarten und Pflanzenpflege'),
('Goethe-Band', 'DENZ', 'Musik', 'Donnerstag', 0, 'Gemeinsames Musizieren in der Schulband'),
('Goetheater-AG', 'WEIS', 'Bühne', 'Mittwoch', 0, 'Theaterproben und Aufführungen'),
('Informatik', 'CAKM', 'OG112', 'Dienstag', 0, 'Hier lernt ihr viel über Informatik!'),
('Mathe-AG', 'MEYR', 'R104', 'Mittwoch', 0, 'Mathematische Übungen und Wettbewerbe'),
('Mint-Garage', 'HARJ', 'Lab1', 'Dienstag', 0, 'MINT-Projekte und Experimente'),
('Robotik-AG', 'CAKM', '111', 'Montag', 0, 'Programmierung und Bau von Robotern'),
('Sanitäter-AG', 'GROE', 'E3', 'Freitag', 0, 'Erste Hilfe und Sanitätsausbildung'),
('Sport', 'POEP', 'TUH2', 'Mittwoch', 0, 'Bewegt euch!'),
('Tanz-AG', 'MNDT', 'THG1', 'Mittwoch', 0, 'Tanztraining und Choreografien'),
('Technik-AG', 'SCHA', '101', 'Freitag', 0, 'Technische Projekte und Veranstaltungen'),
('Umwelt-AG', 'LANG', 'K3', 'Dienstag', 0, 'Umweltschutz und Nachhaltigkeitsprojekte'),
('Video-AG', 'BRUE', '111', 'Montag', 0, 'Videodreh und Medienproduktion'),
('Vogel-AG', 'POEP', 'BioTop', 'Donnerstag', 0, 'Beobachtung und Erforschung von Vögeln');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `klassen`
--

CREATE TABLE `klassen` (
  `Klasse` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Daten für Tabelle `klassen`
--

INSERT INTO `klassen` (`Klasse`) VALUES
('10a'),
('10b'),
('10c'),
('5a'),
('5b'),
('5c'),
('6a'),
('6b'),
('6c'),
('7a'),
('7b'),
('7c'),
('8a'),
('8b'),
('8c'),
('9a'),
('9b'),
('9c'),
('Q1'),
('Q2'),
('Q3');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lehrer`
--

CREATE TABLE `lehrer` (
  `Kuerzel` varchar(4) NOT NULL,
  `Vorname` varchar(255) NOT NULL,
  `Nachname` varchar(255) NOT NULL,
  `Rolle` varchar(32) NOT NULL DEFAULT 'lehrer'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Daten für Tabelle `lehrer`
--

INSERT INTO `lehrer` (`Kuerzel`, `Vorname`, `Nachname`, `Rolle`) VALUES
('BRUE', 'Thomas', 'Brückler', 'schulleitung'),
('CAKM', 'Ferit', 'Cakmaz', 'schulleitung'),
('DENZ', 'Steffi', 'Denz', 'schulleitung'),
('GOEL', 'Marcus', 'Göldner', 'lehrer'),
('GOTT', 'Goltsche', 'Ilka', 'lehrer'),
('GROE', 'Judith', 'Grön', 'lehrer'),
('HARJ', 'Olaf', 'Harjes', 'schulleitung'),
('LANG', 'Judith', 'Lange', 'lehrer'),
('MEYR', 'Merlin', 'Meyer', 'lehrer'),
('MNDT', 'Meline', 'Mundt', 'lehrer'),
('POEP', 'Nicola', 'Poeplau', 'lehrer'),
('SCHA', 'Alexander', 'Schäfer', 'lehrer'),
('SCHU', 'Patrick', 'Schulze', 'lehrer'),
('SHUT', 'Teresa', 'Schuh', 'lehrer'),
('STRI', 'Ines', 'Stricker', 'lehrer'),
('WEIS', 'Torsten', 'Weis', 'lehrer');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lehrerlogin`
--

CREATE TABLE `lehrerlogin` (
  `Kuerzel` varchar(4) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Daten für Tabelle `lehrerlogin`
--

INSERT INTO `lehrerlogin` (`Kuerzel`, `PasswordHash`) VALUES
('CAKM', '$argon2id$v=19$m=131072,t=4,p=2$Li9aanV2SmFBZHdaMTRDSQ$iLEyLYc7kUcvnwszXzECLWa+RTIsSjTYzrdsBGCOj4A');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schueler`
--

CREATE TABLE `schueler` (
  `SID` int(11) NOT NULL,
  `Vorname` varchar(255) NOT NULL,
  `Nachname` varchar(255) NOT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Klasse` varchar(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Daten für Tabelle `schueler`
--

INSERT INTO `schueler` (`SID`, `Vorname`, `Nachname`, `Email`, `Klasse`) VALUES
(2, 'Akshat', 'Venugopal', 'akshatvenugopal@outlook.de', '9b'),
(3, 'Akshat', 'Venugopal', 'akshatvenugopal@ggb.kbs.schule', 'Q1'),
(4, 'patrick', 'schulze', 'akshat@jrjr.efkeo', '9c'),
(5, 'Max', 'Mustermann', 'max.mustermann@schule.de', 'Q1'),
(6, 'r3r333', '3r3344f', 'ef3f34f@rgrg.rrvr', 'Q1'),
(7, 'Daniele', 'Forina', 'daniele.forina@gmail.com', '8a');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schulleitung`
--

CREATE TABLE `schulleitung` (
  `Kuerzel` varchar(4) NOT NULL,
  `Bezeichnung` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Daten für Tabelle `schulleitung`
--

INSERT INTO `schulleitung` (`Kuerzel`, `Bezeichnung`) VALUES
('CAKM', 'Schulleiter'),
('DENZ', 'schulleitung');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `teilnahme`
--

CREATE TABLE `teilnahme` (
  `TID` int(11) NOT NULL,
  `AgName` varchar(255) DEFAULT NULL,
  `SID` int(11) DEFAULT NULL,
  `Genehmigt` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Daten für Tabelle `teilnahme`
--

INSERT INTO `teilnahme` (`TID`, `AgName`, `SID`, `Genehmigt`) VALUES
(1, 'Sport', 2, 0),
(2, 'Informatik', 2, 1),
(4, 'Goethe-Band', 4, 1),
(5, 'Informatik', 5, 0),
(6, 'Basketball-AG', 6, 1),
(7, 'Basketball-AG', 7, 1);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Name`);

--
-- Indizes für die Tabelle `ag`
--
ALTER TABLE `ag`
  ADD PRIMARY KEY (`Name`),
  ADD KEY `Leitung` (`Leitung`);

--
-- Indizes für die Tabelle `klassen`
--
ALTER TABLE `klassen`
  ADD PRIMARY KEY (`Klasse`);

--
-- Indizes für die Tabelle `lehrer`
--
ALTER TABLE `lehrer`
  ADD PRIMARY KEY (`Kuerzel`);

--
-- Indizes für die Tabelle `lehrerlogin`
--
ALTER TABLE `lehrerlogin`
  ADD PRIMARY KEY (`Kuerzel`);

--
-- Indizes für die Tabelle `schueler`
--
ALTER TABLE `schueler`
  ADD PRIMARY KEY (`SID`),
  ADD KEY `Klasse` (`Klasse`);

--
-- Indizes für die Tabelle `schulleitung`
--
ALTER TABLE `schulleitung`
  ADD PRIMARY KEY (`Kuerzel`);

--
-- Indizes für die Tabelle `teilnahme`
--
ALTER TABLE `teilnahme`
  ADD PRIMARY KEY (`TID`),
  ADD KEY `AgName` (`AgName`),
  ADD KEY `SID` (`SID`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `schueler`
--
ALTER TABLE `schueler`
  MODIFY `SID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT für Tabelle `teilnahme`
--
ALTER TABLE `teilnahme`
  MODIFY `TID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `ag`
--
ALTER TABLE `ag`
  ADD CONSTRAINT `ag_ibfk_1` FOREIGN KEY (`Leitung`) REFERENCES `lehrer` (`Kuerzel`);

--
-- Constraints der Tabelle `lehrerlogin`
--
ALTER TABLE `lehrerlogin`
  ADD CONSTRAINT `lehrerlogin_ibfk_1` FOREIGN KEY (`Kuerzel`) REFERENCES `lehrer` (`Kuerzel`);

--
-- Constraints der Tabelle `schueler`
--
ALTER TABLE `schueler`
  ADD CONSTRAINT `schueler_ibfk_1` FOREIGN KEY (`Klasse`) REFERENCES `klassen` (`Klasse`);

--
-- Constraints der Tabelle `schulleitung`
--
ALTER TABLE `schulleitung`
  ADD CONSTRAINT `schulleitung_ibfk_1` FOREIGN KEY (`Kuerzel`) REFERENCES `lehrer` (`Kuerzel`);

--
-- Constraints der Tabelle `teilnahme`
--
ALTER TABLE `teilnahme`
  ADD CONSTRAINT `teilnahme_ibfk_1` FOREIGN KEY (`AgName`) REFERENCES `ag` (`Name`),
  ADD CONSTRAINT `teilnahme_ibfk_2` FOREIGN KEY (`SID`) REFERENCES `schueler` (`SID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
