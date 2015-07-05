<?php

CONST RETURN_LIMIT = 50; 

/**
 * PhoneNumberValidator.Getinvalidphones API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_phone_number_validator_getinvalidphones_spec(&$spec) {
  $spec['selectedRegexIds']['api.required'] = 1;
  // TODO make below variables allowable
  // $spec['selectedPhoneTypeId']['api.required'] = 1; 
  // $spec['selectedContactTypeId']['api.required'] = 1;
}

/**
 * PhoneNumberValidator.Getinvalidphones API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_phone_number_validator_getinvalidphones($params) {

  $selectedContactTypeId = $params['selectedContactTypeId'];
  $selectedPhoneTypeId   = $params['selectedPhoneTypeId'];
    
  $selectedRegexRuleIds = $params['selectedRegexIds'];
  $selectedAllowCharacterRules = $params['selectedAllowCharacters'];
  
  // We use one large SQL query instead of API calls for efficiency.
  $getBrokenPhonesSelectSql = "SELECT contact.id AS contact_id, "
      . "display_name, "
      . "phone.id AS phone_id, "
      . "phone AS phone_number, "
      . "phone_type_id, phone_ext ";
  
  // Assemble FROM statement.
  $getBrokenPhonesFromSql = CRM_Phonenumbervalidator_Utils::buildFromStatementMyqlString($selectedRegexRuleIds, $selectedAllowCharacterRules);
  
  // Assemble WHERE statement.
  $getBrokenPhonesWhereSqlArray = CRM_Phonenumbervalidator_Utils::buildWhereStatementMysqlString($selectedContactTypeId, $selectedPhoneTypeId);
   
  try {
    $queryString = $getBrokenPhonesSelectSql . 
        $getBrokenPhonesFromSql . 
        $getBrokenPhonesWhereSqlArray['statement'] . 
        "LIMIT " . RETURN_LIMIT;
      
    $dao = CRM_Core_DAO::executeQuery(
      $queryString, 
      $getBrokenPhonesWhereSqlArray['params']);
       
    $returnValues = array();
    
    while ($dao->fetch()){
        
      $rawReturnValues = array(
        'contact_id' => $dao->contact_id,
        'display_name' => $dao->display_name,
        'phone_id' => $dao->phone_id,
        'phone_number' => $dao->phone_number,
        'phone_type_id' => $dao->phone_type_id,
        'phone_ext' => $dao->phone_ext,
      );
      
      if ($rawReturnValues['phone_ext'] == NULL){
        $rawReturnValues['phone_ext'] = '';
      }
      
      $returnValues[] = $rawReturnValues;
    }
    
    return civicrm_api3_create_success($returnValues, $params, 'PhoneNumberValidator', 'Getinvalidphones');
  }
  catch (Exception $e){
    return civicrm_api3_create_error(
      $e->getMessage() . " with sql " . 
      $getBrokenPhonesSelectSql . 
      $getBrokenPhonesFromSql . 
      $getBrokenPhonesWhereSqlArray['statement']
    );
  }
  
  return civicrm_api3_create_success($returnValues, $params, 'PhoneNumberValidator', 'Getinvalidphones');
}

