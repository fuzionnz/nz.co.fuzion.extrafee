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
    $processingFee = (float) CRM_Utils_Array::value('processing_fee', $extraFeeSettings, 0);
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

      $form->assign('processor_extra_fee_values', json_encode(self::getProcessorExtraFees()));
      $form->assign('extra_fee_settings', json_encode($extraFeeSettings));

      $form->assign('extraFeeMessage', $extraFeeSettings['message']);
      $form->assign('extraFeeOptional', $extraFeeSettings['optional']);
      $form->assign('quick_config_display', $priceSet['is_quick_config']);
      CRM_Core_Region::instance('page-body')->add([
        'template' => CRM_Extrafee_ExtensionUtil::path('templates/extra_fee.tpl')
      ]);
    }
  }

  public static function addOptionalFeeCheckbox($form, $extraFeeSettings) {
    $form->add('checkbox', 'extra_fee_add', $extraFeeSettings['label']);
  }

  /**
   *  Add % fee in submitted params.
   */
  public static function modifyTotalAmountInParams($formName, &$form, $extraFeeSettings, $ppId) {
    if (!empty($extraFeeSettings['optional']) && !CRM_Utils_Request::retrieveValue('extra_fee_add', 'String')) {
      return;
    }
    $processingFee = (float) CRM_Utils_Array::value('processing_fee', $extraFeeSettings, 0);
    $percent = CRM_Utils_Array::value('percent', $extraFeeSettings, 0);
    $ppExtraFeeSettings = json_decode(Civi::settings()->get('processor_extra_fee_settings'), TRUE);
    if (!empty($ppExtraFeeSettings[$ppId]['percent'])) {
      $percent = $ppExtraFeeSettings[$ppId]['percent'];
    }
    if (!empty($ppExtraFeeSettings[$ppId]['processing_fee'])) {
      $processingFee = $ppExtraFeeSettings[$ppId]['processing_fee'];
    }

    if ($formName == 'CRM_Contribute_Form_Contribution_Main') {
      if (!empty($form->_amount)) {
        $form->_amount += $form->_amount * $percent/100 + $processingFee;
        $form->_amount = number_format($form->_amount, 2);
        $form->set('amount', $form->_amount);
      }
      elseif ($amt = $form->get('amount')) {
        $form->_amount = $amt + $amt * $percent/100 + $processingFee;
        $form->_amount = number_format($form->_amount, 2);
        $form->set('amount', $form->_amount);
      }
    }
    elseif ($formName == 'CRM_Event_Form_Registration_Register') {
      $params = $form->getVar('_params');
      if (!empty($params[0]['amount'])) {
        $params[0]['amount'] += $params[0]['amount'] * $percent/100 + $processingFee;
        $params[0]['amount'] = number_format($params[0]['amount'], 2);
        $form->setVar('_params', $params);
        $form->set('params', $params);
      }
    }
  }

  /**
   * Is the form eligible to calculate / display the extra fee?
   *
   * @param \CRM_Core_Form $form
   * @param array $extraFeeSettings
   * @param array $ppExtraFeeSettings
   */
  public static function isFormEligibleForExtraFee($form, $extraFeeSettings, $ppExtraFeeSettings) {
    if (empty($extraFeeSettings['paymentprocessors'])) {
      // If we didn't set any payment processors we apply to all forms
      return TRUE;
    }
    $activeProcessors = $form->getVar('_paymentProcessors');
    if (empty($activeProcessors)) {
      // No payment processors on the form or missing variable - we'll leave active for now.
      return TRUE;
    }
    foreach ($activeProcessors as $paymentProcessorID => $detail) {
      if (in_array($paymentProcessorID, $extraFeeSettings['paymentprocessors'])) {
        // We have matched on one of the processors we are enabled for
        return TRUE;
      }
      // If processor has custom extra fee configured, return true.
      if (!empty($ppExtraFeeSettings[$paymentProcessorID]['percent']) || !empty($ppExtraFeeSettings[$paymentProcessorID]['processing_fee'])) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Get Extra fees overriden by payment processor.
   */
  public static function getProcessorExtraFees() {
    $ppExtraFeeSettings = json_decode(Civi::settings()->get('processor_extra_fee_settings'), TRUE);
    foreach ($ppExtraFeeSettings as $ppID => $pp) {
      if (empty($pp['percent'])) {
        unset($ppExtraFeeSettings[$ppID]);
      }
    }
    return $ppExtraFeeSettings;
  }

}
