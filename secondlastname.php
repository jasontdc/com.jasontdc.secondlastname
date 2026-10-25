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

		return $result['values'][0]['latest'] ?? '';

	} catch(Exception $e) {}

	return '';
}

/**
 * Implements hook_civicrm_tokens().
 *
 * Defines the tokens provided by this module. 
 * There is only one token: contact.second_last_name
 *
 * @param array $tokens
 */
function secondlastname_civicrm_tokens(&$tokens) {
	// Define the contact.second_last_name token
  $tokens['contact'] = array(
    'contact.second_last_name' => 'Second Last Name',
  );
}

/**
 * Implements hook_civicrm_tokenValues().
 *
 * Reconstructs the display name if it contains the second last name field.
 *
 * @param array $values
 * @param array $cids
 * @param string $job
 * @param array $tokens
 * @param string $context
 */
function secondlastname_civicrm_tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
  // Retrieve the token and add it
  if (!empty($tokens['contact'])) {
    foreach ($cids as $cid) {
			$second_last_name = array(
				'contact.second_last_name' => getSecondLastName($cid),
			);
			if(empty($values[$cid])) {
				$values[$cid] = $second_last_name;
			} else {
				$values[$cid] = $values[$cid] + $second_last_name;
			}
    }
  }
}

/**
 * Implements hook_civicrm_pre().
 *
 * Adds the second_last_name field to the contact parameters so it can be used as a
 * token in the display and sort names.
 *
 * @param string $op
 * @param string $objectName
 * @param string $id
 * @param array $params
 */
function secondlastname_civicrm_pre($op, $objectName, $id, &$params) {
	if($objectName == 'Individual') {
		if($op == 'view') {
  		$params['contact.second_last_name'] = getSecondLastName($id);
		} else if($op == 'create' || $op == 'edit') {
			if(array_key_exists('com_jasontdc_secondlastname_field', $params)) {
				$params['contact.second_last_name'] = $params['com_jasontdc_secondlastname_field'];
			} else if($id) {
				$params['contact.second_last_name'] = getSecondLastName($id);
			} else {
				$params['contact.second_last_name'] = NULL;
			}
		}
	}
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

	//if we're displaying a contact form, add the second last name field
	if ($formName == 'CRM_Contact_Form_Inline_ContactName' || $formName == 'CRM_Contact_Form_Contact') {

		//We only want to add the field for Individuals, since Households and
		//Organizations don't have a second last name.
		//This also adds the field for sub-types of the Individual contact type.
		if(isset($form->_contactType) && $form->_contactType == "Individual") {

			$value_array = null;

			//try to get the current value of the second last name field if it exists
			if(isset($form->_contactId)) {
				$value_array = array('value' => getSecondLastName($form->_contactId));
			}

			//add the field value to the form
			$form->add('text', 'com_jasontdc_secondlastname_field', ts('Second Last Name'), $value_array);

			//choose the template for displaying the field depending on whether it is inline or not
			if($formName == 'CRM_Contact_Form_Inline_ContactName') {
				CRM_Core_Region::instance('page-body')->add([
					'template' => 'CRM/Secondlastname/Form/secondlastname.div.tpl',
				]);
			} else if($formName == 'CRM_Contact_Form_Contact') {
				CRM_Core_Region::instance('page-body')->add([
					'template' => 'CRM/Secondlastname/Form/secondlastname.td.tpl',
				]);
			}
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
	// If the user is posting a contact form, update the second last name field
	if ($formName == 'CRM_Contact_Form_Inline_ContactName' || $formName == 'CRM_Contact_Form_Contact') {
		// Only set the second last name field for individuals
		if(isset($form->_contactType) && $form->_contactType == "Individual" && isset($form->_submitValues['com_jasontdc_secondlastname_field'], $form->_contactId)) {
			$result = civicrm_api3('CustomValue', 'create', array(
				'entity_id' => $form->_contactId,
				'custom_com_jasontdc_secondlastname_group:com_jasontdc_secondlastname_field' => $form->_submitValues['com_jasontdc_secondlastname_field'],
			));
		}
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
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function secondlastname_civicrm_install() {
  _secondlastname_civix_civicrm_install();
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
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *

 // */

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
} // */
