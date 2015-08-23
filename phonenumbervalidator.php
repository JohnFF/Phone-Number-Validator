<?php

require_once 'phonenumbervalidator.civix.php';

require_once 'CRM/Phonenumbervalidator/Utils.php';

const PV_SUBMENU_LABEL = 'Contact Validation';

/**
 * Implementation of hook_civicrm_config
 */
function phonenumbervalidator_civicrm_config(&$config) {
  _phonenumbervalidator_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function phonenumbervalidator_civicrm_xmlMenu(&$files) {
  _phonenumbervalidator_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function phonenumbervalidator_civicrm_install() {
  CRM_Phonenumbervalidator_Utils::installDefaults();
  return _phonenumbervalidator_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function phonenumbervalidator_civicrm_uninstall() {
  CRM_Phonenumbervalidator_Utils::deleteDbSettings();
  return _phonenumbervalidator_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function phonenumbervalidator_civicrm_enable() {
  return _phonenumbervalidator_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function phonenumbervalidator_civicrm_disable() {
  return _phonenumbervalidator_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function phonenumbervalidator_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _phonenumbervalidator_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function phonenumbervalidator_civicrm_managed(&$entities) {
  return _phonenumbervalidator_civix_civicrm_managed($entities);
}

function phonenumbervalidator_civicrm_navigationMenu_getSubmenuKey( &$params, $contactMenuId ){
  foreach($params[$contactMenuId]['child'] as $key => $item){
    if ($item['attributes']['label'] == PV_SUBMENU_LABEL){
      return $item['attributes']['navID'];
    }       
  }

  return NULL;
}

function phonenumbervalidator_civicrm_navigationMenu( &$params ) {
  // get the id of the Contact Menu
  $contactMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'Contacts', 'id', 'name');

  // Add the "Contact Validation" submenu to 'Contacts', if it doesn't alrady exist
  $subMenuId = phonenumbervalidator_civicrm_navigationMenu_getSubmenuKey($params, $contactMenuId);
    
  if (!$subMenuId){
    //  Get the maximum key of $params
    $maxKey = max( array_keys($params[$contactMenuId]['child']));

    $subMenuId = $maxKey+1;

    $params[$contactMenuId]['child'][$subMenuId] = array (
      'attributes' => array (
         'label'      => PV_SUBMENU_LABEL,
         'name'       => PV_SUBMENU_LABEL,
         'url'        => null,
         'permission' => null,
         'operator'   => null,
         'separator'  => null,
         'parentID'   => $contactMenuId,
         'navID'      => $subMenuId,
         'active'     => 1
      )
    );
  }

  $phoneValidatorKey = max(array_keys($params[$contactMenuId]['child']))+1;

  $params[$contactMenuId]['child'][$subMenuId]['child'][$phoneValidatorKey] = array (
    'attributes' => array (
      'label' => 'Phone Number Validator',
      'name' => 'PhoneValidator',
      'url' => 'civicrm/phonenumbervalidator',
      'permission' => 'view all contacts',
      'operator' => NULL,
      'separator' => FALSE,
      'parentID' => $subMenuId,
      'navID' => $phoneValidatorKey,
      'active' => 1
    )
  );
}
