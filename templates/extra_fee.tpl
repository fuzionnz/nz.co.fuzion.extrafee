{literal}
<script>
CRM.$(function($) {
  var isQuickConfig = {/literal}{$is_quick_config}{literal};
  var payNowPayment = {/literal} {if $payNowPayment} {$payNowPayment} {else} 0 {/if}{literal};
  var percent = {/literal} {if $extraFeePercentage} {$extraFeePercentage} {else} 0 {/if}{literal};
  var processingFee = {/literal} {if $extraFeeProcessingFee} {$extraFeeProcessingFee} {else} 0 {/if}{literal};
  var message = {/literal} {if $extraFeeMessage} '{$extraFeeMessage}' {else} '' {/if}{literal};
  {/literal}{if $extraFeeOptional}{literal}
    $msg = '<div class="content" id="extra_fee_checkbox">{/literal}{$form.extra_fee_add.html} {$form.extra_fee_add.label}{literal}</div><br />';
  {/literal}{else}{literal}
    $msg = '<div class="content" id="extra_fee_msg">'+ message.replace(/{total_amount}/g, "0") +'</div><br />';
  {/literal}{/if}{literal}
  if (payNowPayment) {
    if (isQuickConfig) {
      $('#total_amount').closest('div').append($msg);
    }
    else {
      $('.total_amount-section').append($msg);
    }
  }
  else if (isQuickConfig) {
    $('#priceset').append($msg);
  }
  else {
    $('#pricesetTotal').append($msg);
  }

  $('input#extra_fee_add').on('change', function() { displayTotalAmount(calculateTotalFee()); });

  function displayTotalAmount(totalfee) {
    totalfee = Math.round(totalfee*100)/100;
    var totalEventFee  = formatMoney( totalfee, 2, separator, thousandMarker);
    document.getElementById('pricevalue').innerHTML = "<b>"+symbol+"</b> "+totalEventFee;

    $('#total_amount').val( totalfee );
    $('#pricevalue').data('raw-total', totalfee).trigger('change');

    ( totalfee < 0 ) ? $('table#pricelabel').addClass('disabled') : $('table#pricelabel').removeClass('disabled');
  }

  var origcalculateTotalFee = window.calculateTotalFee;
  window.calculateTotalFee = function(argument) {
    var totalFee = 0;
    $("#priceset [price]").each(function () {
      totalFee = totalFee + $(this).data('line_raw_total');
    });
    totalWithoutTax = totalFee;

    var extraFeeCheckbox = $('input#extra_fee_add');
    var addExtraFee = true;
    if ((extraFeeCheckbox.length !== 0) && (!extraFeeCheckbox.prop('checked'))) {
      addExtraFee = false;
    }

    var pp = $('input[name=payment_processor_id]:checked').val();
    if (typeof pp === 'undefined') {
      pp = $('input[name=payment_processor_id]').val();
    }
    if (typeof pp !== 'undefined' && pp != 0 && totalFee) {
      if (addExtraFee) {
        totalFee += (totalFee * percent / 100 + processingFee);
      }
    }
    $('#extra_fee_msg').hide();

    if (totalFee > totalWithoutTax) {
      var newhtml = message.replace(/{total_amount}/g, Math.round(totalFee * 100)/100);
      $('#extra_fee_msg').text(newhtml);
      $('#extra_fee_msg').show();
    }
    return totalFee;
  }
  if (!payNowPayment) {
    displayTotalAmount(calculateTotalFee());
  }

  $('input[type=radio][name=payment_processor_id]').change(function() {
    displayTotalAmount(calculateTotalFee());
  });

});
</script>
{/literal}
