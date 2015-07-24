<?php

class CRM_Phonenumbervalidator_Utils {

  /*
   * Install the valid phone number regexes.
   */
  public static function installPhoneNumberRegexes(){
    // Add valid phone matching regexes. This structure allows each to have its own id and name, but be grouped together in the interface.
    $aValidPhonesRegexes = array(
      'Australia' => array(
        array('label' => 'Australian landlines (local)',         'regex' => '^0[^4][0-9]{8}$'),
        array('label' => 'Australian mobiles (local)',           'regex' => '^04[0-9]{8}$'),
        array('label' => 'Australian landlines (international)', 'regex' => '^0061[^4][0-9]{8}$'),
        array('label' => 'Australian mobiles (international)',   'regex' => '^00614[0-9]{8}$'),
      ),
      'Britain' => array(
        array('label' => 'British landlines (local)',            'regex' => '^0[^7][0-9]{9}$'),
        array('label' => 'British mobiles (local)',              'regex' => '^07[0-9]{9}$'),
        array('label' => 'British landlines (international)',    'regex' => '^0044[^7][0-9]{9}$'),
        array('label' => 'British mobiles (international)',      'regex' => '^00447[0-9]{9}$'),
      ),
      'Denmark' => array(
        array('label' => 'Danish phones (local)',                'regex' => '^[0-9]{8}$'),
        array('label' => 'Danish phones (international)',        'regex' => '^0045[0-9]{8}$'),
      ),
      'France' => array(
        array('label' => 'French landlines (local)',             'regex' => '^0[1|2|3|4|5|8|9][0-9]{8}$'), // 10 digits with 0 instead of 0033 (followed by non zero what is OR in regex?
        array('label' => 'French mobiles (local)',               'regex' => '^0[6|7][0-9]{8}$'), // 06 and 07 are mobiles services
        array('label' => 'French landlines (international)',     'regex' => '^0033[1|2|3|4|5|8|9][0-9]{8}$'), // cannot have 00 as 10 digit var
        array('label' => 'French mobiles (international)',       'regex' => '^0033[6|7][0-9]{8}$'),
      ),
      'Ireland' => array(
        array('label' => 'Irish phones (local)',                 'regex' => '^1[0-9]{7}$'), // TODO can we include mobiles v landlines distinction?
        array('label' => 'Irish phones (international)',         'regex' => '^00353[0-9]{7}$'),
      ),
      'The Netherlands' => array(
        array('label' => 'Dutch phones (local)',                 'regex' => '^[0-9]{9}$'), // TODO can we include mobiles v landlines distinction?
        array('label' => 'Dutch phones (international)',         'regex' => '^0031[0-9]{9}$'),
      ),
      'Norway' => array(
        array('label' => 'Norwegian landlines (local)',          'regex' => '^[^4|9][0-9]{7}$'),
        array('label' => 'Norwegian mobiles (local)',            'regex' => '^[4|9][0-9]{7}$'),
        array('label' => 'Norwegian landlines (international)',  'regex' => '^0047[^4|9][0-9]{7}$'),
        array('label' => 'Norwegian mobiles (international)',    'regex' => '^0047[4|9][0-9]{7}$'),
      ),
      'Poland' => array(
        array('label' => 'Polish landlines (local)',             'regex' => '^[^{5|6|7|8}][0-9]{8}$'), // 9 digits.
        array('label' => 'Polish mobiles (local)',               'regex' => '^{5|6|7|8}[0-9]{8}$'), //  5, 6, 7 or 8 as lead indicate mobile
        array('label' => 'Polish landlines (international)',     'regex' => '^0048[^{5|6|7|8}][0-9]{8}$'),
        array('label' => 'Polish mobiles (international)',       'regex' => '^0048{5|6|7|8}[0-9]{8}$'),
      ),
      'Spain' => array(
        array('label' => 'Spanish landlines (local)',            'regex' => '^9[^{6|7}][0-9]{8}$'), // 10 digits with not 6 or 7 as the second digit
        array('label' => 'Spanish mobiles (local)',              'regex' => '^9[6|7][0-9]{8}$'),
        array('label' => 'Spanish landlines (international)',    'regex' => '^00349[^{6|7}][0-9]{8}$'),
        array('label' => 'Spanish mobiles (international)',      'regex' => '^00349[6|7][0-9]{8}$'),
      ),
      'Switzerland' => array(
        array('label' => 'Swiss phones (local)',                 'regex' => '^0[0-9]{9}$'),
        array('label' => 'Swiss phones (international)',         'regex' => '^0041[0-9]{9}$'),
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
  public static function deleteDbSettings() {
    $deleteMysql = 'DELETE FROM civicrm_setting WHERE group_name = "com.civifirst.phonenumbervalidator"';
    CRM_Core_DAO::executeQuery($deleteMysql);
  }

  /* 
   * For a given rule id returns the raw regex rule.
   * @param array $regexRuleSets
   * @param string $ruleId
   * @return string regex
   */
  private static function getRegexRule($regexRuleSets, $ruleId){
    if (substr_count($ruleId, '_') != 1){
      $errorMessage = "Phone Number Validator getRegexRule: Incorrect number of underscores found." . $ruleId;
      CRM_Core_Error::debug($errorMessage);
      throw new exception($errorMessage);
    }

    $ruleIdArrayRaw = explode("_", $ruleId);
    $ruleIdArray = array('country' => $ruleIdArrayRaw[0], 'id' => $ruleIdArrayRaw[1]);

    if (!array_key_exists($ruleIdArray['country'], $regexRuleSets)) {
      $errorMessage = "Phone Number Validator getRegexRule: Country does not exist - see log error.";
      CRM_Core_Error::debug($errorMessage);
      throw new exception($errorMessage);
    }

    $country = $ruleIdArray['country'];

    if (!array_key_exists($ruleIdArray['id'], $regexRuleSets[$ruleIdArray['country']])){
      $errorMessage = "Phone Number Validator getRegexRule: Id does not exist for country " . $ruleIdArray['country'] . " - see log error.";
      CRM_Core_Error::debug($errorMessage);
      throw new exception($errorMessage);
    }
    $id = $ruleIdArray['id'];

    return $regexRuleSets[$country][$id]['regex'];
  }

  /* 
   * For a given list of ids, return the associated rules.
   * @param array $selectedRegexRuleIds
   * @return array of regexes
   */
  public static function getSelectedRegexRules(array $selectedRegexRuleIds){
    $regexRules = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'regex_rules');
    $selectedRegexRules = array();
    foreach($selectedRegexRuleIds as $selectedRegexRuleId){
      $selectedRegexRules[] = self::getRegexRule($regexRules, $selectedRegexRuleId);
    }
    return $selectedRegexRules;
  }
}