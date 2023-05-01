<?php

require_once 'extrafee.civix.php';
use CRM_Extrafee_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function extrafee_civicrm_config(&$config) {
  _extrafee_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function extrafee_civicrm_xmlMenu(&$files) {
  _extrafee_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function extrafee_civicrm_install() {
  _extrafee_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function extrafee_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Admin_Form_PaymentProcessor') {
    $defaults = [];
    if (!empty($form->_id)) {
      $ppExtraFeeSettings = json_decode(Civi::settings()->get('processor_extra_fee_settings'), TRUE);
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
  if (!in_array($formName, [
    'CRM_Contribute_Form_Contribution_Main',
    'CRM_Event_Form_Registration_Register',
    'CRM_Event_Form_Registration_AdditionalParticipant'
    ])) {
    return;
  }
  $extraFeeSettings = json_decode(Civi::settings()->get('extra_fee_settings'), TRUE);
  $ppExtraFeeSettings = json_decode(Civi::settings()->get('processor_extra_fee_settings'), TRUE);
  if (!CRM_Extrafee_Fee::isFormEligibleForExtraFee($form, $extraFeeSettings, $ppExtraFeeSettings)) {
    return;
  }
  if (!empty($extraFeeSettings['percent']) || !empty($extraFeeSettings['processing_fee'])) {
    $params = $form->getVar('_params');
    if ($formName == 'CRM_Event_Form_Registration_AdditionalParticipant' && !empty($params[0]['payment_processor_id'])) {
      $form->assign('selected_payment_processor', $params[0]['payment_processor_id']);
      if (!empty($params[0]['extra_fee_add'])) {
        $defaults['extra_fee_add'] = 1;
        $form->setDefaults($defaults);
      }
    }
    CRM_Extrafee_Fee::displayFeeMessage($form, $extraFeeSettings);
    CRM_Extrafee_Fee::addOptionalFeeCheckbox($form, $extraFeeSettings);
  }
}

/**
 * Implements hook_civicrm_postProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function extrafee_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Admin_Form_PaymentProcessor') {
    $ppExtraFeeSettings = json_decode(Civi::settings()->get('processor_extra_fee_settings'), TRUE);
    $ppExtraFeeSettings[$form->_id] = [
      'percent' => $form->_submitValues['extra_fee_percentage'] ?? NULL,
      'processing_fee' => $form->_submitValues['extra_fee_processing_fee'] ?? NULL,
      'message' => addslashes($form->_submitValues['extra_fee_message']),
    ];
    Civi::settings()->set('processor_extra_fee_settings', json_encode($ppExtraFeeSettings));
  }

  if (!in_array($formName, [
    'CRM_Contribute_Form_Contribution_Main',
    'CRM_Event_Form_Registration_Register',
    'CRM_Event_Form_Registration_AdditionalParticipant'
    ])) {
    return;
  }
  $extraFeeSettings = json_decode(Civi::settings()->get('extra_fee_settings'), TRUE);
  $ppExtraFeeSettings = json_decode(Civi::settings()->get('processor_extra_fee_settings'), TRUE);
  if (!CRM_Extrafee_Fee::isFormEligibleForExtraFee($form, $extraFeeSettings, $ppExtraFeeSettings)) {
    return;
  }
  $ppID = $form->getVar('_paymentProcessorID');
  $params = $form->getVar('_params');
  if ($formName == 'CRM_Event_Form_Registration_AdditionalParticipant' && empty($ppID) && !empty($params[0]['payment_processor_id'])) {
    $ppID = $params[0]['payment_processor_id'];
  }
  if ((!empty($extraFeeSettings['percent']) || !empty($extraFeeSettings['processing_fee'])) && !empty($ppID) && empty($form->_ccid)) {
    CRM_Extrafee_Fee::modifyTotalAmountInParams($formName, $form, $extraFeeSettings, $ppID);
  }
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function extrafee_civicrm_postInstall() {
  _extrafee_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function extrafee_civicrm_uninstall() {
  _extrafee_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function extrafee_civicrm_enable() {
  _extrafee_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function extrafee_civicrm_disable() {
  _extrafee_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function extrafee_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _extrafee_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function extrafee_civicrm_managed(&$entities) {
  _extrafee_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function extrafee_civicrm_caseTypes(&$caseTypes) {
  _extrafee_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function extrafee_civicrm_angularModules(&$angularModules) {
  _extrafee_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function extrafee_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _extrafee_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function extrafee_civicrm_entityTypes(&$entityTypes) {
  _extrafee_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
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
