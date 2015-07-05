<?php

//require_once 'CiviTest/CiviUnitTestCase.php';

ini_set('include_path', '/var/www/html/prod/drupal');
require_once '/var/www/html/prod/drupal/sites/all/modules/civicrm/civicrm.config.php';
require_once 'CRM/Core/Config.php';
CRM_Core_Config::singleton();

/**
 * FIXME
 */
// class CRM_Phonenumbervalidator_UtilsTest extends CiviUnitTestCase {
class CRM_Phonenumbervalidator_UtilsTest extends PHPUnit_Framework_TestCase {
  function setUp() {
    // If your test manipulates any SQL tables, then you should truncate
    // them to ensure a consisting starting point for all tests
    // $this->quickCleanup(array('example_table_name'));
    parent::setUp();
  }

  function tearDown() {
    parent::tearDown();
  }

  /**
   * Delete the old settings, reinstall them, and test that they made it in ok.
   */
  function testInstallAndUninstallSettings(){
    CRM_Phonenumbervalidator_Utils::deleteDbSettings();

    $this->assertEquals(NULL, CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'regex_rules'));

    CRM_Phonenumbervalidator_Utils::installDefaults();

    $expectedValues = array(
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

    $actualValues = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'regex_rules');

    $this->assertEquals($expectedValues, $actualValues, "Found " . print_r($actualValues, TRUE));
  }
  
  function testRegexRuleMatches () {
    $invalidBritishNumbers = array(
      "0711111111", // too short
      "071111111111", // too long
      "non-numeric", // non numeric
      "07111111111 chars", // valid num with some chars on end
    );
    // TODO
  }
  
  function testGetRegexRule(){
    $regexRuleSets = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'regex_rules');  
    
    $this->assertEquals('^0[^7][0-9]{9}$', CRM_Phonenumbervalidator_Utils::getRegexRule($regexRuleSets, 'Britain_0'));
    
    $this->setExpectedException(
      'Exception', 'Phone Number Validator getRegexRule: Id does not exist for country Britain - see log error.'
    );
    CRM_Phonenumbervalidator_Utils::getRegexRule($regexRuleSets, 'Britain_invalidid');
    
    $this->setExpectedException(
      'Exception', 'Phone Number Validator getRegexRule: Id does not exist for country Britain - see log error.'
    );
    CRM_Phonenumbervalidator_Utils::getRegexRule($regexRuleSets, 'invalid_id');
    
    //TODO: 
    //CRM_Phonenumbervalidator_Utils::getRegexRule($regexRuleSets, 'invalidid');
  }
  
  function testBuildReplacementMysqlString () {
    // Test hyphens and brackets.
    $selectedAllowCharactersArray = array('hyphens', 'brackets');
    $expectedOutput = "REPLACE(REPLACE(REPLACE(phone, '-', ''), '(', ''), ')', '')";
    $output = CRM_Phonenumbervalidator_Utils::buildReplacementMysqlString($selectedAllowCharactersArray);
    $this->assertEquals($expectedOutput, $output, "Found " . print_r($output, TRUE));
    
    // Test no ignore characts.
    $selectedIgnoreCharactersArray = array();
    $expectedOutput = "phone";
    $output = CRM_Phonenumbervalidator_Utils::buildReplacementMysqlString($selectedIgnoreCharactersArray);
    $this->assertEquals($expectedOutput, $output, "Found " . print_r($output, TRUE));
  }
  
  function testBuildFromStatementMyqlString () {
    $selectedRegexRuleIds = array('Britain_0', 'Britain_1', 'Britain_2', 'Britain_3');
    $selectedAllowCharacterRules = array('hyphens', 'brackets');
    
    $expectedOutput = "FROM (SELECT id, phone, phone_ext, phone_type_id, contact_id FROM civicrm_phone WHERE "
            . "(REPLACE(REPLACE(REPLACE(phone, '-', ''), '(', ''), ')', '') NOT REGEXP '^0[^7][0-9]{9}$') AND "
            . "(REPLACE(REPLACE(REPLACE(phone, '-', ''), '(', ''), ')', '') NOT REGEXP '^07[0-9]{9}$') AND "
            . "(REPLACE(REPLACE(REPLACE(phone, '-', ''), '(', ''), ')', '') NOT REGEXP '^0044[^7][0-9]{9}$') AND "
            . "(REPLACE(REPLACE(REPLACE(phone, '-', ''), '(', ''), ')', '') NOT REGEXP '^00447[0-9]{9}$')) "
            . "AS phone JOIN civicrm_contact AS contact ON phone.contact_id = contact.id ";
    $output = CRM_Phonenumbervalidator_Utils::buildFromStatementMyqlString($selectedRegexRuleIds, $selectedAllowCharacterRules);
    
    $this->assertEquals($expectedOutput, $output, "Found " . print_r($output, TRUE));
  }
  
  function testBuildWhereStatementMyqlString () {
    $testData = array(
      array(
          'contactTypeId' => '1', 
          'phoneTypeId' => '1', 
          'expectedStatementOutput' => "WHERE 1 AND contact_type LIKE '%%1%' AND phone_type_id = '%2' ",
          'expectedParamsOutput' => array(1 => array(0 => 'Individual', 1 => 'String', 2 => 2), 2 => array(0 => 1, 1 => 'Int')),
      ),
      //array('contactTypeId' => '', 'phoneTypeId' => '1', 'expectedOutput' => "WHERE 1 AND phone_type_id = '%2' "),
      //array('contactTypeId' => '1', 'phoneTypeId' => '', 'expectedOutput' => "WHERE 1 AND contact_type LIKE '%%1%' "),
//      array('contactTypeId' => '1000', 'phoneTypeId' => '1', 'expectedOutput' => ''),
//      array('contactTypeId' => '1', 'phoneTypeId' => '1000', 'expectedOutput' => ''),
//      array('contactTypeId' => 'string where id should be', 'phoneTypeId' => '1', 'expectedOutput' => ''),
//      array('contactTypeId' => '1', 'phoneTypeId' => 'string where id should be', 'expectedOutput' => ''),
    );
    
    foreach($testData as $eachTest){
      $actualOutput = CRM_Phonenumbervalidator_Utils::buildWhereStatementMysqlString($eachTest['contactTypeId'], $eachTest['phoneTypeId']);
      $this->assertEquals($eachTest['expectedStatementOutput'], $actualOutput['statement'], "Test failed, actual output was: " . print_r($actualOutput['statement'], TRUE));
      $this->assertEquals($eachTest['expectedParamsOutput'], $actualOutput['params'], "Test failed, actual output was: " . print_r($actualOutput['params'], TRUE));
    }
  }
}