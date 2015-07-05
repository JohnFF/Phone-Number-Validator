<?php

class CRM_Phonenumbervalidator_Utils {
  
  /*
   * Installs some default valid phone validation rules and other settings.
   */
  // TODO split into two install functions  
  public static function installDefaults() {
    // Check if the setting is already present.
    $aValidPhonesRegex = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'regex_rules');
    if ($aValidPhonesRegex) {
      return TRUE;
    }

    // Add valid phone matching regexes. This structure allows each to have its own id and name, but be grouped together in the interface.
    $aValidPhonesRegex = array(
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
    
    CRM_Core_BAO_Setting::setItem($aValidPhonesRegex, 'com.civifirst.phonenumbervalidator',
        'regex_rules');
    CRM_Core_BAO_Setting::setItem('-1', 'com.civifirst.phonenumbervalidator', 'last_selected_regex');

    // Check if the settings are now present (as setItem returns void).
    $aStoredCountryRegex = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator',
        'regex_rules');
    $iStoredLastSelectedRegex = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator',
        'last_selected_regex');
    if ($aStoredCountryRegex && $iStoredLastSelectedRegex) {
      return TRUE;
    }

    throw new Exception('Could not create the regex settings. Country Regex: ' . 
        $aValidPhonesRegex . 'Last Selected: '. $iLastSelectedRegex);
  }
  
  public static function deleteDbSettings () {
    $deleteMysql = 'DELETE FROM civicrm_setting WHERE group_name = "com.civifirst.phonenumbervalidator"';
    CRM_Core_DAO::executeQuery($deleteMysql);
  }
  
  /* TODO */
  public static function buildReplacementMysqlString ($selectedAllowCharactersArray) {
    
    if (in_array('plus', $selectedAllowCharactersArray)){
      // Replace the + with 00 only for the first letter
      // then concatenate it with the rest of the phone number
      $mysqlPhoneString = "CONCAT(REPLACE(SUBSTRING(phone,1,1), '+', '00'), "
              . "SUBSTRING(phone,2,LENGTH(phone)-1))";
    }
    else {
      $mysqlPhoneString = "phone";
    }
    
    $charactersToAllowArray = array();
    
    if (in_array('hyphens', $selectedAllowCharactersArray)) {
      $charactersToAllowArray[] = '-';
    }
    
    if (in_array('fullstops', $selectedAllowCharactersArray)) {
      $charactersToAllowArray[] = '.';
    }

    if (in_array('brackets', $selectedAllowCharactersArray)) {
      $charactersToAllowArray[] = '(';
      $charactersToAllowArray[] = ')';
    }
    
    if (in_array('spaces', $selectedAllowCharactersArray)){
      $charactersToAllowArray[] = ' ';
    }
    
    foreach ($charactersToAllowArray as $characterToAllow) {
      $mysqlPhoneString = "REPLACE($mysqlPhoneString, '$characterToAllow', '')";
    }
    
    return $mysqlPhoneString;
  }
  
  /* TODO */
  public static function getRegexRule($regexRuleSets, $ruleId){
    // TODO check that $ruleId has one _ or assert
      
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
  public static function buildFromStatementMyqlString ($selectedRegexRuleIds, $selectedAllowCharacterRules) {
    $getBrokenPhonesFromSql = "FROM ";
    $getBrokenPhonesFromSql .= "(SELECT id, phone, phone_ext, phone_type_id, contact_id "
        . "FROM civicrm_phone WHERE ";

    $regexRules = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'regex_rules');

    $fromRegexSql = array();

    $phoneMysqlString = self::buildReplacementMysqlString($selectedAllowCharacterRules);

    foreach($selectedRegexRuleIds as $ruleId){
      $fromRegexSql[] = "($phoneMysqlString NOT REGEXP '" . self::getRegexRule($regexRules, $ruleId) . "')";
    }

    $getBrokenPhonesFromSql .=  implode(" AND ", $fromRegexSql) . ") AS phone ";
    $getBrokenPhonesFromSql .= 'JOIN civicrm_contact AS contact '
        . 'ON phone.contact_id = contact.id ';

    return $getBrokenPhonesFromSql;
  }
  
  public static function buildWhereStatementMysqlString($selectedContactTypeId, $selectedPhoneTypeId){
    
    $getBrokenPhonesWhereSql = "WHERE 1 ";
    $queryParameters = array();
    
    if ($selectedContactTypeId){
      // Check that we have been passed an integer.
      if (intval($selectedContactTypeId) == 0) {
        throw new exception("Phone Number Validator - passed an invalid selected contact type id. String received");
      }
      
      // Retrieve information about the civicrm contact type. via the api
      $getContactTypesParams = array(
        'version' => 3,
        'sequential' => 1,
        'id' => $selectedContactTypeId,
      );
      $getContactTypesResults = civicrm_api('ContactType', 'getsingle', $getContactTypesParams);
    
      if (civicrm_error($getContactTypesResults)){
        // TODO 
      }
      
      // If the contact type has a parent id then it is a contact sub type.
      // Otherwise it's a contact type.
      if (array_key_exists('parent_id', $getContactTypesResults)){
        $getBrokenPhonesWhereSql .= "AND contact_sub_type LIKE '%%1%' ";
        $queryParameters['1'] = array($getContactTypesResults['name'], 'String', CRM_Core_DAO::QUERY_FORMAT_NO_QUOTES);
      }
      else { 
        $getBrokenPhonesWhereSql .= "AND contact_type LIKE '%%1%' ";
        $queryParameters['1'] = array($getContactTypesResults['name'], 'String', CRM_Core_DAO::QUERY_FORMAT_NO_QUOTES);
      }
    }

    if ($selectedPhoneTypeId){
      $getBrokenPhonesWhereSql .= "AND phone_type_id = '%2' ";
      $queryParameters['2'] = array($selectedPhoneTypeId, 'Int');  
    }
    
    return array('statement' => $getBrokenPhonesWhereSql, 'params' => $queryParameters);
  }
}