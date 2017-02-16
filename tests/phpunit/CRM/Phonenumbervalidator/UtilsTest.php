<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * This tests the utils functions, including the regexes themselves.
 *
 * @group headless
 */
class CRM_Phonenumbervalidator_UtilsTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

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

  /**
   * Delete the old settings, reinstall them, and test that they made it in ok.
   */
  function testInstallAndUninstallSettings(){
    CRM_Phonenumbervalidator_Utils::deleteDbSettings();

    Civi::settings()->loadValues();

    $this->assertEquals(NULL, CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'com.civifirst.phonenumbervalidator.regex_rules'));

    CRM_Phonenumbervalidator_Utils::installDefaults();

    Civi::settings()->loadValues();

    $expectedValues = CRM_Phonenumbervalidator_Utils::getPhoneNumberRegexes();

    $actualValues = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'com.civifirst.phonenumbervalidator.regex_rules');

    $this->assertEquals($expectedValues, $actualValues, "Found " . print_r($actualValues, TRUE));
  }

  /**
   * Test the British regex rules. To keep it a pure test, this assumes all the character substitution has already been done.
   */
  function testBritishRegexRuleMatches () {
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
      '0044120411111', // 10-digit international landline beginning with 00441
    );

    foreach ($validBritishNumbers as $validBritishNumber) {
      $matchCount = 0;
      foreach ($britishRegexRules as $britishRegexRule) {
        $matchCount += preg_match('/' . $britishRegexRule['regex'] . '/', $validBritishNumber);
      }
      $this->assertEquals(1, $matchCount, "Failed on $validBritishNumber.");
    }
  }

  /**
   * Test the South Africa rules.
   */
  function testSouthAfricanRegexRuleMatches () {
    $regexRules = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'com.civifirst.phonenumbervalidator.regex_rules');
    $southAfricanRegexRules = $regexRules['South Africa'];

    $validSouthAfricanNumbers = array(
      '0123456789', // National.
      '0027123456789', // International.
    );

    foreach ($validSouthAfricanNumbers as $validSouthAfricanNumber) {
      $matchCount = 0;
      foreach ($southAfricanRegexRules as $southAfricanRegexRule) {
        $matchCount += preg_match('/' . $southAfricanRegexRule['regex'] . '/', $validSouthAfricanNumber);
      }
      $this->assertEquals(1, $matchCount, "Failed on $validSouthAfricanNumber.");
    }
  }

  /**
   * Test the retrieval of the regices.
   */
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
