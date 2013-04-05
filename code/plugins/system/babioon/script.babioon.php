<?php
/**
 * babioon
 * @package    BABIOON_SPLL
 * @author     Robert Deutz <rdeutz@gmail.com>
 * @copyright  2012 Robert Deutz Business Solution
 * @license    GNU General Public License version 2 or later
 **/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of babioon-spll plugin
 *
 * @package  BABIOON_SPLL
 * @since    2.0.0
 */
class PlgSystembabioonInstallerScript
{

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @param   string      $type    Installation type (install, update, discover_install)
	 * @param   JInstaller  $parent  Parent object
	 *
	 * @return void
	 */
	function postflight($type, $parent)
	{
		$src = $parent->getParent()->getPath('source');

		// Install the babioon Library
		$source = $src . '/library/babioon';
		$installer = new JInstaller;
		$result = $installer->install($source);
	}
}
