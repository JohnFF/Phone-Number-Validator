<?php
/**
 * Collection of upgrade steps.
 */
class CRM_Phonenumbervalidator_Upgrader extends CRM_Phonenumbervalidator_Upgrader_Base {
  
  /**
   * Upgrade 2.02 Reinstalls the phone numbers, after upgrading british phones 
   * to support 10 digit numbers beginning 01.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_2020() {
    $this->ctx->log->info('Applying update 2020.');
    CRM_Phonenumbervalidator_Utils::installPhoneNumberRegexes();
    return TRUE;
  }
}
