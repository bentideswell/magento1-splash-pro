<?php
/**
 * @category    Fishpig
 * @package    Fishpig_AttributeSplashPro
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

	// Declare helper class
	class Fishpig_AttributeSplashPro_Helper_LegacyHacks extends Mage_Core_Helper_Abstract {}

	$level = error_reporting(0);

	if (!class_exists('Mage_Core_Model_Resource_Db_Abstract')) {
		// Declare legacy hack classes
		abstract class Mage_Core_Model_Resource_Db_Abstract extends Mage_Core_Model_Mysql4_Abstract {}
		abstract class Mage_Core_Model_Resource_Db_Collection_Abstract extends Mage_Core_Model_Mysql4_Collection_Abstract {}
	}
	
	error_reporting($level);
