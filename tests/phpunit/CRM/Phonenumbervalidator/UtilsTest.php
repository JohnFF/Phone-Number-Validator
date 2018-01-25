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
  public function testInstallAndUninstallSettings() {
    CRM_Phonenumbervalidator_Utils::deleteDbSettings();

    Civi::settings()->loadValues();

    $this->assertEquals(NULL, CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'com.civifirst.phonenumbervalidator.regex_rules'));

    CRM_Phonenumbervalidator_Utils::installDefaults();

    Civi::settings()->loadValues();

    $expectedValues = CRM_Phonenumbervalidator_Utils::getPhoneNumberRegexes();

    $actualValues = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'com.civifirst.phonenumbervalidator.regex_rules');

    $this->assertEquals($expectedValues, $actualValues, "Found " . print_r($actualValues, TRUE));
  }

  public function regexRulesMatchInternalTest($nation, $invalidPhoneNumbers, $validPhoneNumbers) {
    $allRegexRules = CRM_Core_BAO_Setting::getItem('com.civifirst.phonenumbervalidator', 'com.civifirst.phonenumbervalidator.regex_rules');
    $nationalRegexRules = $allRegexRules[$nation];

    foreach ($invalidPhoneNumbers as $invalidPhoneNumber) {
      foreach ($nationalRegexRules as $eachNationalRegexRule) {
        $this->assertEquals(0, preg_match('/' . $eachNationalRegexRule['regex'] . '/', $invalidPhoneNumber));
      }
    }

    foreach ($validPhoneNumbers as $validPhoneNumbers) {
      $matchCount = 0;
      foreach ($nationalRegexRules as $eachNationalRegexRule) {
        $matchCount += preg_match('/' . $eachNationalRegexRule['regex'] . '/', $validPhoneNumbers);
      }
      $this->assertEquals(1, $matchCount, "Failed on $validPhoneNumbers.");
    }
  }

  /**
   * Test the British regex rules. To keep it a pure test, this assumes all the character substitution has already been done.
   */
  public function testBritishRegexRuleMatches() {

    $invalidBritishNumbers = array(
      '0711111111', // too short
      '071111111111', // too long
      'non-numeric', // non numeric
      '07111111111 chars', // valid num with some chars on end
      '0220411111', // 10-digit landline beginning with 02
    );

    $validBritishNumbers = array(
      '07111111111', // 11-digit mobile
      '02081111111', // 11-digit landline
      '0120411111', // 10-digit landline beginning with 01
      '0044120411111', // 10-digit international landline beginning with 00441
    );

    $this->regexRulesMatchInternalTest('Britain', $invalidBritishNumbers, $validBritishNumbers);
  }

  /**
   * Test the South Africa rules.
   */
  public function testSouthAfricanRegexRuleMatches() {
    $validSouthAfricanNumbers = array(
      '0123456789', // National.
      '0027123456789', // International.
    );

    $invalidNumbers = array(NULL, 0, 'invalid number');

    $this->regexRulesMatchInternalTest('South Africa', $invalidNumbers, $validSouthAfricanNumbers);
  }

  public function testMalaysianRegexRuleMatches() {

    $validMalaysianNumbers = array(
      '0321702200', // National.
      '0060321702200', // International.
    );

    $invalidNumbers = array(NULL, 0, 'invalid number');

    $this->regexRulesMatchInternalTest('Malaysia', $invalidNumbers, $validMalaysianNumbers);
  }

  public function testVanuatuanRegexRuleMatches() {

    $validVanuatuanNumbers = array(
      '55555', // National.
      '7777777', // National.
      '0067823111', // International.
      '006785594400', // International.
    );

    $invalidNumbers = array(NULL, 0, 'invalid number');
    
    $this->regexRulesMatchInternalTest('Vanuatu', $invalidNumbers, $validVanuatuanNumbers);
  }

  /**
   * Test the retrieval of the regices.
   */
  public function testGetRegexRule() {
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
