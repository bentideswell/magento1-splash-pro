<?php
/**
 * @category    Fishpig
 * @package    Fishpig_AttributeSplashPro
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

	$this->startSetup();
	
	$this->getConnection()->addColumn($this->getTable('splash_page'), 'page_layout', " varchar(32) NOT NULL default '' AFTER layout_update_xml");

	$this->endSetup();
	