[Release Note](https://bsscommerce.com/magento-2-customer-attributes-extension.html)
=============

### v1.5.0 (Nov 13, 2024)
- Compatible php < 7.3
- Compatible magento247
- Fix bug when admin create order with virtual product

### v1.4.9 (Sep 19, 2024)
**Fix bugs:**
- Dicompile version M2.4.4 and M2.4.5
- Doesn't send mail after editing the customer information.

### v1.4.8 (May 10, 2024)
**Compatible with Magento 2.4.7, PHP 8.3**

### v1.4.7 (Mar 25, 2024)
**Update:**
- Send customer attributes through email for guest checkout.
- Optimize compatibility with BSS M2 B2B registration.

### v1.4.6 (Dec 21, 2023)
**Fix bugs:**
- Error when saving attributes in Order detail page BE/FE and Address Book
- Compatibility issue with PHP 7.2
- Compatibility issue with M2.4.5

### v1.4.5 (Jun 27, 2023)
**Update:**
- Optimize code and performance of validation attributes

**Fix bugs:**
- Error when disabling Magento_Csp module
- Compatibility with M2.4.6, PHP 8.2
- Converting attribute type to date format without checking the input type of that customer address attribute

### v1.4.4 (May 30, 2023)
**Updates:**
- New config "Display in backend customer detail page", only operates when this module is installed together with BSS M2 B2B Registration Form
- Compatible with Magento 2.4.6, PHP 8.2

**Fix bugs:**
- Not translating labels via email
- Compatibility issues with BSS M2 B2B Registration Form
- Validate Customer Attribute with Text field input type at checkout page

### v1.4.3 (Feb 24, 2023)
**Update:**
- Setting empty value for datetime attribute when not being configured for all page.

**Fix bugs:**
- Compatibility with M2.3.7 class Magento\Framework\View\Helper\SecureHtmlRenderer
- Validate customer address field
- Missing customer attribute when enabling the default Magento's customer attributes
- Not displaying Customer Attributes at default Magento's registration form
- Hyva compatible

### v1.4.2 (Nov 16, 2022)
**Updates:**
- Display customer address attributes when creating a new billing address at checkout page
- Compatible with M2 B2B Registration

### v1.4.1 (Aug 22, 2022)
**Update:**
- Missing table and compatible knockout JS on M2.4.4

### v1.4.0 (Jul 28, 2022)
**Updates:**
- Dependent Attribute
- Remove time stamp 00:00:00 of the Date field at the checkout page
- Customer address attribute API
- Convert setup dir to patch data, db_schema.
- Fix general bugs.

### v1.3.5 (Jun 30, 2022)
- Compatible with Magento version 2.4.4 and PHP 8.1;
- Fix bug compatible with Swagger;
- Update logic: Add variable email template;
- Fix displays store labels in order detail; 
- Fix not pass when running composer dump-autoload.

### v1.3.4 (Aug 23, 2021)
- Fix compatibility issues with BSS M2 Company Account, BSS M2 B2B Registration. 
- Fix edit address sales order when order not custom address attribute;
- Support type file When edit address sales order;
- Fix display custom address attributes in order detail when disable module or disable attributes;
- Fix bug when choosing new address shipping;
- Fix logic of validating custom address attributes customer;
- Support type file of custom address attribute when create order backend and in checkout page

### v1.3.4 (Aug 23, 2021)
- Fix compatibility issues with BSS M2 Company Account, BSS M2 B2B Registration.

### v1.3.3 (Jul 18, 2021):
- Show label of customer options in exported file

### v1.3.1 (Mar 10, 2021):
- Hide customer attribute type date in customer grid (Applied to Magento version >= 2.4.0)
- Fix coding issue
- Fix bug when clicking button "Save and Edit Continue" redirecting to other pages
- Fix bug Config Hide If Filled Before set to Yes, customer attribute type Yes/No still displayed on checkout page when choosing No value
- Fix bug not displaying reCaptcha on Edit Account page (Applied to Magento version >=2.4.1)
- Fix bug on checkout page where customer address attribute on shipping address displayed attribute code instead of attribute label
- Fix bug for Customer B2B having attribute set display in B2B account page = yes and display in account page = no uneditable
- Fix bug not changing order status
- Display type file in order backend
- Fix compatibility issue with M2 B2B Registration
- Update attribute info to customize price of shipping method
- Save new customer address when placing order
- Fix bug Attribute address type checkbox not working with place order
- Fix validation type only on Magento 2.3.6

### v1.2.9 (Nov 23, 2020):
- Fix bug when using M2 customer attribute module without creating customer address
- Update logic of notification display when customers upload file
- Compatible with Magento 2.4.0
- Allow file upload with server-configured file size
- Update logic of File type validation
- Fix bug with config "Display in Admin Checkout"
- Fix bug with M2 default Login as Customer
- Fix bug with customer attributes value on checkout page not updating in customer account and order detail

### v1.2.8 (Sep 28, 2020):
- Support REST API to get order {baseUrl}/rest/V1/orders

### v1.2.7 (Sep 22, 2020):
- Add dependency into core module Magento_Multishipping on Magento 2.4
- Fix bug when installing with Magento 2
- Add customer attribute into API to get order {baseUrl}/rest/V1/orders/:id

### v1.2.6 (Sep 12, 2020):
- Fix bug not saving/creating/editing customer
- Fix bug creating order in backend

### v1.2.5 (Sep 03, 2020):
- Support Customer Address Attribute
- Use the same input types as default Customer Attribute

### v1.2.4 (August 14, 2020):
- Support File Attachment Type on checkout page
- Fix bug on customer information page

### v1.2.3 (Jun 26, 2020):
- Fix bug where required customer attribute on checkout page of checkbox and radio type was not enforced
- Fix issue with saving customer

### v1.2.2 (May 27, 2020):
- Fix compilation issue with M2 B2B Registration by BSS Commerce

### v1.2.1 (May 20, 2020):
- Fix error on create account page in module version 1.2.0
- Fix bug of saving customer in backend in version 1.2.0

### v1.2.0 (Apr 21, 2020): 
- Work with M2 B2B Registration by BSS Commerce
- 
### v1.1.9 (Mar 18, 2020): 
- Fix bug can't save email, password, tax vat edit customer frontend;
- Fix bug with customer attribute which is file type

### v1.1.8 (Feb 11, 2020):
- Allow changing position of customer attributes on registration page

### v1.1.7 (Jan 05, 2020):
- Compatible with Magento 2.3.3; Fix bug when editting account's email/password in frontend

### v1.1.6 (Oct 10, 2019):
- Fix the validate code follow the standard of Magento 2

### v1.1.5 (Oct 03, 2019): 
- Add new function allowing show customer attribute on checkout page with 2 new configs Display On Checkout page and Hide If Filled Before; Fix bug show customer attribute in order;
- Fix error not display order & account detail in invoice, shipment, credit memo

### v1.1.4 (Sep 04, 2019): 
- Fix save attribute issue; Fix display of attribute using File Type on order view detail page; Fix error with the config Set Default Required Attribute For Existing Customer

### v1.1.3 (Feb 21, 2019): 
- Fix bug when editting customer information from frontend account; 
- Fix bug when editting customer information from backend; Support getting customer and Customer Attribute from API

### v1.1.2 (Jan 10, 2019):
- Fix Compilation error on Magento 2.2.2 and Magento 2.1.15; Update new account email sending

### v1.1.1 (Dec 26, 2018):
- Fix bug when saving frontend Account Information due to File Invalid and the error on backend Customer detail page due to Invalid Date

### v1.1.0 (Dec 18, 2018): 
- Support Input type File; Add config Allow Download Customer Attribute File;
- Fix validation issue of require customer attributes on Frontend Account Information and fix its display issue; 
- Check validation of customer attribute with input type which is Multiple Select

### v1.0.9 (Nov 09, 2018): 
- Add New account customer attribute variable and New order customer attribute on email template; 
- Allow displaying customer attribute on account email and order confirmation email; 
- Display customer attribute on frontend order detail

### v1.0.8 (Nov 01, 2018): 
- Fix conflict with amzn/amazon-pay-module; 
- Fix bug of inablility to save Customer attribute when disable config Add Secret Key to URLs

### v1.0.6 (Aug 15, 2018): 
- Fix attribute display on Customer grid; 
- Fix the issue of Customer attribute set up Required and it doesn't not disply in frontend (disable both Display in Registration Form and My Account Page configuration) which leads to checkout error

### v1.0.5 (Jul 18, 2018):
- Compatible with magento 2.2.5

### v1.0.4 (Jul 16, 2018): 
- Fix bug on checkout page when Optional Customer Attributes is not filled

### v1.0.3 (May 08, 2018): 
- Fix attribute display issue;
- Fix checkbox display issue in customer grid and edit checkbox

### v1.0.2 (Apr 16, 2018): 
- Fix error message when adding new attributes;
- Fix Mass action delete error;
- Fix issue when accessing customer detail in backend

### v1.0.1 (Nov 16, 2017): 
- Compatible with magento 2.2

### v1.0.0 (Aug 16, 2017): 
- First Release
