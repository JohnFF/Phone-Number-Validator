<?php

/**
 * PhoneNumberValidator.Getinvalidphonescount API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_phone_number_validator_getinvalidphonescount_spec(&$spec) {
  $spec['selectedRegexIds']['api.required'] = 1;
  // TODO make below variables allowable
  // $spec['selectedPhoneTypeId']['api.required'] = 1; 
  // $spec['selectedContactTypeId']['api.required'] = 1;
}

/**
 * PhoneNumberValidator.Getinvalidphonescount API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_phone_number_validator_getinvalidphonescount($params) {
  $selectedContactTypeId = $params['selectedContactTypeId'];
  $selectedPhoneTypeId   = $params['selectedPhoneTypeId'];
    
  $selectedRegexRuleIds = $params['selectedRegexIds'];
  $selectedSubstitutionRuleIds = $params['selectedAllowCharactersIds'];
  
  try {
    $selectedRegexRules = CRM_Phonenumbervalidator_Utils::getSelectedRegexRules($selectedRegexRuleIds);
  
    $invalidNumberRetriever = new CRM_Phonenumbervalidator_InvalidNumberRetriever($selectedRegexRules, $selectedSubstitutionRuleIds, $selectedContactTypeId, $selectedPhoneTypeId);
    
    $returnValues = $invalidNumberRetriever->getInvalidPhoneNumbersCount();
    
    return civicrm_api3_create_success($returnValues, $params, 'PhoneNumberValidator', 'Getinvalidphonescount');
  }
  catch (Exception $e){
    return civicrm_api3_create_error(
      $e->getMessage() . " with sql " . 
      $getBrokenPhonesSelectSql . 
      $getBrokenPhonesFromSql . 
      $getBrokenPhonesWhereSqlArray['statement']
    );
  }
  
  return civicrm_api3_create_success($returnValues, $params, 'PhoneNumberValidator', 'Getinvalidphonescount');
}

