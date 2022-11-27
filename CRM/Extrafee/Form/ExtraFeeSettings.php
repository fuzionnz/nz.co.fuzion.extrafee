<?php

use CRM_Extrafee_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Extrafee_Form_ExtraFeeSettings extends CRM_Core_Form {

  /**
   * Set default values for the form.
   *
   * Note that in edit/view mode the default values are retrieved from the database
   */
  public function setDefaultValues() {
    $extraFeeSettings = json_decode(Civi::settings()->get('extra_fee_settings'), TRUE);
    $defaults = [
      'extra_fee_percentage' => CRM_Utils_Array::value('percent', $extraFeeSettings, 1.7),
      'extra_fee_processing_fee' => CRM_Utils_Array::value('processing_fee', $extraFeeSettings, 0.20),
      'extra_fee_message' => CRM_Utils_Array::value('message', $extraFeeSettings, 'A 1.7% credit card fee and 20c processing fee will apply.'),
      'extra_fee_paymentprocessors' => CRM_Utils_Array::value('paymentprocessors', $extraFeeSettings, []),
      'extra_fee_optional' => CRM_Utils_Array::value('optional', $extraFeeSettings, FALSE),
      'extra_fee_label' => CRM_Utils_Array::value('label', $extraFeeSettings, 'Include Extra Fee?'),
    ];
    return $defaults;
  }

  public function buildQuickForm() {
    // add form elements
    $this->add('text', 'extra_fee_percentage', ts('Percentage'));
    $this->add('text', 'extra_fee_processing_fee', ts('Processing Fee (Amount in Dollars)'));

    // add description
    $this->add('textarea', 'extra_fee_message', ts('Message'), ['rows' => 3, 'cols' => 45]);

    $this->add('select', 'extra_fee_paymentprocessors', ts('Enable for payment processors'), self::getPaymentProcessors(), FALSE, [
      'class' => 'crm-select2 huge',
      'placeholder' => ts('- select -'),
      'multiple' => TRUE,
    ]);

    $this->add('advcheckbox', 'extra_fee_optional', ts('Extra fee is optional'));
    $this->add('text', 'extra_fee_label', ts('Label'));

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();
    $extraFeeSettings = [
      'percent' => $values['extra_fee_percentage'] ?? NULL,
      'processing_fee' => $values['extra_fee_processing_fee'] ?? NULL,
      'message' => addslashes($values['extra_fee_message']),
      'paymentprocessors' => $values['extra_fee_paymentprocessors'] ?? NULL,
      'optional' => $values['extra_fee_optional'] ?? NULL,
      'label' => $values['extra_fee_label'] ?? NULL,
    ];
    Civi::settings()->set('extra_fee_settings', json_encode($extraFeeSettings));
    CRM_Core_Session::setStatus(E::ts('Your settings are saved.'), 'Success', 'success');
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

  /**
   * Get payment processors.
   *
   * This differs from the option value in that we append description for disambiguation.
   *
   * @return array
   * @throws \CiviCRM_API3_Exception
   */
  public static function getPaymentProcessors(): array {
    $results = civicrm_api3('PaymentProcessor', 'get', [
      'is_test' => ['IN' => [0, 1]],
      'return' => ['id', 'name', 'description', 'domain_id', 'is_test'],
    ]);

    $processors = [];
    foreach ($results['values'] as $processorID => $details) {
      $processors[$processorID] = ($details['is_test'] ? CRM_Core_TestEntity::appendTestText($details['name']) : $details['name']);
      if (!empty($details['description'])) {
        $processors[$processorID] .= ' : ' . $details['description'];
      }
    }
    return $processors;
  }

}
