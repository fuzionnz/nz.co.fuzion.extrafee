# nz.co.fuzion.extrafee

This extension allows for a credit card fee and optional processing fee for the CC payment to be added to the total to be paid via any payment processor that uses Credit Cards or other payment gateways. It does not get applied for Pay Later transactions.

The extension provides a configurable 
- percentage field
- an optional Processing charge field
- a message field that will display below the Contribution amount.

It calculates the extra amount to be paid for Contribution and Event pages whether they are using Price Sets or Price options. It applies to all payment pages across the site.

It provides a configurable message under the Total Amount stating what has been charged and what the new total is (which helps if you are not using a Price Set as otherwise the user does not see the new Total)

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM (*FIXME: Version number*)

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl nz.co.fuzion.extrafee@https://github.com/fuzionnz/nz.co.fuzion.extrafee/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/fuzionnz/nz.co.fuzion.extrafee.git
cv en extrafee
```

## Usage

- Install the extension.
- Navigate to "Administer >> CiviContribute >> Extrafee Settings" or /civicrm/extrafeesettings URL to set the percentage, optional 'processing fee' and message that needs to be applied on the total amount.
- Load any live contribution page or the event registration page. Currently, the additional fee will be applied to all the processors except Pay Later.

## Other solutions

https://github.com/twomice/com.joineryhq.percentagepricesetfield provides options to add a "Percentage" price set field, which can add an additional amount to a transaction, as a configurable percentage of other selected price set options.
