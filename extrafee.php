<?php

require_once 'extrafee.civix.php';
use CRM_Extrafee_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config
 */
function extrafee_civicrm_config(&$config) {
  _extrafee_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function extrafee_civicrm_install() {
  _extrafee_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function extrafee_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Admin_Form_PaymentProcessor') {
    $defaults = [];
    if (!empty($form->_id)) {
      $ppExtraFeeSettings = json_decode(Civi::settings()->get('processor_extra_fee_settings') ?? '', TRUE);
      if (isset($ppExtraFeeSettings[$form->_id])) {
        $defaults = [
          'extra_fee_percentage' => $ppExtraFeeSettings[$form->_id]['percent'] ?? NULL,
          'extra_fee_processing_fee' => $ppExtraFeeSettings[$form->_id]['processing_fee'] ?? NULL,
          'extra_fee_message' => $ppExtraFeeSettings[$form->_id]['message'] ?? NULL,
        ];
      }
    }
    $form->add('text', 'extra_fee_percentage', ts('Percentage'));
    $form->add('text', 'extra_fee_processing_fee', ts('Processing Fee (Amount in Dollars)'));
    $form->add('textarea', 'extra_fee_message', ts('Message'), ['rows' => 3, 'cols' => 45]);
    $form->setDefaults($defaults);
    $templatePath = realpath(dirname(__FILE__)."/templates");
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => "{$templatePath}/CRM/Extrafee/Form/processor_extra_fee.tpl"
    ));
  }
  if (!in_array($formName, ['CRM_Contribute_Form_Contribution_Main', 'CRM_Event_Form_Registration_Register'])) {
    return;
  }
  $extraFeeSettings = json_decode(Civi::settings()->get('extra_fee_settings') ?? '', TRUE);
  $ppExtraFeeSettings = json_decode(Civi::settings()->get('processor_extra_fee_settings') ?? '', TRUE);
  if (!CRM_Extrafee_Fee::isFormEligibleForExtraFee($form, $extraFeeSettings, $ppExtraFeeSettings)) {
    return;
  }
  if (!empty($extraFeeSettings['percent']) || !empty($extraFeeSettings['processing_fee'])) {
    CRM_Extrafee_Fee::displayFeeMessage($form, $extraFeeSettings);
    CRM_Extrafee_Fee::addOptionalFeeCheckbox($form, $extraFeeSettings);
  }
}

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess/
 */
function extrafee_civicrm_preProcess($formName, &$form) {
  if (!in_array($formName, [
    'CRM_Contribute_Form_Contribution_Confirm',
    'CRM_Contribute_Form_Contribution_ThankYou'
  ])) {
    return;
  }
  $extraFeeSettings = json_decode(Civi::settings()->get('extra_fee_settings') ?? '', TRUE);
  $ppExtraFeeSettings = json_decode(Civi::settings()->get('processor_extra_fee_settings') ?? '', TRUE);
  if (!CRM_Extrafee_Fee::isFormEligibleForExtraFee($form, $extraFeeSettings, $ppExtraFeeSettings)) {
    return;
  }
  $ppID = $form->getVar('_paymentProcessorID') ?? $form->_paymentProcessor['id'] ?? NULL;
  if ((!empty($extraFeeSettings['percent']) || !empty($extraFeeSettings['processing_fee'])) && !empty($ppID) && empty($form->_ccid)) {
    CRM_Extrafee_Fee::modifyTotalAmountInParams($formName, $form, $extraFeeSettings, $ppID);
  }
}
/**
 * Implements hook_civicrm_postProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postProcess/
 */
function extrafee_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Admin_Form_PaymentProcessor') {
    $ppExtraFeeSettings = json_decode(Civi::settings()->get('processor_extra_fee_settings') ?? '', TRUE);
    $ppExtraFeeSettings[$form->_id] = [
      'percent' => $form->_submitValues['extra_fee_percentage'] ?? NULL,
      'processing_fee' => $form->_submitValues['extra_fee_processing_fee'] ?? NULL,
      'message' => addslashes($form->_submitValues['extra_fee_message']),
    ];
    Civi::settings()->set('processor_extra_fee_settings', json_encode($ppExtraFeeSettings));
  }
  if (!in_array($formName, [
    'CRM_Contribute_Form_Contribution_Main',
    'CRM_Event_Form_Registration_Register'
  ])) {
    return;
  }

  $extraFeeSettings = json_decode(Civi::settings()->get('extra_fee_settings') ?? '', TRUE);
  $ppExtraFeeSettings = json_decode(Civi::settings()->get('processor_extra_fee_settings') ?? '', TRUE);
  if (!CRM_Extrafee_Fee::isFormEligibleForExtraFee($form, $extraFeeSettings, $ppExtraFeeSettings)) {
    return;
  }
  $ppID = $form->getVar('_paymentProcessorID') ?? $form->_paymentProcessor['id'] ?? NULL;
  if ((!empty($extraFeeSettings['percent']) || !empty($extraFeeSettings['processing_fee'])) && !empty($ppID) && empty($form->_ccid)) {
    CRM_Extrafee_Fee::modifyTotalAmountInParams($formName, $form, $extraFeeSettings, $ppID);
  }
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function extrafee_civicrm_enable() {
  _extrafee_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function extrafee_civicrm_navigationMenu(&$menu) {
  _extrafee_civix_insert_navigation_menu($menu, 'Administer/CiviContribute', array(
    'label' => E::ts('Extrafee Settings'),
    'name' => 'extra_fee_settings',
    'url' => 'civicrm/extrafeesettings',
    'permission' => 'access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
}
