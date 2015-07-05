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
  $selectedAllowCharacterRules = $params['selectedAllowCharacters'];
  
  // We use one large SQL query instead of API calls for efficiency.
  $getBrokenPhonesCountSql = "SELECT count(contact.id) AS count ";
  
  // Assemble FROM statement.
  $getBrokenPhonesFromSql = CRM_Phonenumbervalidator_Utils::buildFromStatementMyqlString($selectedRegexRuleIds, $selectedAllowCharacterRules);
  
  // Assemble WHERE statement.
  $getBrokenPhonesWhereSqlArray = CRM_Phonenumbervalidator_Utils::buildWhereStatementMysqlString($selectedContactTypeId, $selectedPhoneTypeId);
   
  try {
    $queryString = $getBrokenPhonesCountSql . 
        $getBrokenPhonesFromSql . 
        $getBrokenPhonesWhereSqlArray['statement'];
     
    $dao = CRM_Core_DAO::executeQuery(
      $queryString, 
      $getBrokenPhonesWhereSqlArray['params']);
       
    $returnValues = array();
    
    $dao->fetch();
    $returnValues['count'] = $dao->count;
    
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

