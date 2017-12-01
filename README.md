# sitemap-splitter
A simple Magento sitemap splitter

Number of URLs in a sitemap should be less than or equal to 50K according to google. This Magento module helps you split sitemap if you have more than 50K URLs(Product, category and CMS URLs).

## How to Install and Configure?
1. Copy the app folder into your Magento root folder.
2. Clear cache.
3. Goto `System / Configuration->Sitemap splitter->General Settings->Sitemap Splitter Options`.
4. Enable the module and specify the sitemap limit which is the number of URLs in a sitemap.
5. You can generate the sitemap from `Catalog->Google Sitemap`.
