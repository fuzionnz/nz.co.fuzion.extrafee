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
  if (!in_array($formName, ['CRM_Contribute_Form_Contribution_Main', 'CRM_Event_Form_Registration_Register'])) {
    return;
  }
  $extraFeeSettings = json_decode(Civi::settings()->get('extra_fee_settings'), TRUE);
  if (!CRM_Extrafee_Fee::isFormEligibleForExtraFee($form, $extraFeeSettings)) {
    return;
  }
  if (!empty($extraFeeSettings['percent']) || !empty($extraFeeSettings['processing_fee'])) {
    CRM_Extrafee_Fee::displayFeeMessage($form, $extraFeeSettings);
  }
}

/**
 * Implements hook_civicrm_postProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function extrafee_civicrm_postProcess($formName, &$form) {
  if (!in_array($formName, ['CRM_Contribute_Form_Contribution_Main', 'CRM_Event_Form_Registration_Register'])) {
    return;
  }
  $extraFeeSettings = json_decode(Civi::settings()->get('extra_fee_settings'), TRUE);
  if (!CRM_Extrafee_Fee::isFormEligibleForExtraFee($form, $extraFeeSettings)) {
    return;
  }
  $ppID = $form->getVar('_paymentProcessorID');
  if ((!empty($extraFeeSettings['percent']) || !empty($extraFeeSettings['processing_fee'])) && !empty($ppID) && empty($form->_ccid)) {
    CRM_Extrafee_Fee::modifyTotalAmountInParams($formName, $form, $extraFeeSettings);
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

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function extrafee_civicrm_preProcess($formName, &$form) {

} // */

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
