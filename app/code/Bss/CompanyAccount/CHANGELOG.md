[Release Note](https://bsscommerce.com/magento-2-company-account-extension.html)
=============

v1.3.1 (Jan 03, 2024)
=============
* Fix email sending errors when change password.
* Remove all code sections that use customer session in the email flow.
* Role selection bug fix for "Approve Order Request" auto-selecting "View All Orders."
* Cart restrictions when handling approved orders (editing, accessing, adding products, and UI updates).
* Security update to prevent checking out rejected orders by URL manipulation.
* Unified logic for "Waiting" and "Approved" order statuses.
* Preservation of active quotes post-order placement.
* Independent cart functionality for sub-users and proper error handling for invalid checkout attempts.

v1.3.0 (Oct 18, 2024)
=============
* Display sub-user account information, update sub-user email + password and update information on My Account page.
* Fixed a bug where selecting the role Approve order request does not automatically select the role View all orders.
* Uncheck root in tree permissions.

v1.2.9 (Sep 12, 2024)
=============
* Fix: Bug doesn't auto select permission "View all Order" when choosing permission "Approve Order Request" for the sub-user role.

v1.0.3 GraphQL (Jul 18, 2024)
=============
* Fix: company account and sub-user have same quote

v1.2.8 (Jun 3, 2024)
=============
* Fix bug: prefix database
* Update: Compatible with M2.4.7
      
v1.2.7 (Mar 6, 2024 )
=============
* Fix bugs: can not filter the "Purchased Date" column at the backend correctly.

v1.2.6 (Jan 29, 2024 )
=============
* Sub-user token error in Magento 2.4.x
* Sub-user and company admin cannot place separate orders
    
v1.2.5 (Dec 25, 2023)
=============
* Update: Modify role labels in permission tree
* Fix bug when calling bssCompanyAccountResetSubPassword GraphQL mutation

v1.2.4 (May 26, 2023)
=============
* Update: Compatible with Magento 2.4.6, PHP 8.2

v1.2.3 (May 11, 2023)
=============
* Fix Bug: Security hole: Sub users without permission to add roles can still access the add new role page via a link
* Update: Compatible with Request for Quote
* Update: Compatible with BSS M2 OneStep Check-out (in progress)
* Hyva compatible v1.0.1 (March 17, 2023): Fix bug: Missing "Created by" column.
* Hyva compatible v1.0.0 (Feb 3, 2023): First release

v1.2.2 (Jan 19, 2023)
=============
* Fix pagination and other errors. Fix data type casting error. Optimize code. Compatible with Magento ver 2.4.5. and PHP 8.1.
  
GraphQl v1.0.2 (Jan 19, 2023 )
=============
* Update GraphQL APIs to create and delete sub-users.

v1.2.1 (Jul 26, 2022)
=============
* Convert from installSchema to db_schema. Fix bug saving role.

v1.2.0 (Jun 21, 2022)
=============
* Fix bug add-ons CustomerToSubUser: can not assign a customer to sub-user without SMTP.
* Fix Bug: no external lock with order_id.
* Fix Bug: Button "Back" is not translated because it has not yet used the translated card in the template.
* Update: Add role submit Order Waiting for sub-user.
* Update: Admin company account and sub-user have permission to approve Order Waiting.
* Update: Company Account Report.
* Compatible with BSS M2 Customer Attributes: Attribute 'Is Company Account' of BSS M2 Company Account is enabled when adding BSS Customer Attributes.
* Update: Add email notifications when updating sub-user/role and re-compose the email order in Configuration (BE).

v1.0.7 (Oct 18, 2021)
=============
* Update Restful API and GraphQL.

v1.0.6 (July 18, 2021)
=============
* Fix bug when sub-user role is set to admin. 
* Fix bug when email service is not set up sub-user is not deleted in sub-user management.
* Fix bug of when sub-user’s role is admin it’s unable to add to cart and add to quote.
* Show label when exported.
* Update ability to remove permission by xml (company_rules.xml).

v1.0.5 (Jun 10, 2021)
=============
* Remove redundant code

v1.0.4 (April 1, 2021)
=============
* Support REST API that allows creating and deleting sub-users

v1.0.3 (Mar 12, 2021)
=============
* Compatible with M2 Order Delivery Date by BSS Commerce

v1.0.2 (Nov 04, 2020)
=============
* Fix installation issue

v1.0.1 (Oct 18, 2020)
=============
* Fix bug not creating role without choosing permission.
* Fix bug duplicate admin role key when setting up module.
* Fix bug not setting up role for email account.
* Create 2 role “Create a Quote” and “View Quotes”.
* Process sub user when Submit Quote Request.
* Fix bug module mixin.
* Fix bug when Send Tracking Information from shipment.
* Fix conflict with M2 Customer Attribute when attribute type is file.
* Update logic display of sub user when saving sub user without configuring smtp.
* Fix duplicate data in table bss_sub_user_order when installing with other modules in B2B Packages.
* Save order information from quote extension in backend as admin.
* Update logic email sending to sub user when creating quotes_extensions.

v1.0.0 (May 27, 2020)
=============
* First Release
