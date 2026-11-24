# SQS Copy Text for SuiteCRM 7

Adds an **Allow copying in Detail View** checkbox to supported fields in Studio.
When enabled, the record's Detail View shows a copy button beside the field value.
The browser copies the visible text without its label or markup and displays
a short confirmation in the CRM user's language (Polish or English). No new database
table or field type is created.

## Compatibility

| SuiteCRM | Module PHP syntax | Official PHP support for SuiteCRM |
| --- | --- | --- |
| 7.14.3–7.14.9 | 7.4–8.4 | 8.1–8.2 |
| 7.15.0–7.15.2 | 7.4–8.4 | 8.1–8.4 |

The package is installable on PHP 7.4, but **SuiteCRM 7.14.x running PHP 7.4
is outside SuiteCRM's supported configuration**. To move from such a system
to a supported target, take a backup,
test the application on PHP 8.2, upgrade SuiteCRM to 7.15.2, then move to
PHP 8.4. See the official [compatibility matrix](https://docs.suitecrm.com/admin/compatibility-matrix/).

The installer leaves the active Detail View template and theme untouched.
It registers an application-level `after_ui_frame` hook which loads the
copy button assets only on Detail Views with enabled fields. The Studio
field-editor template remains based on SuiteCRM's v7.14.3 template.

## Download installer

Download [SQS_Copy_Text-1.0.6.zip](https://github.com/maladrill/suitecrm_copy_text/releases/download/v1.0.6/SQS_Copy_Text-1.0.6.zip) from [GitHub Releases](https://github.com/maladrill/suitecrm_copy_text/releases/tag/v1.0.6).
Upload that ZIP directly to SuiteCRM's Module Loader. The repository's
**Code → Download ZIP** archive is source code, not the installer; a GitHub
Actions artifact is an outer archive containing the installer ZIP.

## Build

```sh
python3 tools/build.py
```

The result is `dist/SQS_Copy_Text-1.0.6.zip`, ready to upload in
**Admin → Module Loader**. The build requires only Python 3 and includes
the sources, installer manifest and installation hooks. It produces identical
bytes for identical source files. GitHub Actions lints the PHP source on
PHP 7.4, 8.2 and 8.4, checks Module Loader scanner restrictions, runs
browser code tests and builds the ZIP.

## Install and configure

1. Back up the SuiteCRM application files and database.
2. Install the ZIP through Module Loader on a test instance first.
3. Open **Admin → Studio → [module] → Fields → [field]** and enable
   **Allow copying in Detail View**, then save.
4. Open a record's Detail View and click the adjacent copy icon. A confirmation
   appears for three seconds without changing the field layout. The CRM
   must be served over HTTPS for the Clipboard API.

The installer refuses to overwrite unrelated custom Studio files, hooks,
and package assets. An upgrade checks the existing installation record and
SHA-256 hashes of every existing package file before overwriting it. Local
edits to installed package files require a deliberate merge; they are never
silently overwritten.

Supported field types: varchar, text, phone, email, URL, enum, multienum,
integer, float, decimal, date, datetime, currency and relate. Encrypted,
HTML, file and password fields are intentionally excluded. The displayed
text is copied as shown; for a link, the link text is copied rather than
the hidden target URL. Empty fields have no button.

## Upgrade and removal

To upgrade 1.0.3, 1.0.4, or 1.0.5, upload 1.0.6 through Module Loader
without uninstalling the current package. The existing field selections
remain in SuiteCRM's `config` table. Test the updated Detail View and
notification after a hard browser refresh. If the package files were
modified after installation, the upgrade stops and reports the conflicting
path. Review and merge that customization before retrying.

Version 1.0.2 used an old global Detail View template. To move from that
version, uninstall it first, run **Quick Repair and Rebuild**, and confirm
that `custom/include/DetailView/DetailView.tpl` was removed.

Module Loader keeps files in `custom/`, so a SuiteCRM upgrade preserves
them. Test the package before upgrading beyond 7.15.2. Uninstall removes
the package files and the settings created by its Studio checkbox.

Changing a field checkbox stores a module and field setting in SuiteCRM's
`config` table through its Administration API; no database schema change
is needed. Only users permitted to modify fields through Studio can change
this setting. Copying to the clipboard is a client-side action and creates
no server-side audit event; existing CRM read permissions still apply.

## Verification limits

The included tests validate syntax, packaging and clipboard interactions.
An end-to-end test in a copy of the actual SuiteCRM instance is needed to
confirm integration with its installed language packs, themes, extensions
and current field metadata. No production deployment has been performed.

## License

AGPL-3.0; the derived SuiteCRM Studio Smarty template retains its original
copyright notices. See [LICENSE.txt](LICENSE.txt).
