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
      'extra_fee_percentage' => $extraFeeSettings['percent'] ?: 1.7,
      'extra_fee_processing_fee' => $extraFeeSettings['processing_fee'] ?: 0.20,
      'extra_fee_message' => $extraFeeSettings['message'] ?:'A 1.7% credit card fee and 20c processing fee will apply.',
    ];
    return $defaults;
  }

  public function buildQuickForm() {
    // add form elements
    $this->add('text', 'extra_fee_percentage', ts('Percentage'));
    $this->add('text', 'extra_fee_processing_fee', ts('Processing Fee (Amount in Dollars)'));

    // add description
    $this->add('textarea', 'extra_fee_message', ts('Message'), 'rows=3 cols=45');
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
      'percent' => CRM_Utils_Array::value('extra_fee_percentage', $values),
      'processing_fee' => CRM_Utils_Array::value('extra_fee_processing_fee', $values),
      'message' => CRM_Utils_Array::value('extra_fee_message', $values),
    ];
    Civi::settings()->set('extra_fee_settings', json_encode($extraFeeSettings));
    CRM_Core_Session::setStatus(E::ts('You settings are saved.'), 'Success', 'success');
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

}
