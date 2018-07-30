# Magento 2 - Wics Connector Module
This module can be used to connect your Magento 2 store to a Wics WMS.
 
## Features:
- Sync articles from Magento 2 to Wics
- Sync orders from Magento 2 to Wics
- Sync stock updates from Wics to Magento 2
- Sync shipments Track & Trace information from Wics to Magento 2
- Additional; When the Paazl Module is installed the configuration will be used in this module. Also the chosen shipment/pickup method will be send to Wics.

To use automatic import/export of Stock, Order and Shipment information, a proper configured cron job is required. 

More information about setting up a cron job for Magento 2 can be found here:
http://devdocs.magento.com/guides/v2.2/config-guide/cli/config-cli-subcommands-cron.html 