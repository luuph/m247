# [Release Note](https://bsscommerce.com/magento-2-simple-details-on-configurable-product-extension.html)

## v1.5.4 (Jan 3, 2025):
- Update: change logic meta-title: When configuring Attributes Display Config/Meta Data: Yes, if the child product does not have a meta_title, use the child name. When configured as No, it behaves as default.
## v1.5.3 (Sep 19, 2024):
- Fix compatible bug with Hyva theme v1.3.9.
## v1.5.2 (Sep 5, 2024):
- Compatible with Hyva theme v1.3.9.
## v1.5.1 (May 8, 2024):
- Update: Compatible with magento 2.4.7 php8.3
Fix: minor bug and marketplace bug
## v1.5.0 (Feb 23, 2024):
- Fix bug can not enter new product that has Visibility: Only display Product Page. 
- Remove Visibility: Only display Product Page when disabling the module.
- Fix bug: when all the configurable children products have the required option.
- Optimize performance with ajax load.
## v1.4.9 (Nov 28, 2023):
- Fix: Out of stock child products display issue
## v1.4.8 (Oct 27. 2023):
- Fix bug: Custom attribute still remains in the database even after uninstalling the module
- Configurable product can’t be added to cart from the wishlist section
## v1.4.7 (Oct 23, 2023):
- Update: Optimize code
- Update: Child product option chosen in category page sync with the first product option to be display when going to parent product details page
- Fix bug: Review of child product not displaying on Category & Search page if parent product does not have review
- Fix bug: Child product still able to redirect to parent product after disabling the feature in product edit page
- Fix bug: Product images display order issue
- Compatibility issue with Magento 2.3.3
- Child product briefly changing during product details page loading
## v1.4.6 (Aug 16, 2023):
- Update: Optimize logic of Tier Price configuration
## v1.4.5  (Aug 1, 2023):
- Update: Stock status of child product that has Manage Stock set as No from configurable product will be displayed as "In Stock" instead of "In Stock - [quantity]"
- Fix bug: Bug configurable product shown as Out of stock when Manage Stock is set as No
- Fix bug: Bug child product's option cannot be chosen if attribute value includes "/"
- Fix bug: incorrect filtering of URL rewrite model when URL key is missing the suffix
## v1.4.4 (June 12, 2023):
- Compatible with Magento 2.4.6, PHP 8.2
- Optimize query code
- Fix bug: review info displaying name of child product instead of parent product when disabling Additional Info
- Fix bug: category page still displaying name of child product when swatching options when module is already disabled for the product
- Fix bug: module not working with Flat Catalog
## v1.4.3 (Apr 24, 2023):
- Update compatible with M2 Facebook Pixel.
- Fix bug not displaying child products that are out of stock correctly in swatch options.
## v1.4.2 (Mar 28, 2023):
- Fix bug translating description label. 
- Optimize code & performance. 
- Fix bug not displaying current price of child product correctly when loading.
## v1.4.1 (Mar 08, 2022):
- Fix bug redirecting to configurable products when attribute values contain space.
## v1.4.0 (Oct 20, 2022):
- Update compatible with BSS AJAX Quick View.
- Update displaying child products' reviews on product page and category page.
- Fix bug preselecting child products and redirecting to parent product.
## v1.3.9 (Jul 13, 2022):
- Fix bug front controller reached 100 router match iterations
## v1.3.8 (Jun 29, 2022):
- Compatible with PHP7.x
## v1.3.7 (Apr 29, 2022):
- Fix compatible bug with BSS M2 Configurable product wholesale display: Show full children products.
## v1.3.6 (Apr 26, 2022):
- Update compatible with BSS M2 Improved configurable product package.
- New config "Redirect to configurable product" and visibility option "Only display product page".
- Fix bug when disable MSI. 
- Convert setup schema to db schema.
- Fix Bug: can not add to cart when enable backorder and set out-of-stock threshold = 0, add logic when out-of-stock threshold < 0.
## v1.3.5 (Mar 10, 2022):
- Fix bug not support all languages and special characters.
## v1.3.4 (Feb 25, 2022):
- Fix issue can not update/remove item in checkout cart.
## v1.3.3 (Feb 22, 2022):
- Fix bug Add to Cart action for configurable child product compatible with MSI.
## v1.3.2 (Nov 3, 2021):
- fix error not render price option date custom option on edit product frontend.
- Fix error cannot check out with simple product. 
- Fix error cannot save required option for child product in Magento 2.4.3.
- Update RestAPI and GraphQL API.
## v1.3.1 (Jul 16, 2021):
- Update: Redirect to the URL before param "?". 
- Compatible with BSS M2 Custom Option Template.
- Fix bug: product detail tab only show product SKU.
- Compatible with Magento 2.4.2.
- FIX bug showing 404 content.
- Fix bugs related to child product's option image.
- Fix bug custom option with upload file input type.
## v1.3.0 (Feb 01, 2021):
- Fix compile error for all Magento version.
- Fix 404 console error for the wrong URL.
- Fix bug when copy product URL and paste to a new tab. 
- Fix bug when adding to cart products with custom options.
- Fix Invalid argument supplied for foreach error, update showing review logic.
- Fix 500 error when adding to cart product that's not a configurable product.
- Fix bug showing tooltip when hovering to swatches.
- Fix bug changing the name in Category/ product listing page for configurable children products.
## v1.2.9 (Nov 11, 2020):
- Update compatible with Magento 2.4.0. 
- Fix appearance bug when disable custom options of children products.
## v1.2.8 (Sep 16, 2020):
- Work with M2 Product Custom Tabs by BSS Commerce.
- Fix bug when products having attributes and attribute label with special character.
- Show Import SDCP on admin panel’s menu.
- Fix bug with simple products having custom option(s)
## v1.2.7 (July 29, 2020):
- Work with Multi Source Inventory by Magento default.
- Fix die site issue when reloading custom URL.
- Work with M2 Configurable Product Wholesale Display by BSS Commerce.
- Support custom option display of children product when choosing all super attributes.
- Remove Min/max Qty Allow in Shopping Cart and Qty Increment and use default magento’s config instead.
- Support swatch review of children product when choosing super attribute.
- Allow swatch product name of children product in dynamic category/product listing (config Display Name)
## v1.2.6 (Apr 17, 2020):
- Fix Compile with MSI, Multi Source, Stock, Website.
- Fix die site, unable to load selected option when reload custom URL.
## v1.2.5 (Feb 25, 2020):
- Fix the error with custom URL
## v1.2.4 (Jan 07, 2020):
- Display image like default color swatch function on magento.
- Fix unable to export customized url without suffix configuration of category/product.
- Fix access error of children product which are not set preselect.
- Fix preselect issue with configurable products with dropdown type attribute.
## v1.2.3 (Dec 0, 2019):
- Fix bug not creating sdcp_custom_url table when installing the module the first time.
## v1.2.2 (Nov 04, 2019):
- Fix bug with Stock status config.
- Fix bug with Attribute with dropdown Input type.
- Fix bug with Attribute with swatch type
## v1.2.1 (Sep 11, 2019):
- Fix conflict with Magento 2-Page Builder on Magento 2.3.2
## v1.2.0 (Aug 30, 2019):
- Fixed errors with import Preselect simple details of configurable product
## v1.1.9 (Jul 18, 2019):
- Compatible with Magento 2.3.1
## v1.1.8 (May 17, 2019):
- Compatible with Magento 2 Configurable Product Wholesale Display by BSS Commerce
## v1.1.7 (Apr 16, 2019):
- Support Import PreSelect Option; Compatible with Magento 2.3.
- Support simple details on cart page, mini-cart and checkout page.
- Add config Child Product Image When Not Have Images Itself.
## v1.1.6 (Nov 26, 2018):
- Fix bug when upgrading the module
## v1.1.5 (Nov 26, 2018):
- Allow video setup for children products.
- Update logic if display of attributes.
## v1.1.4 (Nov 02, 2018):
- Change Configuration and logic of swatch image of configurable.
- Fix bug issue with image display.
- Fix display bug of configurable product with one attribute only.
- Fix bug of area code already set when upgrading module
## v1.1.3 (Oct 24, 2018):
- Fix config and logic of swatch image of configurable product.
- Fix display issue of configurable products with one attribute only.
- Fix issue when disabling the module
## v1.1.2 (Oct 09, 2018):
- Update ajax load's logic.
## v1.1.1 (Aug 07, 2018):
- Add function Ajax Load config per product.
- Fix bug when short/full description includes images
## v1.1.0 Jul 31, 2018):
- Fix bug when having multiple children products.
- Fix display issue when including/excluding tax.
## v1.0.9 (Mar 01, 2018):
- Get URL configurable product through db of URL rewrites
## v1.0.8 (Dec 26, 2017):
- Fix bug with cache and get tier price according to customer ID.
- New feature supporting swatch metadata and additional info.
## v1.0.6 (Dec 14, 2017):
- Compatible with magento 2.2.
## v1.0.5 (Aug 30, 2017):
- Fix price error when setting Catalog prices Including tax.
## v1.0.4 (Aug 10, 2017):
- Bug with special character in Attribute Option.
- Bug with product url link including category.
- Bug with Price of children products when setting up tax.
## v1.0.2 (Jun 29, 2017):
- Fix translation issue.
## v1.0.1 (May 18, 2017):
- Ability of select specific configurable products to work with.
## v1.0.0 (Apr 10, 2017):
- First Release.





