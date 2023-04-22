{literal}
<script>
CRM.$(function($) {
  var processor_extrafee = {/literal} {if $processor_extra_fee_values} {$processor_extra_fee_values} {else} 0 {/if}{literal};
  var extra_fee_settings = {/literal} {if $extra_fee_settings} {$extra_fee_settings} {else} 0 {/if}{literal};

  var isQuickConfig = {/literal}{$quick_config_display}{literal};
  var payNowPayment = {/literal} {if $payNowPayment} {$payNowPayment} {else} 0 {/if}{literal};
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

  /*
   * Thanks: https://stackoverflow.com/a/59268677/11400326
   */
  function roundNumber(num) {
    // Return a number the is rounded and two decimals. Ensure a "5" is rounded up
    // rather then down (which varies depending on the browser). For more info, see:
    // https://stackoverflow.com/questions/10015027/javascript-tofixed-not-rounding
    precision = 2;
    return (+(Math.round(+(num + 'e' + precision)) + 'e' + -precision)).toFixed(precision);
  }

  function displayTotalAmount(totalfee) {
    totalfee = roundNumber(totalfee);
    var totalEventFee  = formatExtraFee( totalfee, 2, separator, thousandMarker);
    var pricevalue = "<b>" + symbol + "</b> " + totalEventFee;
    $('#pricevalue').html(pricevalue);

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
        if (processor_extrafee !== null && typeof processor_extrafee[pp] !== 'undefined') {
          percent = processor_extrafee[pp]['percent'];
          processingFee = processor_extrafee[pp]['processing_fee'];
          message = processor_extrafee[pp]['message'];
        }
        else if (typeof extra_fee_settings !== 'undefined') {
          percent = extra_fee_settings['percent'];
          processingFee = extra_fee_settings['processing_fee'];
          message = extra_fee_settings['message'];
        }
        percent = parseFloat(percent) || 0;
        processingFee = parseFloat(processingFee) || 0;
        totalFee += (parseFloat(totalFee) * parseFloat(percent) / 100 + processingFee);
      }
    }
    $('#extra_fee_msg').hide();

    if (totalFee > totalWithoutTax) {
      var newhtml = message.replace(/{total_amount}/g, roundNumber(totalFee));
      $('#extra_fee_msg').text(newhtml);
      $('#extra_fee_msg').show();
    }

    return roundNumber(totalFee);
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
