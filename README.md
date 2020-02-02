# nz.co.fuzion.extrafee

This extension allows for a credit card fee and optional processing fee for the CC payment to be added to the total to be paid via any payment processor that uses Credit Cards or other payment gateways. It does not get applied for Pay Later transactions.

![Screenshot](/images/example.png)

The extension provides a configurable
- percentage field
- an optional Processing charge field
- a message field that will display below the Contribution amount.

It calculates the extra amount to be paid for Contribution and Event pages whether they are using Price Sets or Price options. It applies to all payment pages across the site.

It provides a configurable message under the Total Amount stating what has been charged and what the new total is (which helps if you are not using a Price Set as otherwise the user does not see the new Total)

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.1+
* CiviCRM 5.19+

## Installation

See: https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/#installing-a-new-extension

## Usage

- Install the extension.
- Navigate to "Administer >> CiviContribute >> Extrafee Settings" or /civicrm/extrafeesettings URL to set:
  - Percentage.
  - Optional 'processing fee'.
  - Message that needs to be applied on the total amount.
  - Optionally limit extra fee to pages which have specific payment processors.
  - Optionally allow the user to select if they want to pay the extra fee.
- Load any live contribution page or the event registration page.

Currently, the additional fee will be applied to all the processors on the page except Pay Later.
If you specify the list of payment processors the extra fee will only be active on pages which have one or more of those processors enabled.

## Other solutions

https://github.com/twomice/com.joineryhq.percentagepricesetfield provides options to add a "Percentage" price set field, which can add an additional amount to a transaction, as a configurable percentage of other selected price set options.
