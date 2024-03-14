<?php

use CRM_Extrafee_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Extrafee_Upgrader extends CRM_Extension_Upgrader_Base {

  public function createPriceField() {
    if (empty(CRM_Extrafee_Fee::getExtraFeePriceFieldId())) {
      \Civi\Api4\PriceField::create(FALSE)
        ->addValue('price_set_id.name', 'default_contribution_amount')
        ->addValue('label', 'Extra Fee')
        ->addValue('html_type', 'Text')
        ->addValue('name', 'extrafee')
        ->execute();
    }
  }

  /**
   * Example: Work with entities usually not available during the install step.
   *
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   */
  public function postInstall(): void {
    $this->createPriceField();
  }

  /**
   * Example: Run a simple query when a module is enabled.
   */
  public function enable(): void {
    $pfId = CRM_Extrafee_Fee::getExtraFeePriceFieldId();
    if (empty($pfId)) {
      $this->createPriceField();
    }
    else {
      \Civi\Api4\PriceField::update(FALSE)
        ->addValue('is_active', TRUE)
        ->addWhere('id', '=', $pfId)
        ->execute();
    }
  }

  /**
   * Example: Run a simple query when a module is disabled.
   */
  public function disable(): void {
    $pfId = CRM_Extrafee_Fee::getExtraFeePriceFieldId();
    if (empty($pfId)) {
      $this->createPriceField();
    }
    else {
      \Civi\Api4\PriceField::update(FALSE)
        ->addValue('is_active', FALSE)
        ->addWhere('id', '=', $pfId)
        ->execute();
    }
  }

  /**
   * Example: Run a couple simple queries.
   *
   * @return TRUE on success
   * @throws CRM_Core_Exception
   */
  public function upgrade_4200(): bool {
    $this->ctx->log->info('Applying update 4200');
    $this->createPriceField();
    return TRUE;
  }

}
