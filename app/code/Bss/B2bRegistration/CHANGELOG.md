# [Release Note](https://bsscommerce.com/magento-2-b2b-registration-extension.html)

# v1.4.2 (Dec 16, 2024)
- Fix bug:
  - Compatible with BSS M2 Customer attribute: bug required validate field.
  - Compatible with BSS M2 Company account: showing "Register as a company account" field at the B2B registration form while disable "Is company account" field.
# v1.4.1 (Dec 6, 2024)
- Update: Validate phone number template
- Compatible with reCAPTCHA Invisible v2/v3 on Hyva theme v1.3.9.
# v1.4.0 (Sep 30, 2024)
- Update: Not sending email pending account when tủn on the config Auto approve b2b account, Not sending email when set No to the config admin email.
# v1.3.9 (Apr 3, 2024)
- Fix bug compatible with BSS M2 Customer Attribute.
# v1.3.8 (Jul 21, 2023)
- Update: Validate URL key of the B2B Account Page Url like the default Magento
- Fix bug: Validate region with not required countries
# v1.3.7 (May 26, 2023)
- Update: Changing a field name to "Register as a Company Account" (When this module is used with BSS M2 Company Account)
# v1.3.6 (Apr 25, 2023)
- Update: Compatible with Magento v2.4.6, PHP v8.2. Fix bug: Not validate address field from server.
# v1.3.5 (Dec 23, 2022)
- Fix bug: the module can't set Admin Email configuration empty. Fix bug: If Admin approves accounts in customer grid, the customer group will be automatically changed to General, not Wholesale. Fix bug: Invalid Date Field. Update: Compatible with php8.1. Fix bug: can't save customer account when they register a B2B Account.
# v1.3.4 (Nov 22, 2022)
- Compatible with BSS M2 Customer Atrributes.
# v1.3.3 (Jun 22, 2022)
- Fix bug not displaying Personal Information fields in My Account on Frontend.
# v1.3.2 (Jun 8, 2022)
- Compatible with Magento 2.4.4 and PHP 8.1; Compatible with BSS M2 Company Account: Add new config Is Company Account in Backend when both modules are enabled; Fix prefix database when setup module; Convert setup to patch data.
# v1.3.1 (Dec 30, 2021)
- Fix bug dupliacte attribute_id when upgrading module from older version to new version. Fix bug wrong B2B registration form url after validate field and reload page. Fix bug account still be created when validate false field. Adding "Pending email template" config. Adding Company name variable into confirm email template and send to admin emai template. Fix bug not assign customer account to default customer group.
# v1.3.0 (Jul 18, 2021)
- Add a page type called B2b Registration form when creating a widget; Display label of b2b approval status instead of value when exported
# v1.2.9 (Mar 24, 2021)
- Fix bug not saving other fields when changing b2b status; Work with Google reCAPTCHA
# v1.2.8 (Aug 25. 2020)
- Fix customer email missing; Fix issue with Page create B2B Account on Magento 2.4
# v1.2.7 (July 9, 2020)
- Add config Admin Email Setting Enabled
# v1.2.6 (July 1, 2020)
- Fix scopeConfig declaration when creating b2b account; Fix bug with updating data version script
# v1.2.5 (June 16, 2020)
- Fix bug not sending required email on Magento 2.3.4 when enable the configuration required email confirm register; Fix bug with M2 Customer Attribute by BSS Commerce
# v1.2.4 ( Jun 4, 2020)
- Add class customer-account-create for b2b create account page
# v1.2.3 (Apr 21, 2020)
- Magento 2 B2B Business Login now works with BSS M2 Customer Attributes without custom module
# v1.2.2 (Apr 21, 2020)
- Work with Company account on Magento EE
# v1.2.1 (Jan 31, 2020)
- Optimize b2b registration page's URL
# v1.2.0 (Jan 07, 2020)
- Update auto assign customer group after changing B2B status; Fix email sending issue on magento 2.3.3
# v1.1.9 (Nov 04, 2019)
- Fix not showing Create B2B account button on login popup
# v1.1.8 (Oct 02, 2019)
- Fix conflict with Enable Automatic Assignment to Customer Group config of Magento default
# v1.1.7 (Sep 06, 2019)
- Fix Invalid form key and wrong subscription email; Fix installation issue; Fix bug sending email when the config Admin Email Settings is No; Fix invalid return type when clicking create account button
# v1.1.6 (May 28, 2019)
- Update route and event to fix magento core bug and fix bug with message when using html
# v1.1.5 (Apr 04, 2019)
- Fix bug with Dedault Captcha; Fix bug when creating account default orB2B account
# v1.1.4 (Jan 09, 2019)
- Fix bug with duplicating Status in customer backend after updating version 1.1.3
# v1.1.3 (Nov 16, 2018)
- Fix bug when exporting customer from customer grid
# v1.1.2 (Oct 17, 2018)
- Fix bug with Email Sender and Email Template based on config Store View when Admin sends emails to Customers
# v1.1.1 (Sep 13, 2018)
- Fix logic of unsubcribe when choose subcribe newsletter on create account page
# v1.0.9 (Aug 01, 2018)
- Update logo in email header per Storeview
# v1.0.8 (Jul 25, 2018)
- Add configuration disable regular register
# v1.0.7 (May 21, 2018)
- Compatible with M2 Customer Attributes by BSS Commerce
# v1.0.5 (Apr 27, 2018)
- Fix escapeHtmlAttr issue; Fix telephone field display
# v1.0.4 (Apr 14, 2018)
- Fix bug sending email when saving customer in admin
# v1.0.3 (Mar 26, 2018)
- Fix bugs with required fields; Compatible with Magento 2.2.3
# v1.0.1 (Feb 13, 2018)
- Update sufix, middle, prefix
# v1.0.0 (Dec 20, 2017)
- First Release