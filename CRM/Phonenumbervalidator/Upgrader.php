<?php

/**
 * Collection of upgrade steps
 */
class CRM_Phonenumbervalidator_Upgrader extends CRM_Phonenumbervalidator_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Create the default equivalent domain settings if the setting does not
   * already exist.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_0001() {
    $this->ctx->log->info('Applying update 0001');

    //CRM_Phonenumbervalidator_Utils::installDefaults();
  }
}