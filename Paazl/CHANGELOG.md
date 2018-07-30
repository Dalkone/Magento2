# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) 
and this project adheres to [Semantic Versioning](http://semver.org/).

## [1.3.7]
### Improvements / Bug fixes
- Previous release introduced an issue in which the order could not be created anymore.

## [1.3.6]
### Improvements / Bug fixes
- Solve issue when an invoice was created based on an order that had an including a comma then the street would be empty.

## [1.3.5]
### Improvements / Bug fixes
- Solve issue when a customer is logged in the address would not be saved when it including a comma. This address is now also correctly send to Paazl in the commit call.

## [1.3.4]
### Improvements / Bug fixes
- Solve issue when RC versions were used. Due to several upgrade paths it could happen that extra address fields would not show in My Account page because of an incorrect setting to these attributes. This version will correct these attribute settings.
- Solve issue where the state for an US address would not be committed correctly.
- Solve issue when choosing for a different address in step 2 of the checkout would cause the checkout to stop.

## [1.3.3]
### Changed
- Bugfix: extra address fields would not show in My Account page during a fresh install.
- Bugfix: create order and reorder from backend now fill in address fields correctly.

## [1.3.2]
### Changed
- Bugfix: Swagger error: invalid parameter type of Paazl Shipping Model Data Delivery
- Fixed compatiblity issue with MultiSafepay. (With help from redelschaap)

## [1.3.1]
### Changed
- Bugfix: The final order reference will use the order increment ID instead of quote ID.

## [1.3.0] - 2017-08-02
With the 1.3.0 release we're introducing a better way for Paazl to store customer address information in Magento. By default, Magento handles the street information in a single field and uses multiple lines to store the information. This is considered suboptimal as it causes a lot of problems with compatibility. New separate Customer attributes are introduced: street_name, house_number, house_number_addition.

*EE Only: If you're already using custom Customer attributes (e.g. `house number`), make sure it follows Paazl's exact naming convention (they are varchar fields).*

- All UI components that have address fields are replaced with the new customer attributes (frontend / backend).
- All existing addresses are automatically converted to the new format when they are loaded.
- To maintain 100% compatibility, before saving the customer address a new flattened value is stored in the original street attribute.

### Improvements / Bug fixes
A lot of bug fixes and small improvements are included in this release:
- The product matrix is now configurable from A-ZZ instead of A-Z.
- Added option to enable the postcode validation (disabled by default due to UI issues with addresses outside the Netherlands, better UI planned for a future release).
- Checkout field sort order set to: Street Number, House Number, House Number Addition, Postcode, City, State, Country
- Solve issue in the cart where the tax would not be calculated properly because Paazl expects a full address instead of only a postcode. (Reported by ISM e-Company)
- Solve issue in the cart where filling in your state would throw a JS error.
- Solve issue where saving a customer address would throw an exception. CE Only
- Solve issue where a reorder would throw an exception. (Reported by Guapa)
- Solve issue where having a different configuration per website would cause issues (Reported by Guapa).
- House number now has numeric validation on the frontend
- Cron jobs will now keep running when a single order fails to commit to Paazl.
- Cron jobs will now log encountered error codes in the order.
- My Account section translations
- Solve issue where the address couldn't be rendered when using a newly entered address in the checkout when logged-in.
- Solve issue where Street+Housenumber+Addition would be send to Paazl on a single line.
- Solve issue where delivery dates weren't in the proper timezone
- Solve issue where the shipping options were empty because timezone differences.
- Solve issue where the pickup option would always be selected after the delivery type was changed
multiple times in the popup.
- Solve issue where opening the Paazl Perfect popup would reset the country.

### Known Issues / Limitations
- 'Admin Panel Order Create' and 'Frontend multishipping' does not support Paazl Perfect, only basic functionality is supported.
- Core Bug: Customer Address field are not update for order edit address.  (Ticket #03212673)
    - Backend, open order, choose ‘edit’ at billing address. change the house number, field is not updated for the order.
    - If you order as logged-in customer and choose for a different billing address on the payment step then it does not save the house number.

## [1.3.0-rc2] - To be released

### Improvements / Bug fixes
- House number now has numeric validation on the frontend
- Cron jobs will now keep running when a single order fails to commit to Paazl.
- Cron jobs will now log encountered error codes in the order.
- My Account section translations
- Solve issue where the address couldn't be rendered when using a newly entered address in the checkout when logged-in.
- Solve issue where Street+Housenumber+Addition would be send to Paazl on a single line.
- Solve issue where delivery dates weren't in the proper timezone
- Solve issue where the shipping options were empty because timezone differences.
- Solve issue where the pickup option would always be selected after the delivery type was changed
multiple times in the popup.
- Solve issue where opening the Paazl Perfect popup would reset the country.

## [1.3.0-rc1] - To be released

With the 1.3.0 release we're introducing a better way for Paazl to store customer address information in Magento. By default, Magento handles the street information in a single field and uses multiple lines to store the information. This is considered suboptimal as it causes a lot of problems with compatibility. New separate Customer attributes are introduced: street_name, house_number, house_number_addition.

*EE Only: If you're already using custom Customer attributes (e.g. `house number`), make sure it follows Paazl's exact naming convention (it are varchar fields).*

- All UI components that have address fields are replaced with the new customer attributes (frontend / backend).
- All existing addresses are automatically converted to the new format when they are loaded.
- To maintain 100% compatibility, before saving the customer address a new flattened value is stored in the original street attribute.

### Improvements / Bug fixes
A lot of bug fixes and small improvements are included in this release:
- The product matrix is now configurable from A-ZZ instead of A-Z.
- Added option to enable the postcode validation (disabled by default due to UI issues with addresses outside the Netherlands, better UI planned for a future release).
- Checkout field sort order set to: Street Number, House Number, House Number Addition, Postcode, City, State, Country
- Solve issue in the cart where the tax would not be calculated properly because Paazl expects a full address instead of only a postcode. (Reported by ISM e-Company)
- Solve issue in the cart where filling in your state would throw a JS error.
- Solve issue where saving a customer address would throw an exception. CE Only
- Solve issue where a reorder would throw an exception. (Reported by Guapa)
- Solve issue where having a different configuration per website would cause issues (Reported by Guapa).

### Known Issues / Limitations
- 'Admin Panel Order Create' and 'Frontend multishipping' does not support Paazl Perfect, only basic functionality is supported.
- ~~When the cron job is trying to communicate orders to Paazl and Paazl throws an error for a single order, the rest of the orders wont be processed.~~ Solved in rc2
- ~~Core Bug: Multi Address Checkout: New address does not show house number.~~ (Ticket #03212624) Solved in rc2
- Core Bug: Customer Address field are not update for order edit address.  (Ticket #03212673)
    - Backend, open order, choose ‘edit’ at billing address. change the house number, field is not updated for the order.
    - If you order as logged-in customer and choose for a different billing address on the payment step then it does not save the house number.

## [1.2.10] - 2017-07-06
### Changed
- Bugfix commit order would not use correct paazl account in a multistore setup

## [1.2.9] - 2017-06-02
### Changed
- Bugfix matrix code when empty

## [1.2.8] - 2017-05-24
### Changed
- added body of request during exception logging

## [1.2.7] - 2017-05-24
### Changed
- prevent undefined variable responseTime during exception

## [1.2.6] - 2017-05-24
### Changed
- Bugfix matrix code
- Add more debug information to Paazl Log when exception occurs

## [1.2.5] - 2017-05-12
### Changed
- Bugfix error message when Paazl is offline
- Bugfix logged in user could not go to Paazl Perfect popup with saved address when coming from product
- Fixup region
- Bugfix loading correct addres data for Paazl Perfect popup

## [1.2.4] - 2017-05-11
### Changed
- Bugfix logged in user could not go to Paazl Perfect popup with saved address
- Bugfix load correct address data for Paazl Perfect popup with saved address

## [1.2.3] - 2017-05-09
### Changed
- Add magento errors to paazl_log for cronjobs
- Bugfix issue when adding address with wrong postcode + house nr or loading a saved adres.
- Bugfix when checkoutStatus gives a half response
- Bugfix no house number on payment step in checkout

## [1.2.2] - 2017-05-05
### Changed
- Added response time to paazl_log
- Bugfix when there are no servicePoints in Paazl Perfect

## [1.2.1] - 2017-05-05
### Changed
- Make Matrix attribute into a select

## [1.2.0] - 2017-05-04
### Changed
- Add notification for delivery type IDM
- Automatically create shipment
- Automatically create shipping labels
- Allow to show Paazl error messages in the checkout or display a custom message
- Bugfix 'select a shipping method' after choosing something in paazl perfect popup
- Bugfix issue radio buttons become disabled after page refresh. See: https://github.com/magento/magento2/issues/7497
- Set correct shipping method coming from Paazl
- Add customsValue for Fedex orders

## [1.0.5] - 2017-04-26
### Changed
- Bugfix for checkoutRequest with error code 1053
- Bugfix class name
- Allow shipping methods who don't have a delivery date

## [1.0.4] - 2017-04-24
### Changed
- Bugfix when there is no lowerBound delivery time.
- Set same text as in popup when only lowerBound or upperBound is available
- Bugfix "Undefined index: deliveryDates" when a delivery has no dates

## [1.0.3] - 2017-04-20
### Changed
- Added preferred delivery date to commitOrder

## [1.0.1] - 2017-04-18
### Changed
- Don't show carrier title

## [1.0.0] - 2017-04-18
### Note
- First release of Paazl_Shipping.
