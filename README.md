# nz.co.fuzion.extrafee

This extension applies additional percentage amount on the total payment. It is required when you need to cover extra credit card fee or processing fee for the CC payment.

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
- Navigate to "Administer >> CiviContribute >> Extrafee Settings" or /civicrm/extrafeesettings URL to set the percentage and message that needs to be applied on the total amount.
- Load any live contribution page or the event registration page. Currently, the additional fee will be applied to all the processor except pay later. So only enable those PP on the page which should have this fee applied.
