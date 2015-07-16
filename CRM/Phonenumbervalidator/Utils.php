<?php

class CRM_Phonenumbervalidator_Utils {
  
  /*
   * Install the valid phone number regexes.
   */  
  public static function installPhoneNumberRegexes(){
    // Add valid phone matching regexes. This structure allows each to have its own id and name, but be grouped together in the interface.
    $aValidPhonesRegexes = array(
      'Australia' => array(
        array('label' => 'Australia Landline',               'regex' => '^0[^4][0-9]{8}$'),
        array('label' => 'Australia Mobile',                 'regex' => '^04[0-9]{8}$')
      ),
      'Britain' => array(
        array('label' => 'Britain Landline (local)',         'regex' => '^0[^7][0-9]{9}$'),
        array('label' => 'Britain Mobile (local)',           'regex' => '^07[0-9]{9}$'),
        array('label' => 'Britain Landline (international)', 'regex' => '^0044[^7][0-9]{9}$'),
        array('label' => 'Britain Mobile (international)',   'regex' => '^00447[0-9]{9}$')
      ),
      'France' => array(
        array('label' => 'France Landline (local)',          'regex' => '^0[1|2|3|4|5|8|9][0-9]{8}$'), // 10 digits with 0 instead of 0033 (followed by non zero what is OR in regex?
        array('label' => 'France Mobile (local)',            'regex' => '^0[6|7][0-9]{8}$'), // 06 and 07 are mobile services
        array('label' => 'France Landline (international)',  'regex' => '^0033[1|2|3|4|5|8|9][0-9]{8}$'), // cannot have 00 as 10 digit var
        array('label' => 'France Mobile (international)',    'regex' => '^0033[6|7][0-9]{8}$'),
      ),
    );
    
    CRM_Core_BAO_Setting::setItem($aValidPhonesRegexes, 'com.civifirst.phonenumbervalidator',
        'regex_rules');
    
    // Check if the settings are now present (as setItem returns void).
    $aStoredValidPhonesRegexes = CRM_Core_BAO_Setting::getItem(
      'com.civifirst.phonenumbervalidator', 'regex_rules');
    
    if (!$aStoredValidPhonesRegexes) {
      throw new Exception('Phone Number Validator Install: Could not store the phone regexes.');
    }
  }  
    
  /*
   * Add a placeholder for the last selected values in the interface.
   */
  public static function installLastSelectedSettingsDefault(){
    CRM_Core_BAO_Setting::setItem('-1', 'com.civifirst.phonenumbervalidator', 'last_selected_settings');

    // Check if the settings are now present (as setItem returns void).
    $aStoredLastSelectedSettings = CRM_Core_BAO_Setting::getItem(
      'com.civifirst.phonenumbervalidator', 'last_selected_settings');
    
    if (!$aStoredLastSelectedSettings) {
      throw new Exception('Phone Number Validator Install: Could not store the phone regexes.');
    }
  }
          
  /*
   * Installs some default valid phone validation rules and other settings.
   */
  public static function installDefaults() {
    // Remove the settings if they are already present.
    self::deleteDbSettings();
    self::installPhoneNumberRegexes();
    self::installLastSelectedSettingsDefault();
  }
       
  /*
   * Removes all settings from civicrm DB added by the phonenumbervalidator.
   */
  public static function deleteDbSettings () {
    $deleteMysql = 'DELETE FROM civicrm_setting WHERE group_name = "com.civifirst.phonenumbervalidator"';
    CRM_Core_DAO::executeQuery($deleteMysql);
  }
  
  
  
  /* TODO */
  public static function getRegexRule($regexRuleSets, $ruleId){
    // TODO check that $ruleId has one _ or assert
    if (substr_count($ruleId, '_') != 1){
      throw new exception("Phone Number Validator getRegexRule: Incorrect number of underscores found." . $ruleId); // TODO write to error log   
    }
      
    $ruleIdArrayRaw = explode("_", $ruleId);
    $ruleIdArray = array('country' => $ruleIdArrayRaw[0], 'id' => $ruleIdArrayRaw[1]);
    
    // TODO use better error types.
    
    if (!array_key_exists($ruleIdArray['country'], $regexRuleSets)) {
      throw new exception("Phone Number Validator getRegexRule: Country does not exist - see log error."); // TODO write to error log
    }
    
    $country = $ruleIdArray['country'];
    
    if (!array_key_exists($ruleIdArray['id'], $regexRuleSets[$ruleIdArray['country']])){
      throw new exception("Phone Number Validator getRegexRule: Id does not exist for country " . $ruleIdArray['country'] . " - see log error."); // TODO write to error log
    }
    $id = $ruleIdArray['id'];
    
    return $regexRuleSets[$country][$id]['regex'];
  }
  
  /* TODO */
  public static function getSelectedRegexRules(array $selectedRegexRuleIds){
    $regexRules = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'regex_rules');  
    $selectedRegexRules = array();
    foreach($selectedRegexRuleIds as $selectedRegexRuleId){
      $selectedRegexRules[] = self::getRegexRule($regexRules, $selectedRegexRuleId);
    }
    return $selectedRegexRules;
  }
}