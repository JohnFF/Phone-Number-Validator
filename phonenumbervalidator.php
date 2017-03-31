<?php

require_once 'phonenumbervalidator.civix.php';

require_once 'CRM/Phonenumbervalidator/Utils.php';

const PV_SUBMENU_LABEL = 'Contact Validation';

/**
 * Implements hook_civicrm_config().
 */
function phonenumbervalidator_civicrm_config(&$config) {
  _phonenumbervalidator_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 */
function phonenumbervalidator_civicrm_xmlMenu(&$files) {
  _phonenumbervalidator_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 */
function phonenumbervalidator_civicrm_install() {
  CRM_Phonenumbervalidator_Utils::installDefaults();
  return _phonenumbervalidator_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 */
function phonenumbervalidator_civicrm_uninstall() {
  CRM_Phonenumbervalidator_Utils::deleteDbSettings();
  return _phonenumbervalidator_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 */
function phonenumbervalidator_civicrm_enable() {
  return _phonenumbervalidator_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 */
function phonenumbervalidator_civicrm_disable() {
  return _phonenumbervalidator_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 */
function phonenumbervalidator_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _phonenumbervalidator_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function phonenumbervalidator_civicrm_managed(&$entities) {
  return _phonenumbervalidator_civix_civicrm_managed($entities);
}

function phonenumbervalidator_civicrm_navigationMenu_getSubmenuKey(&$params, $contactMenuId) {
  foreach ($params[$contactMenuId]['child'] as $key => $item) {
    if ($item['attributes']['label'] == PV_SUBMENU_LABEL) {
      return $item['attributes']['navID'];
    }
  }

  return NULL;
}

/**
 * Implements hook_civicrm_navigationMenu().
 */
function phonenumbervalidator_civicrm_navigationMenu(&$params) {
  // get the id of the Contact Menu
  $contactMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'Contacts', 'id', 'name');

  // Add the "Contact Validation" submenu to 'Contacts', if it doesn't alrady exist
  $subMenuId = phonenumbervalidator_civicrm_navigationMenu_getSubmenuKey($params, $contactMenuId);

  if (!$subMenuId) {
    //  Get the maximum key of $params
    $maxKey = max(array_keys($params[$contactMenuId]['child']));

    $subMenuId = $maxKey + 1;

    $params[$contactMenuId]['child'][$subMenuId] = array(
      'attributes' => array(
        'label'      => PV_SUBMENU_LABEL,
        'name'       => PV_SUBMENU_LABEL,
        'url'        => NULL,
        'permission' => NULL,
        'operator'   => NULL,
        'separator'  => NULL,
        'parentID'   => $contactMenuId,
        'navID'      => $subMenuId,
        'active'     => 1,
      ),
    );
  }

  $phoneValidatorKey = max(array_keys($params[$contactMenuId]['child'])) + 1;

  $params[$contactMenuId]['child'][$subMenuId]['child'][$phoneValidatorKey] = array(
    'attributes' => array(
      'label' => 'Phone Number Validator',
      'name' => 'PhoneValidator',
      'url' => 'civicrm/phonenumbervalidator',
      'permission' => 'view all contacts',
      'operator' => NULL,
      'separator' => FALSE,
      'parentID' => $subMenuId,
      'navID' => $phoneValidatorKey,
      'active' => 1,
    ),
  );
}
