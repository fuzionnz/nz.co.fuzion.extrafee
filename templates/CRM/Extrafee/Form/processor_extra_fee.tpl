<fieldset class='extra_fee_settings'>
<legend>Extra Fee Settings</legend>
  <table class="form-layout-compressed">
    <tbody>
      <tr class="crm-paymentProcessor-form-block-extra_fee_percentage">
        <td class="label">{$form.extra_fee_percentage.label}</td>
        <td>{$form.extra_fee_percentage.html}</td>
      </tr>
      <tr class="crm-paymentProcessor-form-block-extra_fee_processing_fee">
        <td class="label">{$form.extra_fee_processing_fee.label}</td>
        <td>{$form.extra_fee_processing_fee.html}</td>
      </tr>
      <tr class="crm-paymentProcessor-form-block-extra_fee_message">
        <td class="label">{$form.extra_fee_message.label}</td>
        <td>{$form.extra_fee_message.html}</td>
      </tr>
    </tbody>
  </table>
</fieldset>

<script type="text/javascript">
{literal}
CRM.$(function($) {
  $(".extra_fee_settings").insertAfter($( ".crm-paymentProcessor-form-block table:eq(0)"));
});
{/literal}
</script>
