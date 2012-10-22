-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 22, 2012 at 06:00 AM
-- Server version: 5.0.95
-- PHP Version: 5.4.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `iplan`
--

-- --------------------------------------------------------

--
-- Table structure for table `alert`
--

DROP TABLE IF EXISTS `alert`;
CREATE TABLE IF NOT EXISTS `alert` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL,
  `planid` int(10) unsigned default NULL,
  `msg` varchar(100) NOT NULL,
  `time` char(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `authority`
--

DROP TABLE IF EXISTS `authority`;
CREATE TABLE IF NOT EXISTS `authority` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `planid` int(11) NOT NULL,
  `type` enum('MEMBER','FRIEND','ALL','GLOBAL') NOT NULL,
  `auth` char(4) NOT NULL,
  `data` varchar(100) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kv`
--

DROP TABLE IF EXISTS `kv`;
CREATE TABLE IF NOT EXISTS `kv` (
  `k` varchar(50) character set utf8 NOT NULL,
  `v` text character set utf8 NOT NULL,
  `type` varchar(10) character set utf8 NOT NULL,
  UNIQUE KEY `k` (`k`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `plan`
--

DROP TABLE IF EXISTS `plan`;
CREATE TABLE IF NOT EXISTS `plan` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(125) NOT NULL,
  `actor` varchar(30) default NULL,
  `status` enum('RUNNING','PAUSE','WAITING','HALT','COMPLETE') NOT NULL default 'RUNNING',
  `description` varchar(500) default NULL,
  `target` varchar(500) default NULL,
  `owner` int(11) default NULL,
  `start` date NOT NULL,
  `deadline` date NOT NULL,
  `stepid` int(10) unsigned NOT NULL default '0',
  `depend_plan` int(10) unsigned default NULL,
  `par_plan` int(10) unsigned NOT NULL default '0',
  `summary` varchar(500) default NULL,
  `archive` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
CREATE TABLE IF NOT EXISTS `session` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `skey` varchar(50) NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `user` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `step`
--

DROP TABLE IF EXISTS `step`;
CREATE TABLE IF NOT EXISTS `step` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `action` varchar(124) NOT NULL,
  `status` enum('FINISH','DOING') default NULL,
  `do_it` enum('YES','NO') NOT NULL default 'NO',
  `next` int(11) unsigned default NULL,
  `planid` int(10) unsigned NOT NULL,
  `subplanid` int(10) unsigned default NULL,
  `summary` tinytext,
  `summary_time` datetime default NULL,
  `memo` varchar(1000) default NULL,
  `memo_time` datetime default NULL,
  `done` tinytext,
  `done_time` datetime default NULL,
  `todo` tinytext,
  `todo_time` datetime default NULL,
  `red_tomato` tinyint(1) NOT NULL default '0',
  `green_tomato` tinyint(1) NOT NULL default '0',
  `eaten` tinyint(2) NOT NULL default '0',
  `lastday` date default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `summary_log`
--

DROP TABLE IF EXISTS `summary_log`;
CREATE TABLE IF NOT EXISTS `summary_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `planid` int(10) unsigned NOT NULL,
  `target` varchar(100) default NULL,
  `description` varchar(100) default NULL,
  `summary` varchar(100) NOT NULL,
  `sum_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tomatos`
--

DROP TABLE IF EXISTS `tomatos`;
CREATE TABLE IF NOT EXISTS `tomatos` (
  `planid` int(10) unsigned NOT NULL,
  `stepid` int(10) unsigned NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tomato_timer`
--

DROP TABLE IF EXISTS `tomato_timer`;
CREATE TABLE IF NOT EXISTS `tomato_timer` (
  `uid` int(10) unsigned NOT NULL,
  `planid` int(10) unsigned NOT NULL default '0',
  `stepid` int(10) unsigned NOT NULL default '0',
  `startime` datetime NOT NULL,
  `counts` tinyint(2) unsigned NOT NULL default '0',
  UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL auto_increment,
  `user` varchar(20) NOT NULL,
  `passwd` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
