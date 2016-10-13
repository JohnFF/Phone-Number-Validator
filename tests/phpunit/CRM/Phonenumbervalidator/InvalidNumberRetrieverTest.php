<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class CRM_Phonenumbervalidator_InvalidNumberRetrieverTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  function testBuildReplacementMysqlString () {
    // Test hyphens and brackets.
    $selectedAllowCharactersArray = array('hyphens', 'brackets');
    $expectedOutput = "REPLACE(REPLACE(REPLACE(phone, '-', ''), '(', ''), ')', '')";
    $output = CRM_Phonenumbervalidator_InvalidNumberRetriever::buildReplacementMysqlString($selectedAllowCharactersArray);
    $this->assertEquals($expectedOutput, $output, "Found " . print_r($output, TRUE));

    // Test no ignore characts.
    $selectedIgnoreCharactersArray = array();
    $expectedOutput = "phone";
    $output = CRM_Phonenumbervalidator_InvalidNumberRetriever::buildReplacementMysqlString($selectedIgnoreCharactersArray);
    $this->assertEquals($expectedOutput, $output, "Found " . print_r($output, TRUE));
  }

  function testBuildFromStatementMyqlString () {
    $selectedRegexRuleIds = array('Britain_0', 'Britain_1', 'Britain_2', 'Britain_3');
    $selectedAllowCharacterRules = array('hyphens', 'brackets');

    $expectedOutput = "FROM (SELECT id, phone, phone_ext, phone_type_id, contact_id FROM civicrm_phone "
            . "WHERE (REPLACE(REPLACE(REPLACE(phone, '-', ''), '(', ''), ')', '') NOT REGEXP 'Britain_0') AND "
            . "(REPLACE(REPLACE(REPLACE(phone, '-', ''), '(', ''), ')', '') NOT REGEXP 'Britain_1') AND "
            . "(REPLACE(REPLACE(REPLACE(phone, '-', ''), '(', ''), ')', '') NOT REGEXP 'Britain_2') AND "
            . "(REPLACE(REPLACE(REPLACE(phone, '-', ''), '(', ''), ')', '') NOT REGEXP 'Britain_3')) "
            . "AS phone JOIN civicrm_contact AS contact ON phone.contact_id = contact.id ";

    $output = CRM_Phonenumbervalidator_InvalidNumberRetriever::buildFromStatementMyqlString($selectedRegexRuleIds, $selectedAllowCharacterRules);

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
      array(
        'contactTypeId' => '',
        'phoneTypeId' => '1',
        'expectedStatementOutput' => "WHERE 1 AND phone_type_id = '%2' ",
        'expectedParamsOutput' => array(2 => array(0 => 1, 1 => 'Int')),
      ),
      array(
        'contactTypeId' => '1',
        'phoneTypeId' => '',
        'expectedStatementOutput' => "WHERE 1 AND contact_type LIKE '%%1%' ",
        'expectedParamsOutput' => array(1 => array(0 => 'Individual', 1 => 'String', 2 => 2)),
      ),
//      array('contactTypeId' => '1000', 'phoneTypeId' => '1', 'expectedOutput' => ''),
//      array('contactTypeId' => '1', 'phoneTypeId' => '1000', 'expectedOutput' => ''),
//      array('contactTypeId' => 'string where id should be', 'phoneTypeId' => '1', 'expectedOutput' => ''),
//      array('contactTypeId' => '1', 'phoneTypeId' => 'string where id should be', 'expectedOutput' => ''),
    );

    foreach($testData as $eachTest){
      $actualOutput = CRM_Phonenumbervalidator_InvalidNumberRetriever::buildWhereStatementMysqlString($eachTest['contactTypeId'], $eachTest['phoneTypeId']);
      $this->assertEquals($eachTest['expectedStatementOutput'], $actualOutput['statement'], "Test failed, actual output was: " . print_r($actualOutput['statement'], TRUE));
      $this->assertEquals($eachTest['expectedParamsOutput'], $actualOutput['params'], "Test failed, actual output was: " . print_r($actualOutput['params'], TRUE));
    }
  }
}
