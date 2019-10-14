<?php

use CRM_Extrafee_ExtensionUtil as E;
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
class CRM_ExtraFee_FeeTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

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
   * Test extra fee payment.
   */
  public function testExtraFeePayment() {
    $extraFeeSettings = [
      'percent' => 2.1,
      'processing_fee' => 0.2,
      'message' => 'A 2.1% credit card fee and 20c processing fee will apply.',
    ];
    Civi::settings()->set('extra_fee_settings', json_encode($extraFeeSettings));
    $form = $this->getContributionForm();
    CRM_Extrafee_Fee::modifyTotalAmountInParams('CRM_Contribute_Form_Contribution_Main', $form, $extraFeeSettings);

    //Assert amount has been modified to include the extra fee.
    $this->assertEquals(61.46, $form->_amount);
    $this->assertEquals(61.46, $form->get('amount'));
  }


  /**
   * Get a contribution form object for testing.
   *
   * @return \CRM_Contribute_Form_Contribution_Main
   */
  protected function getContributionForm() {
    $form = new CRM_Contribute_Form_Contribution_Main();
    $form->_priceSetId = civicrm_api3('PriceSet', 'getvalue', [
      'name' => 'default_contribution_amount',
      'return' => 'id',
    ]);
    $form->controller = new CRM_Core_Controller();
    $priceFields = civicrm_api3('PriceField', 'get', ['id' => $form->_priceSetId]);
    $form->_priceSet['fields'] = $priceFields['values'];
    $form->_values = [
      'title' => "Test Contribution Page",
      'financial_type_id' => 1,
      'currency' => 'NZD',
      'goal_amount' => 60,
      'total_amount' => 60,
      'amount' => 60,
      'is_monetary' => TRUE,
    ];
    $form->_amount = 60;
    return $form;
  }

}
