# Replicate Options 4 Magento

For those who has lots of options, choices and products, this can be a solution.
It's a simple extension that copies all options and choices from one source product to other products of your choice (based on a keyword on name).

*Tested on Magento 1.9.x*

## Installing

1. Save a backup of your Magento install.
2. Upload app folder to magento root.
3. Clean cache, log out/login on admin.
4. Configure and *ENABLE IT*!

## Configuring

You must enable the extension here:
System > Configuration > SmartIT > Replicate Options > General

You must configure the product source SKU here:
System > Configuration > SmartIT > Replicate Options > Product Source Filter

You can configure a keyword for filtering products by name here*:
System > Configuration > SmartIT > Replicate Options > Product Target Filter

*ATTENTION: If you do not configure a filter for target it will RUN ON ALL PRODUCTS!

## Running

Just access* this URL: http://magentoroot/replicateoptions

*The process runs for a while before outputing the result. So, please wait! Test it on a few products before doing a large change and see the expected results.

You can use filters directly on the URL too. The parameters that are not filled on the URL will be filled with the default configurations. You can use a target and source parameter simultaneously.

*Samples:*

Targeting just one sku:
http://magentoroot/replicateoptions?targetSKU=00000

Targeting one different (from config) keyword:
http://magentoroot/replicateoptions?targetKeyword=Donuts

Applying from a different source product (from config):
http://magentoroot/replicateoptions?sourceSKU=DonutModel

## Future

* Make it run as a cron job correctly
* Extend filters to use category, comma-separated list of ids and skus...
