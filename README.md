# sitemap-splitter
A simple Magento sitemap splitter

Number of URLs in a sitemap should be less than or equal to 50K according to google. This Magento module helps you split sitemap if you have more than 50K URLs(Product, category and CMS URLs).

## How to Install and Configure?
1. Copy the app folder into your Magento root folder.
2. Clear cache.
3. Goto `System / Configuration->Sitemap splitter->General Settings->Sitemap Splitter Options`.
4. Enable the module and specify the sitemap limit which is the number of URLs in a sitemap.
5. You can generate the sitemap from `Catalog->Google Sitemap`.

### Forked from jishvi/sitemap-splitter
1. Added empty helper class and supporting changes to resolve Magento Admin php fatal error when navigating to System > Permissions > Roles and clicking "Create New Role". This module does one thing, and does it well, it was just missing the helper class.
