# [Release Note](https://bsscommerce.com/magento-2-out-of-stock-notification-extension.html)

## ## v1.4.4 (Jan 3, 2025):
- Fix bug: Add a strikethrough for out-of-stock child products, making the option un-clickable.
- Fix bug: Compatibility issue with EE.
## v1.4.0 (Sep 6, 2024):
- Fix bug: sending spam stock notification email.
## v1.3.9 (Aug 23, 2024):
- Update: Module v1.3.9 compatible with the Hyva theme ver 1.3.9.
## v1.3.8 (Jun 17, 2024):
- Update: Compatible with M2.4.7.
- Fix: bug duplicate id at the Manage Customer Subscriptions grid.
- Fix bug auto redirect to home page when click button Run Cron now.
## v1.3.7 (May 22, 2024):
- Update: New function Price alert.
## v1.3.6 (Dec 19, 2023):
- Add product name column on the subscription grid. 
- Add product name and website to the filters.
- Fix send mail error because sku was changed.
## v1.3.5 (Sep 6, 2023):
- Fix call getId on bool; 
- Remove events ApplyProductAlertOnCollectionAfterLoadObserver.
## v1.3.4 (Sep 6, 2023):
- Update using ajax load stop instead of using cookie.
## v1.3.3 (Jun 25, 2023):
- Update: Compatible with M2.4.6
- Update: Module works with disabled Manage stock
## v1.3.2 (Dec 12, 2022):
- Update showing button Stop notify in category page, search page.
- Add a status "Sent count limit reached" into Status column. 
- Update compatible with M2.4.4.
- Fix bug not sending emails when product back in stock; fix bug string SKU when run cron.
## v1.3.1 (Jun 27, 2022):
- Fix bug cannot run executeQueryInRow() due to missing $setup variable.
## v1.3.0 (Jun 13, 2022):
- Fix bug di compile and click button "Cron now" redirected to frontend. 
- Convert install/upgrade schema scripts to db_schema.xml files and data/patch format.
## v1.2.9 (Mar 23, 2022):
- Fix bug module does not work because vadu.html called does not exist.
## v1.2.8 (Mar 10, 2022):
- Fix bug showing customer email instead of customer name in notification email. 
- Fix bug do not run cron and send notification automatically when one of the configurable child product in-stock.
## v1.2.7 (Oct 29, 2021):
- Fix bug sending email when one of the child product of group and bundle product in-stock. 
- Fix bug after run cron now (compatible with Magento 2.4.3). 
- Fix bug wrong customer name in stock notification email.
- Fix bug logo at the email link to the first subscribe website. 
- Fix logic bundle and grouped product back in-stock.
## v1.2.6 (Jul 7, 2021):
- Fix bug entering email to the notification box with the log-in account.
- Fix bug sending error email with only deleted products once.
## v1.2.5 (May 17, 2021):
- Fix bug removing stock notification form when un-choosing the configurable child product.
- Validate email when subscribe by hitting the enter button.
- Fix bug get the stock item to get quantity in the product page.
- Fix bug redirect to 404 bugs when clicking on subscribed products.
- Fix bug js when using merge js. 
- Update stock notification on group products to be compatible with themes. 
- Update simple products, configurable child products to be compatible with MSI. 
- Update module compatible with the site without MSI.
- Update function translates for some words in the module template.
## v1.2.4 (Mar 2, 2021):
- Update compatible with Magento 2.4.2.
- Update optimize module's code. 
- Fix logic errors in each product type. 
- Fix sending email errors to the customer and the admin.
- Fix bug call to a member function getFinalProduct() on null.
- Fix getting Qty function error and updating get product collection when disabling MSI.
- Update showing out-of-stock notice at the category pages when installing the extension for the first time.
## v1.2.3 (Dec 14, 2020):
- Update compatible with Enable and Disable Multiple Source Inventory. Remove Recaptcha.
## v1.2.2 (Nov 06, 2020):
- Update optimize request call to database.
## v1.2.1 (Nov 06, 2020):
- Update compatible with BSS Commerce M2 Grouped products with custom options.
## v1.2.0 (Sep 25, 2020):
- Fix bugs and optimize code; Add config Design for notify button;
- Support Run cron now in listing;
- Fix conflict with M2 Pre order v1.1.5 by BSS Commerce;
- Fix conflict with M2 Configurable Product Grid Table view
## v1.1.9 (Apr 17, 2020):
- Fix Configurable product's stock logic;
- Fix bug of sending email
## v1.1.8 (Jan 07, 2020):
- Compatible with recaptcha function on magento 2.3.x;
- Fix not display option of configurable products with one attribute only
## v1.1.7 (Dec 05, 2019):
- Fix selector issue
## v1.1.6 (Oct 14, 2019):
- Fix bug with Send Notification Based on Available Number of Product Fix bug with wrong parameter function construct Bss\ProductStockAlert\Model\ResourceModel\Stock\Collection;
- Fix value input validation issue in backend;
- Fix Responsive issue;
- Fix the module admin grid view issue; 
- Work with multiple source inventory
## v1.1.5 (Jun 04, 2019):
- Display Notify button on category page
## v1.1.4 (Apr 25, 2019):
- Work with Multiple Source Inventory
## v1.1.3 (Apr 04, 2019):
- Fix bug with Config Limit Email Send per Customer.
- Fix bug with Notify me Button on category page
## v1.1.2 (Dec 2, 2018):
- Fix bug when choosing Notify button from category page.
- Fix bug when stockID is not same as productID.
- Fix email template on store-view.
- Fix store-view redirect issue when subscribed.
- Fix logic of email send on Manage Customer Subscriptions Grid.
- Allow better frequency setup;
- Fix redirect product link issue on email
## v1.1.1 (Oct 23, 2018):
- Compatible with Magento 2 Pre Order by BSS Commerce
## v1.0.9 (Aug 01, 2018):
- Fix frontend issue of not showing value of configurable product with attribute value which are special characters on product page.
- Work with children products of grouped product
## v1.0.8 (May 30, 2018):
- Display Notify button on related product
## v1.0.7 (Mar 26, 2018):
- Work with configurable product
## v1.0.6 (Jan 29, 2018):
- Limit email sending based on restocked number and export awaiting list
## v1.0.4 (Dec 04, 2017):
- Fix bug of sending multiple emails with multiple website setup
## v1.0.3 (Oct 20, 2017):
- Fix issue with configurable product when using dropdown attribute
## v1.0.2 (Aug 10, 2017):
- Working with production mode when enable di:compile
## v1.0.1 (Jul 13, 2017):
- Fix errors when run setup:di:compile
## v1.0.0 (Jun 12, 2017):
- First release
