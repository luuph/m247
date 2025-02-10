# [Release Note](https://bsscommerce.com/magento-2-wholesale-fast-order-extension.html)

## v1.4.6 (Jan 03, 2025)
- Fix bug: 
  - Compatible with elasticsearch.
  - Cannot add different options for a configurable product.
  - Unable to send the cookie, size limit of mage-messages exceeded.
- Update:
  - Display the message 'Product Disabled' instead of 'Out of Stock' when the product is both disabled and out of stock.
## v1.4.5 (Sep 19, 2024)
- Fix: issue when access controller
## v1.4.4 (Jun 21, 2024)
- Fix: bug fast order form doesn't showup.
## v1.4.3 (Nov 29, 2023)
- Update: Compatible with BSS M2 Configurable Grid Table View
- Fix Bug: Module doesn't check Parent Product's website before assigning "is Child Product"
## v1.4.2 (Jun 27, 2023)
- Fix bugs:
- Compatibility issue with PHP 8.1
- Search issue with BSS M2 Category Permission
## v1.4.1 (Apr 25, 2023)
- Update: Compatible with Magento v2.4.6 and PHP v8.2.
## v1.4.0 (Jul 13, 2022)
- Compatible with Magento M2.4.4 and PHP8.1
- Convert declarative schema
- Fix error loading upload file CSV configurable product for a long time.
## v1.3.9 (Mar 15, 2022)
- Change logic display install|not install module Configurable Product Grid View and Request for quote at Recommend Extension
- Fix display mini form on mobile.
## v1.3.8 (Feb 22, 2022)
- Fix bug showing noti "SKU do not match or do not exist on the site" while adding product to the Fast order form.
- Fix bug translating the top menu.
- Fix bug when enter "space" to the fast order form.
## v1.3.7 (Jul 18, 2021)
- Update Refresh Logic; Fix edit qty of child product of grouped product
- Work well with M2 Configurable Grid Table View by BSS Commerce
- Compatible with M2 fastest_grocery_gourmet theme
## v1.3.6 (Feb 18, 2021)
- Fix coding standard issue
## v1.3.5 (Dec 18, 2020)
- Support mini fast order form
- Add new config Refresh
- Add notification message about customer’s permission
- Fix display message of out of stock product
## v1.3.3 (Aug 05, 2020)
- Fix bug of configurable product with 3 attributes having different price
- Fix bug of not being able to edit grouped, configurable product when when the config Sales/Tax/Price Display Setting is including tax
- Fix incorrect excluding tax price in the fast order form
- Fix loading image issue when not setting images for children products of configurable product
- Fix bug with custom option which is multiple
- Fix harcoded font issue
- Fix compatibility issues with M2 CatalogPermission
- Remove HttpPostActionInterface to work well with magento 2.3 below
- Fix wrong price display when configuring grouped price
## v1.3.2 (Jun 10, 2020)
- Replace 'jquery/ui' by 'jquery-ui-modules/widget' in JS file
## v1.3.1 (Jun 9, 2020)
- Fix bug searching product with Elasticsearch engine
- Fix bug scrolling when using safari and touchpad
- Improved Search: match multiple words
- Fix bug showing wrong calculated price with product has custom options
- Compatible with Magento 2.3.5
## v1.3.0 (Feb 27, 2020)
- Optimize Speed; Compatible with Magento 2.3.3
## v1.2.8 (Oct 10, 2019)
- Fix scrolling issue on fast order form
- Fix compatibility with M2 Configurable Product Grid Table View
## v1.2.7 (Oct 02, 2019)
- Optimize UI/UX; Compatible with Mageplaza M2 Layered Navigation, Mirasvit M2 Layered Navigation
## v1.2.6 (Sep 04, 2019)
- Update new features and design
## v1.2.5 (Aug 06, 2019)
- Compatible with Magento 2.3.2
- Automatically create Quick Order CMS page
- Remove config "Enabled Shortcut Top Link" and Add config "Enable Fast Order in"
- Add row to FastOrder form
- Update Sort By: Name, SKU, Price, Quantity; Add Proceed to Checkout button
- Compatible with Elasticsearch
## v1.2.4 (Jul 12, 2019)
- Prevent /fastorder/index/csv url to generate a report
## v1.2.3 (Apr 22, 2109)
- Fix bugs on Magento 2.2.8
## v1.2.2 (Apr 09, 2019)
- Compatible with Magento 2.3
## v1.2.1 (Nov 22, 2018)
- Fix bug not display Fast Order link on Magento blank theme
- Change map's name of js file
- Fix search issue of product name having special characters
## v1.2.0 (Oct 05, 2018)
- Fix bug not showing popup with Simple Product with custom option on magento 2.1.15
## v1.1.9 (Sep 25, 2018)
- Fix search issue of still showing products with "not visible indiviaually" on fast order form
## v1.1.8 (Sep 21, 2018)
- Not allow displaying products with status Disabled on suggest list when searching on Fast Order form
## v1.1.7 (Sep 11, 2018)
- Fix bug with price display and inability to add to card products with custom option of which type is Date & Time
- Fix suggested text showing on search field when enable/disable module config "Search by SKU"
- Compatible with magento 2.2.5
## v1.1.6 (Aug 29, 2018)
- Fix bug when product name is as same as its sku
## v1.1.5 (Aug 01, 2018)
- Fix add to cart display issue
## v1.1.4 (Jul 10, 2018)
- Remove OK button; Automatically select with one and only suggested result
## v1.1.3 (Jun 13, 2018)
- Fix bug with Configurable product when children products have different price
## v1.1.2 (May 04, 2018)
- Fix bug with qty increments; Fix grouped product display
- Compatible with magento 2.2.4
## v1.1.1 (Nov 20, 2017)
- Bug in layout file that creates Fatal Error in the backend
## v1.1.0 (Nov 16, 2017)
- Compatible with magento 2.2
## v1.0.9 (Sep 12, 2017)
- Fix issue with https; Fix hardcoded image display
- Validate qty increment
- Fix issue when Configurable product has 2 swatch attributes display lowest price of selected options latter
## v1.0.8 (Jun 07, 2017)
- Fix search issue when disable search by sku configuration
## v1.0.7 (Apr 16, 2107)
- Optimize search function; Add Tooltip for configurable products
## v1.0.5 (Mar 12, 2017)
- Fix bug with configurable products with one attribute
## v1.0.4 (Mar 03, 2017)
- Fix bug with multiple websites and updating subtotal when import products via csv file
## v1.0.3
- Fix duplicate log out on mobile phone, group price issue and default, not allow adding to cart when qty is 0
## v1.0.2
- Work with tier price of products without custom options
## v1.0.1
- Fix issues on IE
## v1.0.0
- First Release