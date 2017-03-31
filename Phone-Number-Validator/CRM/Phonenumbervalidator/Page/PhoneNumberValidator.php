<?php
/**
 * This class builds the interface page.
 */

require_once 'CRM/Core/Page.php';

class CRM_Phonenumbervalidator_Page_PhoneNumberValidator extends CRM_Core_Page {
  public function run() {
    $regexRuleSets = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator',
      'com.civifirst.phonenumbervalidator.regex_rules');
    
    // TODO error checking in case it doesn't work.  
    $this->assign('regex_rules', $regexRuleSets);
  
    $this->assign('allow_options', array(
      'spaces'    => 'Allow spaces " "',
      'hyphens'   => 'Allow hyphens "-"',
      'fullstops' => 'Allow fullstops "."',
      'brackets'  => 'Allow brackets "(" and ")"',
      'slash'     => 'Allow slashes "/"',
      'plus'      => 'Allow "+" instead of leading 00',
    ));
    
    parent::run();
  }
}
