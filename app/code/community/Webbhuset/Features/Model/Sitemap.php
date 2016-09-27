<?php
/**
 * Sitemap model class
 *
 * @copyright Copyright (C) 2016 Webbhuset AB
 * @author Webbhuset AB <info@webbhuset.se>
 */
class Webbhuset_Features_Model_Sitemap
    extends Mage_Sitemap_Model_Sitemap
{
    /**
    * Sitemap file number
    *
    * @var int
    */
    protected $_fileNumber = 0;

    /**
    * Sitemap split limit
    *
    * @var int
    */
    protected $_limit = null;

    /**
    * Counter towards split limit
    *
    * @var int
    */
    protected $_index = 0;

    /**
     * Generate Sitemap Index XML file
     */
    protected function _createSitemapIndexFile()
    {
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));
        $storeId = $this->getStoreId();
        $date    = Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s');
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
        $sitemapFileName = $this->getSitemapFilename();

        $io->streamOpen($sitemapFileName);
        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $io->streamWrite('<siteMapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");

        for ($i = 1; $i <= $this->_fileNumber; $i++) {
            $fileName = $this->_getFilename($i);
            $xml = sprintf(
                '<sitemap><loc>%s</loc><lastmod>%s</lastmod></sitemap>',
                $this->_getSitemapUrl($fileName),
                $date
            );
            $io->streamWrite($xml . "\n");
        }
        $io->streamWrite('</siteMapindex>');
        $io->streamClose();
    }
    
    /**
    * Return url for Sitemap file
    *
    * @param String $fileName
    * @return String
    */
    protected function _getSitemapUrl($fileName)
    {
        $fileName = trim($this->getSitemapPath() . $fileName, '/');
        $url = Mage::helper('sitemap')->escapeHtml(
            Mage::app()->getStore($this->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $fileName
        );

        return $url;
    }

    /**
     * Get fileName for subSitemap XML file
     * 
     * @param int $number
     * @return String
     */
    protected function _getFilename($number = null)
    {
        if ($number === null) {
            $number = $this->_fileNumber;
        }
        $sitemapFileName = $this->getSitemapFilename();
        $fileName = sprintf(
            '%s-%02d.xml',
            basename($sitemapFileName, '.xml'),
            $number
        );

        return $fileName;
    }

    /**
     * check if new SubSitemap XML file is needed.
     *
     * @param Varien_Io_File $io
     * @return Varien_Io_File
     */
    protected function _checkFile($io) 
    {
        if ($this->_limit === null) {
            $this->_limit = Mage::getStoreConfig('sitemap/generate/split_limit');
        }
        if ($this->_index == $this->_limit) {
            $this->_closeFile($io);
            $io = $this->_newFile();
            $this->_index = 0;
        }
        $this->_index += 1;

        return $io;
    }

    /**
     * Initiate new SubSitemap XML file
     *
     * @return Varien_Io_File
     */
    protected function _newFile() 
    {
        $this->_fileNumber += 1;
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));
        $fileName = $this->_getFilename();

        if ($io->fileExists($fileName) && !$io->isWriteable($fileName)) {
            $message = Mage::helper('sitemap')->__(
                'File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.',
                $this->getSitemapFilename() ,
                $this->getPath()
            );
            Mage::throwException($message);
        }
        $io->streamOpen($fileName);
        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");

        return $io;
    }

    /**
     * Close SubSitemap XML file
     *
     * @param Varien_Io_File $io
     */
    protected function _closeFile($io) 
    {
        $io->streamWrite('</urlset>');
        $io->streamClose();
    }

    /**
     * Generate sitemap XML file
     *
     * @return Mage_Sitemap_Model_Sitemap
     */
    public function generateXml()
    {
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
            'collection' => $categories
        ));

        $io = $this->_newFile();
        foreach ($categories->getItems() as $item) {
            $io  = $this->_checkFile($io);
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod>'
                    . '<changefreq>%s</changefreq>'
                    . '<priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml . "\n");
        }
        unset($collection);

        /**
         * Generate Products sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/product/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/product/priority', $storeId);
        $collection = Mage::getResourceModel('whfeatures/sitemap_product')->getCollection($storeId);

        while ($products = $collection->getNextEntitiesPage()) {
            foreach ($products as $item) {
                $io  = $this->_checkFile($io);
                $xml = sprintf(
                    '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                    htmlspecialchars($baseUrl . $item->getUrl()),
                    $date,
                    $changefreq,
                    $priority
                );
                $io->streamWrite($xml . "\n");
            }
        }
        unset($collection);
        unset($products);

        /**
         * Generate Cms sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/page/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/page/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/cms_page')->getCollection($storeId);

        foreach ($collection as $item) {
            $io  = $this->_checkFile($io);
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml . "\n");
        }
        unset($collection);

        $this->_closeFile($io);

        if ($this->_fileNumber > 1) {
            $this->_createSitemapIndexFile();
        } else {
            $fileName = $this->_getFilename();
            $io->mv($fileName, $this->getSitemapFilename());
        }
        $this->setSitemapTime(Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s'));
        $this->save();

        return $this;
    }
}
