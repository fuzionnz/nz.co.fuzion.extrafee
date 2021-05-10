{literal}
<script>
CRM.$(function($) {
  var isQuickConfig = {/literal}{$is_quick_config}{literal};
  var payNowPayment = {/literal} {if $payNowPayment} {$payNowPayment} {else} 0 {/if}{literal};
  var percent = {/literal} {if $extraFeePercentage} {$extraFeePercentage} {else} 0 {/if}{literal};
  var processingFee = {/literal} {if $extraFeeProcessingFee} {$extraFeeProcessingFee} {else} 0 {/if}{literal};
  var message = {/literal} {if $extraFeeMessage} '{$extraFeeMessage}' {else} '' {/if}{literal};
  var thousandMarker = '{/literal}{$config->monetaryThousandSeparator}{literal}';
  var separator      = '{/literal}{$config->monetaryDecimalPoint}{literal}';
  var symbol         = '{/literal}{$currencySymbol}{literal}';
  var optional_input = msg = '';

  {/literal}
    {if $extraFeeOptional}
      {literal}
        optional_input = '<div class="content" id="extra_fee_checkbox">{/literal}{$form.extra_fee_add.html} {$form.extra_fee_add.label}{literal}</div><br />';
      {/literal}
    {/if}
  {literal}
  msg = '<div class="content" id="extra_fee_msg">'+ message.replace(/{total_amount}/g, "0") +'</div><br />';
  if (payNowPayment) {
    if (isQuickConfig) {
      $('#total_amount').closest('div').append(optional_input + msg);
    }
    else {
      $('.total_amount-section').append(optional_input + msg);
    }
  }
  else if (isQuickConfig) {
    $('#priceset').append(optional_input + msg);
  }
  else {
    $('#pricesetTotal').append(optional_input + msg);
  }

  $('input#extra_fee_add').on('change', function() { displayTotalAmount(calculateTotalFee()); });

  function displayTotalAmount(totalfee) {
    totalfee = Math.round(totalfee*100)/100;
    var totalEventFee  = formatExtraFee( totalfee, 2, separator, thousandMarker);
    $('#pricevalue')[0].innerHTML = "<b>" + symbol + "</b> " + totalEventFee;

    $('#total_amount').val( totalfee );
    $('#pricevalue').data('raw-total', totalfee).trigger('change');

    ( totalfee < 0 ) ? $('#pricelabel, #pricevalue').hide() : $('#pricelabel, #pricevalue').show();
  }

  function formatExtraFee(amount, c, d, t){
    var n = amount,
      c = isNaN(c = Math.abs(c)) ? 2 : c,
      d = d == undefined ? "," : d,
      t = t == undefined ? "." : t, s = n < 0 ? "-" : "",
      i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
      j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
  };

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
    return Math.round(totalFee * 100)/100;
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
