SQS Copy Text 1.0.4
SuiteCRM 7.14.3 through 7.15.2. Supports PHP 7.4-compatible module syntax.
Official SuiteCRM 7.14.x runtime support is limited to PHP 8.1 and 8.2;
SuiteCRM 7.15.x supports PHP 8.1 through 8.4.

Install the archive through Admin > Module Loader, then go to
Admin > Studio > [module] > Fields > [field] and select
"Allow copying in Detail View". An HTTPS connection is required for
copying with the browser Clipboard API.

Installation stops when a required custom hook or controller already exists,
so existing customizations are not overwritten. Test on a duplicate CRM
instance before deploying to production.

Source: https://github.com/maladrill/suitecrm_copy_text
