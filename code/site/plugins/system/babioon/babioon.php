<?php
/**
 * BABIOON System Plugin
 * @author Robert Deutz (email contact@rdbs.net / site www.rdbs.de)
 * @version $Id: rdbs.php 15 2010-09-03 13:15:26Z deutz $
 * @package BABIOON_SYSTEM_PLUGIN
 *
 * This is free software
 *
 **/

/**
 * RDBS System Plugin
 *
 * @author		Robert Deutz
 * @package		BABIOON_SYSTEM_PLUGIN
 */
class plgSystemBabioon extends JPlugin
{
	public function __construct($subject, $config = array())
	{
		if (!defined('SITEROOTDIR')) define ('SITEROOTDIR',JPATH_ROOT);
		// load the plugin
		// Require the library loader
		JLoader::import('libraries.babioon.babioon', SITEROOTDIR);
		JLoader::import('libraries.babioon.loader.loader', SITEROOTDIR);

		parent::__construct($subject, $config = array());
	}
	
	function onAfterInitialise()
	{
	    $mydir = dirname(__FILE__);
		include $mydir .'/patched/administrator/components/com_config/models/component.php';
		 
		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
		    $doc=JFactory::getDocument();
		    $doc->addStyleSheet(JURI::base(true).'/../media/babioon/css/rules.css');
		}
		
	}
	
}