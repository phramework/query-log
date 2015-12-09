-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 09, 2015 at 07:39 PM
-- Server version: 5.5.46
-- PHP Version: 5.5.30-1~dotdeb+7.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Table structure for table `query_log`
--

CREATE TABLE IF NOT EXISTS `query_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` varchar(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `query` text NOT NULL,
  `parameters` text,
  `start_timestamp` bigint(20) unsigned NOT NULL,
  `duration` int(10) unsigned NOT NULL,
  `function` varchar(256) NOT NULL,
  `URI` varchar(2048) NOT NULL,
  `additional_parameters` text,
  `call_trace` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
