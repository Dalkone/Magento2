# Paazl Magento 2 Module
The Paazl Magento 2 module gives you instant access to the services of over 50 leading (parcel) carriers worldwide, offering you and your customers ultimate delivery flexibility. This allows you to expand cross-border faster, ship all products using a single solution and always meet your customers’ delivery demands.

[Changelog](CHANGELOG.md)


### Migrating from 1.2.x to a newer version
With the 1.3.0 release we're introducing a better way for Paazl to store customer address information in Magento. By default, Magento handles the street information in a single field and uses multiple lines to store the information. This is considered suboptimal as it causes a lot of problems with compatibility. New separate Customer attributes are introduced: street_name, house_number, house_number_addition.

If you're already using custom Customer attributes (EE only, e.g. `housenumber`). You can use your own fields by simply renaming your attributes to Paazl's exact naming convention (all should be simple varchar fields), the module will not overwrite your attributes.
