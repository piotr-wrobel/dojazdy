-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas generowania: 10 Gru 2016, 22:35
-- Wersja serwera: 10.0.28-MariaDB-0+deb8u1
-- Wersja PHP: 5.6.27-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Baza danych: `doJazdy`
--

DELIMITER $$
--
-- Procedury
--
CREATE DEFINER=`phpmyadmin`@`localhost` PROCEDURE `rozliczenie_na_dzien`(IN `dzien` DATE)
    READS SQL DATA
BEGIN
SELECT su1.imie AS kto_winny, su2.imie AS komu_winny, zliczone.ile
FROM (
	SELECT pierwsza.pasazer AS kto,pierwsza.kierowca AS komu,pierwsza.winien-IFNULL(druga.winien, 0) AS ile 
	FROM (SELECT pasazer,kierowca,SUM(dystans) AS winien FROM przejazdy WHERE data_przejazdu < dzien GROUP BY pasazer, kierowca) pierwsza 
	left join (SELECT pasazer,kierowca,SUM(dystans) AS winien FROM przejazdy WHERE data_przejazdu < dzien GROUP BY pasazer, kierowca) druga 
	ON pierwsza.pasazer=druga.kierowca AND pierwsza.kierowca=druga.pasazer 
	HAVING ile > 0
) zliczone 
INNER JOIN slownik_uczestnicy su1 ON zliczone.kto=su1.id 
INNER JOIN slownik_uczestnicy su2 ON zliczone.komu=su2.id 
ORDER BY ile DESC;
END$$

CREATE DEFINER=`phpmyadmin`@`localhost` PROCEDURE `zaloguj`(IN `haslo` VARCHAR(30) CHARSET utf8)
    MODIFIES SQL DATA
BEGIN
DECLARE id INT DEFAULT 0;
DECLARE hhaslo VARCHAR(50) DEFAULT '';
SELECT su.id,su.haslo INTO id,hhaslo FROM slownik_uczestnicy su WHERE aktywny_do > NOW() AND aktywny_od < NOW() AND su.haslo=PASSWORD(haslo) LIMIT 1;
IF id>0 THEN
	SELECT PASSWORD(CONCAT(NOW(),hhaslo)) INTO hhaslo;
    UPDATE slownik_uczestnicy su SET su.sesja=hhaslo,sesja_wazna=ADDDATE(NOW(),'7') WHERE su.id=id;
END IF;

SELECT hhaslo AS sesja;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `przejazdy`
--

CREATE TABLE IF NOT EXISTS `przejazdy` (
`id` int(11) NOT NULL,
  `pasazer` int(10) unsigned NOT NULL,
  `kierowca` int(10) unsigned NOT NULL,
  `trasa_id` int(10) unsigned NOT NULL,
  `dystans` int(10) unsigned NOT NULL,
  `data_przejazdu` datetime NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dodal` int(11) NOT NULL,
  `uwagi` text CHARACTER SET utf8 COLLATE utf8_bin
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `przejazdy_backup`
--

CREATE TABLE IF NOT EXISTS `przejazdy_backup` (
`id` int(11) NOT NULL,
  `pasazer` int(10) unsigned NOT NULL,
  `kierowca` int(10) unsigned NOT NULL,
  `trasa_id` int(10) unsigned NOT NULL,
  `dystans` int(10) unsigned NOT NULL,
  `data_przejazdu` datetime NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dodal` int(11) NOT NULL,
  `uwagi` text CHARACTER SET utf8 COLLATE utf8_bin
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Zastąpiona struktura widoku `przejazdy_pelny`
--
CREATE TABLE IF NOT EXISTS `przejazdy_pelny` (
`id` int(11)
,`pasazer` varchar(30)
,`id_pasazer` int(10) unsigned
,`kierowca` varchar(30)
,`id_kierowca` int(10) unsigned
,`trasa` varchar(100)
,`id_trasa` int(10) unsigned
,`dystans` int(10) unsigned
,`data_przejazdu` datetime
,`uwagi` mediumtext
,`dodal` varchar(30)
,`dodano` timestamp
);
-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `slownik_odcinki`
--

CREATE TABLE IF NOT EXISTS `slownik_odcinki` (
`id` int(10) unsigned NOT NULL,
  `dlugosc` int(10) unsigned NOT NULL DEFAULT '0',
  `nazwa` varchar(100) DEFAULT NULL,
  `aktywny` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `slownik_uczestnicy`
--

CREATE TABLE IF NOT EXISTS `slownik_uczestnicy` (
`id` int(10) unsigned NOT NULL,
  `imie` varchar(30) DEFAULT NULL,
  `nazwisko` varchar(30) DEFAULT NULL,
  `login` varchar(20) DEFAULT NULL,
  `haslo` varchar(50) DEFAULT NULL,
  `kierowca` tinyint(1) NOT NULL DEFAULT '1',
  `aktywny_do` datetime NOT NULL DEFAULT '2099-12-31 23:59:59',
  `aktywny_od` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sesja` varchar(50) DEFAULT NULL,
  `sesja_wazna` datetime DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura widoku `przejazdy_pelny`
--
DROP TABLE IF EXISTS `przejazdy_pelny`;

CREATE ALGORITHM=UNDEFINED DEFINER=`phpmyadmin`@`localhost` SQL SECURITY DEFINER VIEW `przejazdy_pelny` AS select `p`.`id` AS `id`,`su`.`imie` AS `pasazer`,`p`.`pasazer` AS `id_pasazer`,`su2`.`imie` AS `kierowca`,`p`.`kierowca` AS `id_kierowca`,`so`.`nazwa` AS `trasa`,`p`.`trasa_id` AS `id_trasa`,`p`.`dystans` AS `dystans`,`p`.`data_przejazdu` AS `data_przejazdu`,ifnull(`p`.`uwagi`,'') AS `uwagi`,`su3`.`imie` AS `dodal`,`p`.`timestamp` AS `dodano` from ((((`przejazdy` `p` join `slownik_uczestnicy` `su` on((`su`.`id` = `p`.`pasazer`))) join `slownik_uczestnicy` `su2` on((`p`.`kierowca` = `su2`.`id`))) join `slownik_odcinki` `so` on((`so`.`id` = `p`.`trasa_id`))) join `slownik_uczestnicy` `su3` on((`p`.`dodal` = `su3`.`id`))) order by `p`.`id`;

--
-- Indeksy dla zrzutów tabel
--

--
-- Indexes for table `przejazdy`
--
ALTER TABLE `przejazdy`
 ADD PRIMARY KEY (`id`), ADD KEY `pasazer` (`pasazer`,`kierowca`,`trasa_id`);

--
-- Indexes for table `przejazdy_backup`
--
ALTER TABLE `przejazdy_backup`
 ADD PRIMARY KEY (`id`), ADD KEY `pasazer` (`pasazer`,`kierowca`,`trasa_id`);

--
-- Indexes for table `slownik_odcinki`
--
ALTER TABLE `slownik_odcinki`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `slownik_uczestnicy`
--
ALTER TABLE `slownik_uczestnicy`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT dla tabeli `przejazdy`
--
ALTER TABLE `przejazdy`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=64;
--
-- AUTO_INCREMENT dla tabeli `przejazdy_backup`
--
ALTER TABLE `przejazdy_backup`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=64;
--
-- AUTO_INCREMENT dla tabeli `slownik_odcinki`
--
ALTER TABLE `slownik_odcinki`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT dla tabeli `slownik_uczestnicy`
--
ALTER TABLE `slownik_uczestnicy`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
