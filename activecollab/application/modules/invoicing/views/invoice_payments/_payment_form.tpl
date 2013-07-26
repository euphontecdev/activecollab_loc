<div class="col">
{wrap field=paid_on}
  {label for=invoicePaymentPaidOn required=yes}Paid On{/label}
  {select_date  name=invoice_payment[paid_on] value=$invoice_payment_data.paid_on id=invoicePaymentPaidOn class='required'}
{/wrap}
</div>

<div class="col">
{wrap field=amount}
  {label for=invoicePaymentAmount required=yes}Amount{/label}
{if $active_invoice_payment->isNew()}
  {text_field name=invoice_payment[amount] value=$invoice_payment_data.amount id=invoicePaymentAmount class='required short'} {$active_invoice->getCurrencyCode()|clean}
  <p class="details">{lang}Max amount that can be paid is{/lang}: {$active_invoice->getMaxPayment()|number:$smarty.const.INVOICE_PRECISION} {$active_invoice->getCurrencyCode()|clean}</p>
{else}
  {$active_invoice_payment->getAmount()|number} {$active_invoice->getCurrencyCode()}
{/if}
{/wrap}
</div>

<div class="clear"></div>

{wrap field=comment}
  {label for=invoicePaymentComment}Comment{/label}
  {text_field name=invoice_payment[comment] value=$invoice_payment_data.comment id=invoicePaymentComment class=long}
{/wrap}

<label for="notifyCompany" class="checkbox_complete" id="notifyCompanyBlock" style="display:none">
  {checkbox_field name='notify_company' id=notifyCompany value="true" checked=yes} {lang company_name=$active_invoice->getCompanyName()|clean}Notify client that this invoice is fully paid?{/lang}
</label>