<?php

/**
 * This object retrieves invalid numbers phone numbers from the DAO object it is
 * passed, according to the rules that it is created with. 
 *
 * @author john
 */
class CRM_Phonenumbervalidator_InvalidNumberRetriever {
  
  const DEFAULT_RESULT_LIMIT = 50;
  
  private $sqlFromClause;
  private $sqlWhereClause;
  private $sqlResultLimit;
  
  function __construct($regexRules, $allowRules, $selectedContactTypeId, $selectedPhoneTypeId, $resultLimit = self::DEFAULT_RESULT_LIMIT) {
    watchdog("phone num val", 'step 1', array(), WATCHDOG_ERROR);
    $this->sqlFromClause = self::buildFromStatementMyqlString($regexRules, $allowRules);
    
    watchdog("phone num val", 'step 2', array(), WATCHDOG_ERROR);
    $this->sqlWhereClause = self::buildWhereStatementMysqlString($selectedContactTypeId, $selectedPhoneTypeId);
    
    watchdog("phone num val", 'step 3', array(), WATCHDOG_ERROR);
    $this->sqlResultLimit = $resultLimit;
    
    watchdog("phone num val", 'step 4', array(), WATCHDOG_ERROR);
  }
  
  /*
   * Performs the SQL query needed.
   */
  public function getInvalidPhoneNumbers(){
    $getBrokenPhonesSelectSql = "SELECT contact.id AS contact_id, "
      . "display_name, "
      . "phone.id AS phone_id, "
      . "phone AS phone_number, "
      . "phone_type_id, phone_ext ";  
      
    $queryString = $getBrokenPhonesSelectSql . 
      $this->sqlFromClause . 
      $this->sqlWhereClause['statement'] . 
      "LIMIT " . $this->sqlResultLimit;
         
    // TODO TRY
    $dao = CRM_Core_DAO::executeQuery(
      $queryString, 
      $this->sqlWhereClause['params']);
    
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
    
    return $returnValues;
  }
  
  public function getInvalidPhoneNumbersCount(){
    $getBrokenPhonesCountSql = "SELECT count(contact.id) AS count ";
  
    $queryString = $getBrokenPhonesCountSql . 
      $this->getBrokenPhonesFromSql . 
      $this->getBrokenPhonesWhereSqlArray['statement'];
     
    $dao = CRM_Core_DAO::executeQuery(
      $queryString, 
      $this->getBrokenPhonesWhereSqlArray['params']);
       
    $returnValues = array();
    
    $dao->fetch();
    $returnValues['count'] = $dao->count;
    
    return $returnValues;
  }
  
  /* TODO */
  public static function buildSubstitutionMysqlString ($selectedAllowCharactersArray) {
    
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
  public static function buildFromStatementMyqlString ($selectedRegexRules, $selectedAllowCharacterRules) {
    $getBrokenPhonesFromSql = "FROM ";
    $getBrokenPhonesFromSql .= "(SELECT id, phone, phone_ext, phone_type_id, contact_id "
        . "FROM civicrm_phone WHERE ";

    $fromRegexSql = array();

    $phoneMysqlString = self::buildReplacementMysqlString($selectedAllowCharacterRules);

    foreach($selectedRegexRules as $rule){
      $fromRegexSql[] = "($phoneMysqlString NOT REGEXP '" . $rule . "')";
    }

    $getBrokenPhonesFromSql .=  implode(" AND ", $fromRegexSql) . ") AS phone ";
    $getBrokenPhonesFromSql .= 'JOIN civicrm_contact AS contact '
        . 'ON phone.contact_id = contact.id ';

    return $getBrokenPhonesFromSql;
  }
  
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
  
  public function getErrorDetails(){
    // TODO
//    return " with sql " . 
//    $getBrokenPhonesSelectSql . 
//    $getBrokenPhonesFromSql . 
//    $getBrokenPhonesWhereSqlArray['statement'];
  }
}
