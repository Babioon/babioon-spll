<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_config
 */
class ConfigModelComponent extends JModelForm
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function populateState()
	{
		// Set the component (option) we are dealing with.
		$component = JRequest::getCmd('component');
		$this->setState('component.option', $component);

		// Set an alternative path for the configuration file.
		if ($path = JRequest::getString('path')) {
			$path = JPath::clean(JPATH_SITE . '/' . $path);
			JPath::check($path);
			$this->setState('component.path', $path);
		}
	}

	/**
	 * Method to get a form object.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 *
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		if ($path = $this->getState('component.path')) {
			// Add the search path for the admin component config.xml file.
			JForm::addFormPath($path);
		}
		else {
			// Add the search path for the admin component config.xml file.
			JForm::addFormPath(JPATH_ADMINISTRATOR.'/components/'.$this->getState('component.option'));
		}

		// Get the form.
		$form = $this->loadForm(
				'com_config.component',
				'config',
				array('control' => 'jform', 'load_data' => $loadData),
				false,
				'/config'
			);

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Get the component information.
	 *
	 * @return	object
	 * @since	1.6
	 */
	function getComponent()
	{
		// Initialise variables.
		$option = $this->getState('component.option');

		// Load common and local language files.
		$lang = JFactory::getLanguage();
			$lang->load($option, JPATH_BASE, null, false, false)
		||	$lang->load($option, JPATH_BASE . "/components/$option", null, false, false)
		||	$lang->load($option, JPATH_BASE, $lang->getDefault(), false, false)
		||	$lang->load($option, JPATH_BASE . "/components/$option", $lang->getDefault(), false, false);

		$result = JComponentHelper::getComponent($option);

		return $result;
	}

	/**
	 * Method to save the configuration data.
	 *
	 * @param	array	An array containing all global config data.
	 *
	 * @return	bool	True on success, false on failure.
	 * @since	1.6
	 */
	public function save($data)
	{
		$table	= JTable::getInstance('extension');

		// Save the rules.
		$rulesFields = array();
		$fields	= $this->getForm()->getFieldset();
		foreach ($fields as $f)
		{
			if ($f instanceof JFormFieldRules)
			{
				$rulesFields[] = $f->fieldname;
				$section = 'component';
				if (method_exists($f, 'getSectionProperty'))
				{
					$section = $f->getSectionProperty();
				}
				if (isset($data['params']) && isset($data['params'][$f->fieldname])) {
					$rules	= new JAccessRules($data['params'][$f->fieldname]);
					$asset	= JTable::getInstance('asset');
					$level 	= 1;
					$name=$data['option'];
					if ($section != 'component' && trim($section) != '') {
						$name .= '.'.$section;
						$level = 2;
					}
					if (!$asset->loadByName($name))
					{
						// seems that we don't have row in the DB for this, lets create one
						if ($level == 2) {
							// we need to make sure that we have a level one row in our DB
							$parentAsset = JTable::getInstance('asset');
							if (!$parentAsset->loadByName($data['option'])) {
								// no level one row in out DB, then lets create this
								$root	= JTable::getInstance('asset');
								$root->loadByName('root.1');
								$parentAsset->name 	= $data['option'];
								$parentAsset->title = $data['option'];
								$parentAsset->setLocation($root->id, 'last-child');

								if (!$parentAsset->check() || !$parentAsset->store()) {
									$this->setError($parentAsset->getError());
									return false;
								}
							}
							$asset->setLocation($parentAsset->id, 'last-child');
						}
						else
						{
							// level is == 1
							$root	= JTable::getInstance('asset');
							$root->loadByName('root.1');
							$asset->setLocation($root->id, 'last-child');
						}

						$asset->name 	= $name;
						$asset->title 	= $name;
					}
					$asset->rules = (string) $rules;

					if (!$asset->check() || !$asset->store()) {
						$this->setError($asset->getError());
						return false;
					}
				}
			}
		}


		// We don't need this anymore
		unset($data['option']);
		unset($data['params']['rules']);

		// Load the previous Data
		if (!$table->load($data['id'])) {
			$this->setError($table->getError());
			return false;
		}

		unset($data['id']);

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		// Clean the cache.
		$this->cleanCache('_system', 0);
		$this->cleanCache('_system', 1);

		return true;
	}
}
