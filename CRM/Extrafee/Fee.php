<?php

use CRM_Extrafee_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Extrafee_Fee {

  /**
   * Display extra fee msg on payment page.
   */
  public static function displayFeeMessage($form, $extraFeeSettings) {
    $processingFee = CRM_Utils_Array::value('processing_fee', $extraFeeSettings, 0);
    $percent = CRM_Utils_Array::value('percent', $extraFeeSettings, 0);
    $form->set('amount', 0);
    $form->assign('payNowPayment', FALSE);
    if (!empty($form->_ccid) && !empty($form->_pendingAmount)) {
      $form->_pendingAmount += $form->_pendingAmount * $percent/100 + $processingFee;
      $form->assign('pendingAmount', $form->_pendingAmount);
      $form->assign('payNowPayment', TRUE);
    }
    if (!empty($form->_priceSetId)) {
      $priceSet = civicrm_api3('PriceSet', 'getsingle', [
        'return' => ["is_quick_config"],
        'id' => $form->_priceSetId,
      ]);

      $form->assign('extraFeePercentage', $percent);
      $form->assign('extraFeeProcessingFee', $processingFee);
      $form->assign('extraFeeMessage', $extraFeeSettings['message']);
      $form->assign('is_quick_config', $priceSet['is_quick_config']);
      $templatePath = realpath(dirname(__FILE__) . "/../../templates");
      CRM_Core_Region::instance('page-body')->add(array(
        'template' => "{$templatePath}/extra_fee.tpl"
      ));
    }
  }
  /**
   *  Add % fee in submitted params.
   */
  public static function modifyTotalAmountInParams($formName, &$form, $extraFeeSettings) {
    $processingFee = CRM_Utils_Array::value('processing_fee', $extraFeeSettings, 0);
    $percent = CRM_Utils_Array::value('percent', $extraFeeSettings, 0);
    if ($formName == 'CRM_Contribute_Form_Contribution_Main') {
      if (!empty($form->_amount)) {
        $form->_amount += $form->_amount * $percent/100 + $processingFee;
        $form->set('amount', $form->_amount);
      }
      elseif ($amt = $form->get('amount')) {
        $form->_amount = $amt + $amt * $percent/100 + $processingFee;
        $form->set('amount', $form->_amount);
      }
    }
    elseif ($formName == 'CRM_Event_Form_Registration_Register') {
      $params = $form->getVar('_params');
      if (!empty($params[0]['amount'])) {
        $params[0]['amount'] += $params[0]['amount'] * $percent/100 + $processingFee;
        $form->setVar('_params', $params);
        $form->set('params', $params);
      }
    }
  }

}
