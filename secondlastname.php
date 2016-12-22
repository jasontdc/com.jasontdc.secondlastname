<?php

require_once 'secondlastname.civix.php';

/**
 * Retrieves the value of the second last name for the specified contact.
 *
 * Returns an empty string if there is an error or no value.
 *
 * @param string $cid
 */
 function getSecondLastName($cid) {
	try {

		//try to get the second last name via the api and return it
		$result = civicrm_api3('CustomValue', 'get', array(
			'sequential' => 1,
			'return.com_jasontdc_secondlastname_group:com_jasontdc_secondlastname_field' => 1,
			'entity_id' => $cid,
		));

		return $result['values'][0]['latest'];

	} catch(Exception $e) {}

	return '';
}

/**
 * Implements hook_civicrm_contact_get_displayname().
 *
 * Reconstructs the display name if it contains the second last name field.
 *
 * @param string $display_name
 * @param string $contactId
 * @param CRM_Core_DAO $objContact
 */
function secondlastname_civicrm_contact_get_displayname(&$display_name, $contactId, $objContact) {
	try {
		//get the display name from the settings
		$result = civicrm_api3('Setting', 'get', array(
			'sequential' => 1,
			'return' => array("display_name_format"),
		));

		//look for the second last name field token in the format string
		if(strpos($result['values'][0]['display_name_format'], '{contact.second_last_name}') !== false) {
			//if the second last name is in the format, retrieve the value for this contact
			$second_last_name = getSecondLastName($contactId);
			//if there's no value, we don't need to do anything
			if($second_last_name) {
				$display_name_format = $result['values'][0]['display_name_format'];
				
				//first, replace the second last name
				$display_name = str_replace('{contact.second_last_name}', $second_last_name, $display_name_format);

				//now, iterate over the other tokens and replace each one
				preg_match_all('/{(.*?)}/', $display_name, $matches);
				for ($i = 0; $i < count($matches[0]); $i++) {
					//by default, we'll replace the token by a blank space unless we find the actual value
					$value = ' '; 
					$matched = $matches[1][$i];

					preg_match('/^contact\.(.*?)$/', $matched, $match);
					if(count($match) > 1) {
						//check to see if we already have the property in objContact
						if(property_exists($objContact, $match[1])) {
							//if we can find it, just grab it from there
							$value = $objContact->{$match[1]};
						} else {
							//otherwise we can try to get it from the API

							//we need to special-case the individual suffixes
							$key = $match[1];
							if($key == 'individual_prefix') {
								$key = 'prefix_id';
							} else if($key == 'individual_suffix') {
								$key = 'suffix_id';
							}

							//try to get the value from the API
							$result = civicrm_api3('Contact', 'get', array(
								'sequential' => 1,
								'return' => array($key),
								'id' => $contactId,
							));
							//if we get a valid value we'll use it; otherwise, do nothing
							if($result && $result["is_error"] == 0) {
								$value = $result["values"][0][$match[1]];
							}
						}
					}

					//do the replacement
					$display_name = str_replace($matches[0][$i], $value, $display_name);
				}

				//now, get rid of any excess spaces--doesn't matter for html, but may be important for other cases
				$display_name = trim(preg_replace('/[\s]+/',' ',$display_name));
			}
		}
	} catch(Exception $e) {}
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * Adds the second last name field to the built-in contact edit forms.
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 */
function secondlastname_civicrm_buildForm($formName, &$form) {
	$templatePath = realpath(dirname(__FILE__)."/templates/CRM/Secondlastname/Form");

  //if we're displaying a contact form, add the second last name field
	if ($formName == 'CRM_Contact_Form_Inline_ContactName' || $formName == 'CRM_Contact_Form_Contact') {

		$value_array = null;

    //try to get the current value of the second last name field if it exists
		if($form->_contactId) {
			$result = civicrm_api3('CustomValue', 'get', array(
				'sequential' => 1,
			  'return.com_jasontdc_secondlastname_group:com_jasontdc_secondlastname_field' => 1,
			  'entity_id' => $form->_contactId,
			));

			if($result && $result['is_error'] == 0) {
				$value_array = array('value' => getSecondLastName($form->_contactId));
			}
		}

    //add the field value to the form
		$form->add('text', 'com_jasontdc_secondlastname_field', ts('Second Last Name'), $value_array);

    //choose the template for displaying the field depending on whether it is inline or not
		if($formName == 'CRM_Contact_Form_Inline_ContactName') {
			CRM_Core_Region::instance('page-body')->add(array(
				'template' => $templatePath . "/secondlastname.div.tpl"
			));
		} else if($formName == 'CRM_Contact_Form_Contact') {
			CRM_Core_Region::instance('page-body')->add(array(
				'template' => $templatePath . "/secondlastname.td.tpl"
			));
		}
	}
}

/**
 * Implements hook_civicrm_postProcess().
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 */
function secondlastname_civicrm_postProcess($formName, &$form) {
  //if the user is posting a contact form, update the second last name field
  if ($formName == 'CRM_Contact_Form_Inline_ContactName' || $formName == 'CRM_Contact_Form_Contact') {
		$result = civicrm_api3('CustomValue', 'create', array(
		  'entity_id' => $form->_contactId,
		  'custom_com_jasontdc_secondlastname_group:com_jasontdc_secondlastname_field' => $form->_submitValues['com_jasontdc_secondlastname_field'],
		));
  }
}


/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function secondlastname_civicrm_config(&$config) {
  _secondlastname_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function secondlastname_civicrm_xmlMenu(&$files) {
  _secondlastname_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function secondlastname_civicrm_install() {
  _secondlastname_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function secondlastname_civicrm_uninstall() {
  _secondlastname_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function secondlastname_civicrm_enable() {
  _secondlastname_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function secondlastname_civicrm_disable() {
  _secondlastname_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function secondlastname_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _secondlastname_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function secondlastname_civicrm_managed(&$entities) {
  _secondlastname_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @param array $caseTypes
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function secondlastname_civicrm_caseTypes(&$caseTypes) {
  _secondlastname_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function secondlastname_civicrm_angularModules(&$angularModules) {
_secondlastname_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function secondlastname_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _secondlastname_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function secondlastname_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function secondlastname_civicrm_navigationMenu(&$menu) {
  _secondlastname_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'com.jasontdc.secondlastname')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _secondlastname_civix_navigationMenu($menu);
} // */
