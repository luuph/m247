# [Release Note](https://bsscommerce.com/magento-2-request-for-quote-extension.html)

## v1.3.4 (Nov 14, 2024)
- Fix bug: filtering the quote in the backend + compatible with BSS M2 Company Account extension.
- Update: compatible with BSS M2 Add multiple product to cart extension.

## v1.3.3 (Sep 9, 2024)
- Optimizing code for the bug can not create quote.

## v1.3.2 + Hyva Compat v1.0.1 (Sep 5, 2024)
- Compatible with Hyva theme ver 1.3.9.

## v1.3.1 (May 17, 2024)
- Compatible with Magento 2.4.7.

## v1.3.0 + Hyva compat v1.0.0 (Apr 4, 2024)
- Compatible with Hyva theme.

## v1.2.9 (Mar 13, 2024)
- Fix bug: Backend quote detail page show the field Created by even when there is no M2 Company Account extension.
- Update: Adding "Reference to Order ID" for the quote and the "Reference to Quote ID" for the order.

## v1.2.8 (Dec 19, 2023)
- Updates:
  - Add more customer information into the emails sent to Admin.
  - Scroll quote cart items in mini cart if there are more than 4 items.
- Fix bug:
  - Error when filtering by name in Manage Quote.

## v1.2.7 (Aug 25, 2023)
- Updates:
  - Compatible with Magento version 2.4.6 and below, and PHP 8.2.
  - Add Print button to quote detail page frontend with status as Closed, Ordered.
  - The shipping address is required with not-logged-in users.

## v1.2.6 (Jun 29, 2023)
- Updates:
  - Email template sent to customers after admin's action "Send to customer".
  - Optimize code to display button "Add to quote".
- Fix bugs:
  - Cannot add Configurable product to Quote from Homepage or catalog pages.
  - Security hole: When customer A has already logged in, they can open and view Guest B's quote detail link in the same browser.
  - When configuring Tax Calculation Based On = Billing address, the Order converted from quote has no tax.
  - Fix the distance, length of product name and SKU when printing pdf.

## v1.2.5 (May 11, 2023)
- Updates:
  - Show the result "Email sent" in the Quote detail page.
  - Compatible with BSS M2 Company Account extension.
- Fix bugs:
  - Not logged-in customers are redirected to a blank page after converting a quote to an order and re-access the link to the frontend quote detail page in their email.
  - Not displaying custom option in quote emails.
  - Non-stop sending expiry quote reminder emails.
  - Not displaying header and footer in quote emails.
  - Email sender name for a rejected quote is wrong.

## v1.2.4 (Oct 29, 2022)
- Update: Filter by sales rep in Manage Quote Requests.

## v1.2.3 (Sep 21, 2022)
- Fix bug responsive button Add to quote on Magento EE.
- Fix the message on success page for non-customer.
- Fix bug adding out of stock product in quote at the backend.

## v1.2.2 (Jul 21, 2022)
- Update to support submitting and actioning with quote for non-customer.
- Fix email templates and bug relating to PHP8.1 compatibility.

## v1.2.1 (Jun 8, 2022)
- Compatible with Magento 2.4.4 and PHP 8.1.
- Compatible with declarative schema.
- Update: only apply template when module activated.

## v1.2.0 (Feb 22, 2022)
- Fix not change Shipping & Handling (Flat Rate - Fixed) when changing qty item in frontend quote.
- Support Add quote from wishlist page.
- Fix not submit request4quote when site has store views(before Install module or disable module has create new storeview).
- Fix Jquery compact Fallback.
- Fix wrong quote email template sent to customer.
- Fix not update region when update quote(request for quote submited).
- Fix synchronizing display "created at" date in frontend quote detail page.
- Fix backend quote Expired Day to be compatible with admin interface locale.
- Fix display in quote detail, grid manage quote, when email customer change.
- Compatible print PDF quote in the frontend with Magento 2.4.3.
- Fix bug view backend quote detail after deleting the customer.
- Allow admin and customer to delete quote.
- Fix bug when creating new address while updating the quote.
- Optimize code: Fix phpcs, phpmd.
- Change tax for quote when changing the configs: change customer group, + New config: Auto change quote price when changing customer group + Fix not filter customer id and customer email in grid manage quote.
- Fix Security when update request for quote.

## v1.1.5 (Oct 18, 2021)
- Fix export request for quote. Fix compatible between quote expired date with timezone and locale.

## v1.1.4 (Jul 18, 2021)
- Fix display of customer name when sending quote email.
- Update display logic of quote comment.

## v1.1.3 (Jun 11, 2021)
- Fix date filter issue.
- Update qty of item in mini quote.
- Fix reset quote cart after customers creating accounts.

## v1.1.2 (Mar 24, 2021)
- Compatible with PayPal.
- Fix bug add related product.
- Fix bug Email expiry send continuity.
- Compatible with Klarna Payment.

## v1.1.1 (Dec 15, 2020)
- Display price when send email with quote status "Updated", "Ordered", "Complete" When product hide price.
- Hide Price product on popup quote when product hide price.
- Fix not update qty item when resubmit.
- Change logic resubmit quote (Add button "update quote”).
- Change logic remove quote old.
- Fix view quote error when deleting quote.
- Change button "Agree Quote" to "Finish Quote", "Create an Order" to "Convert Quote to Order".

## v1.0.9 (Oct 18, 2020)
- Display sub user information in Quote if used with M2 Company Account.
- Fix add to quote issue when using with M2 Configurable Product Grid Table View.
- Fix mini quote update.
- Fix sender email not matching with send of smtp.
- Fix bug with viewing/cancelling/resubmitting quote of other customers.
- Add ‘Complete’ Status.
- Update display of price, qty after admin add custom price, qty in backend.

## v1.0.8 (Aug 18, 2020)
- Work with tax setting of magento default.
- Fix bug not saving address when checkout of logged in customers.
- Fix slow load page time of children products.
- Change notification message when a product is unable to added to cart.

## v1.0.7 (Jul 1, 2020)
- Fix incorrect increment id and quote link success page.

## v1.0.6 (Jun 26, 2020)
- Fix missing quote request and inability to submit quote request.
- Fix not showing total when the status is updated.

## v1.0.5 (Jun 10, 2020)
- Work with M2 Sales Rep by BSS Commerce. Fix Fatal error when customers log in and quotes include products.
- Fix error with shipping method configuration.
- Fix wrong time updated at.
- Ignore quote_extension from quote expired delete of Magento default.

## v1.0.4 (Apr 17, 2020)
- Fix submit quote issue of virtual products.
- Update translation file.
- Fix mini quote doesn't display prices excl. tax.
- Fix bug of inability to open updated quote.
- Add shipping cost in pdf quote, email.
- Fix bug with logic if sending quote email.
- Add config disable custom shipping method.
- Fix display issue of quote button when loading page.
- Fix bug of the quote with status expired.
- Update Delete Quote function via cron.
- Fix not displaying options in PDF quote.
- Fix not display Request for Quote button when not assign product(s) into a category.
- Update label of quote comment into customer comment and admin comment.
- Fix bug login to submit a quote.
- Fix bug when first install the module without setting data for attribute quote_category.
- Fix bug of config label on the product page.
- Add total information into reject the quote and accept quote email.

## v1.0.3 (Mar 06, 2020)
- Compatible with M2 Minimum Order amount for Customer Group, M2 Reorder product list, M2 Hide Price, M2 Configurable Product Grid Table View by BSS Commerce.

## v1.0.1
- Compatible with Magento 2.3.3.
- Compatible with M2 Hide Price by BSS Commerce.
- Fix bug when updating quote.
- Update logic of configurable, bundle, grouped product when only using the configuration of parent product.
- Update logic when setting quote on category page.

## v1.0.0 (Jul 17, 2019)
- First Release of Magento 2 Request a Quote extension.
