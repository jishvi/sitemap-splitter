<?php
/*
 * @package     Sitemapsplitter
 * @author      Jishnu V
 */
class Jishvi_Sitemapsplitter_Model_Sitemap extends Mage_Sitemap_Model_Sitemap
{

    protected $numRecords = 50000;
    protected $counter;
    protected $sitemapsplitter_enabled;

    protected function getConfig(){
        $this->counter = 0;
        $this->numRecords = 0;
        $storeId = Mage::app()->getStore()->getStoreId();
        $this->sitemapsplitter_enabled = Mage::getStoreConfig('sitemapsplitter/general/module_enable_disable',$storeId);
        if($this->sitemapsplitter_enabled == 1){
            $this->numRecords  = Mage::getStoreConfig('sitemapsplitter/general/numrecord');
            if( $this->numRecords > 50000) {
                Mage::getSingleton('adminhtml/session')->addNotice(
                    Mage::helper('sitemapsplitter')->__('Number of URLs in sitemap is more than 50000!'));
            }
        }
    }

    /**
     * Generate XML file
     *
     * @return Mage_Sitemap_Model_Sitemap
     */
    public function generateXml()
    {
        $this->getConfig();
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));

        if ($io->fileExists($this->getSitemapFilename()) && !$io->isWriteable($this->getSitemapFilename())) {
            Mage::throwException(Mage::helper('sitemapsplitter')->__('File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.', $this->getSitemapFilename(), $this->getPath()));
        }

        $io->streamOpen($this->getSitemapFilename());

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');

        $storeId = $this->getStoreId();
        $date    = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        /**
         * Generate categories sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/category/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/category/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/catalog_category')->getCollection($storeId);
        $categories = new Varien_Object();
        $categories->setItems($collection);
        Mage::dispatchEvent('sitemap_categories_generating_before', array(
            'collection' => $categories,
            'store_id' => $storeId
        ));
        foreach ($categories->getItems() as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
            $this->newSitemapCreator($io);

        }
        unset($collection);

        /**
         * Generate products sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/product/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/product/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/catalog_product')->getCollection($storeId);
        $products = new Varien_Object();
        $products->setItems($collection);
        Mage::dispatchEvent('sitemap_products_generating_before', array(
            'collection' => $products,
            'store_id' => $storeId
        ));
        foreach ($products->getItems() as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
            $this->newSitemapCreator($io);
        }
        unset($collection);

        /**
         * Generate cms pages sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/page/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/page/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/cms_page')->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
            $this->newSitemapCreator($io);
        }
        unset($collection);

        $io->streamWrite('</urlset>');
        $io->streamClose();

        $this->setSitemapTime(Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s'));
        $this->save();

        return $this;
    }
    /**
     * Check if the number of URLs is more than the configured value and create a new sitemaps
     * @param  [type] &$io
     */
    public function newSitemapCreator(&$io) {
        if($this->sitemapsplitter_enabled == 1){
            $this->counter++;
            if ( ($this->counter % $this->numRecords) == 0 ){
                $io->streamWrite('</urlset>');
                $io->streamClose();
                $newSiteMapName = preg_replace('/\.xml/', '-'.
                    round($this->counter/$this->numRecords).
                    '.xml', $this->getSitemapFilename());
                $io->streamOpen($newSiteMapName);
                $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>'."\n");
                $io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('sitemapsplitter')->__('The sitemap "%s" has been generated.',$newSiteMapName));

            }
        }
    }
}
