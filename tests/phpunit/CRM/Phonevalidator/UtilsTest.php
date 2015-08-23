<?php

ini_set('include_path', '/var/www/html/prod/drupal');
require_once '/var/www/html/prod/drupal/sites/all/modules/civicrm/civicrm.config.php';
CRM_Core_Config::singleton();

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

    $expectedValues = CRM_Phonenumbervalidator_Utils::getPhoneNumberRegexes();

    $actualValues = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'regex_rules');

    $this->assertEquals($expectedValues, $actualValues, "Found " . print_r($actualValues, TRUE));
  }

  /**
   * Test the regex rules. To keep it a pure test, this assumes all the character substitution has already been done.
   * TODO refactor to be more generic.
   */
  function testRegexRuleMatches () {
    $invalidBritishNumbers = array(
      "0711111111", // too short
      "071111111111", // too long
      "non-numeric", // non numeric
      "07111111111 chars", // valid num with some chars on end
    );

    $regexRules = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'regex_rules');
    $britishRegexRules = $regexRules['Britain'];

    foreach ($invalidBritishNumbers as $invalidBritishNumber) {
      foreach ($britishRegexRules as $britishRegexRule) {
        $this->assertEquals(0, preg_match('/' . $britishRegexRule['regex'] . '/', $invalidBritishNumber));
      }
    }
  }

  function testGetRegexRule(){
    $regexRuleSets = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'regex_rules');

    $this->assertEquals('^0[^7][0-9]{9}$', CRM_Phonenumbervalidator_Utils::getRegexRule($regexRuleSets, 'Britain_0'));

    $this->setExpectedException(
      'Exception', 'Phone Number Validator getRegexRule: Id does not exist for country Britain - see log error.'
    );
    CRM_Phonenumbervalidator_Utils::getRegexRule($regexRuleSets, 'Britain_invalidid');

    $this->setExpectedException(
      'Exception', 'Phone Number Validator getRegexRule: Country does not exist - see log error.'
    );
    CRM_Phonenumbervalidator_Utils::getRegexRule($regexRuleSets, 'invalid_id');

    $this->setExpectedException(
      'Exception', 'Phone Number Validator getRegexRule: Incorrect number of underscores found.'
    );
    CRM_Phonenumbervalidator_Utils::getRegexRule($regexRuleSets, 'invalidid');
  }
}
