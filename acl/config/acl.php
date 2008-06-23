<?php
/**
 * Khaos :: Khacl
 * 
 * @author      David Cole <neophyte@sourcetutor.com>
 * @author      dlib  <dlib@hichte.nl>
 */

$config['tables']=array(
            'aros'           => 'aros',
            'acos'           => 'acos',
            'axos'           => 'axos',
            'access'         => 'access',
            'access_actions' => 'access_actions'
        );

$config['cache']=true;

/* SQL for the Acl tables
 * 
 * 
-- phpMyAdmin SQL Dump
-- version 2.11.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 03, 2008 at 10:02 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.4-2ubuntu5.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
--

-- --------------------------------------------------------

--
-- Table structure for table `access`
--

CREATE TABLE IF NOT EXISTS `access` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `aro_id` int(10) unsigned NOT NULL default '0',
  `aco_id` int(10) unsigned NOT NULL default '0',
  `allow` char(1) default NULL,
  PRIMARY KEY  (`id`),
  KEY `relation` (`aro_id`,`aco_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `access_actions`
--

CREATE TABLE IF NOT EXISTS `access_actions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `access_id` int(10) unsigned NOT NULL default '0',
  `axo_id` int(10) unsigned NOT NULL default '0',
  `allow` char(1) default NULL,
  PRIMARY KEY  (`id`),
  KEY `access_id` (`access_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `acos`
--

CREATE TABLE IF NOT EXISTS `acos` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `lft` int(10) unsigned NOT NULL default '0',
  `rgt` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `link` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `branch` (`lft`,`rgt`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `aros`
--

CREATE TABLE IF NOT EXISTS `aros` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `lft` int(10) unsigned NOT NULL default '0',
  `rgt` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `link` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `branch` (`lft`,`rgt`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `axos`
--

CREATE TABLE IF NOT EXISTS `axos` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;
 * 
 */