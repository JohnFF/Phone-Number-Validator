<?php

//ini_set('include_path', '/var/www/html/prod/drupal');
//require_once '/var/www/html/prod/drupal/sites/all/modules/civicrm/civicrm.config.php';
//CRM_Core_Config::singleton();

// class CRM_Phonenumbervalidator_UtilsTest extends CiviUnitTestCase {
class CRM_Phonenumbervalidator_UtilsTesta extends PHPUnit_Framework_TestCase {
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

    $this->assertEquals(NULL, CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'com.civifirst.phonenumbervalidator.regex_rules'));

    CRM_Phonenumbervalidator_Utils::installDefaults();

    $expectedValues = CRM_Phonenumbervalidator_Utils::getPhoneNumberRegexes();

    $actualValues = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'com.civifirst.phonenumbervalidator.regex_rules');

    $this->assertEquals($expectedValues, $actualValues, "Found " . print_r($actualValues, TRUE));
  }

  /**
   * Test the regex rules. To keep it a pure test, this assumes all the character substitution has already been done.
   * TODO refactor to be more generic.
   */
  function testRegexRuleMatches () {
    $regexRules = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'com.civifirst.phonenumbervalidator.regex_rules');
    $britishRegexRules = $regexRules['Britain'];

    $invalidBritishNumbers = array(
      '0711111111', // too short
      '071111111111', // too long
      'non-numeric', // non numeric
      '07111111111 chars', // valid num with some chars on end
      '0220411111', // 10-digit landline beginning with 02
    );

    foreach ($invalidBritishNumbers as $invalidBritishNumber) {
      foreach ($britishRegexRules as $britishRegexRule) {
        $this->assertEquals(0, preg_match('/' . $britishRegexRule['regex'] . '/', $invalidBritishNumber));
      }
    }

    $validBritishNumbers = array(
      '07111111111', // 11-digit mobile
      '02081111111', // 11-digit landline
      '0120411111', // 10-digit landline beginning with 01
    );

    foreach ($validBritishNumbers as $validBritishNumber) {
      $matchCount = 0;
      foreach ($britishRegexRules as $britishRegexRule) {
        $matchCount += preg_match('/' . $britishRegexRule['regex'] . '/', $validBritishNumber);
      }
      $this->assertEquals(1, $matchCount, "Failed on $validBritishNumber.");
    }
  }

  function testGetRegexRule(){
    $regexRuleSets = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'com.civifirst.phonenumbervalidator.regex_rules');

    $this->assertEquals('^0(([^7][0-9]{9})|(1[0-9]{8}))$', CRM_Phonenumbervalidator_Utils::getRegexRule($regexRuleSets, 'Britain_0'));

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
