<?php
/**
 * Product Sitemap Resource model class
 *
 * @copyright Copyright (C) 2016 Webbhuset AB
 * @author Webbhuset AB <info@webbhuset.se>
 */
class Webbhuset_Features_Model_Resource_Sitemap_Product extends Mage_Sitemap_Model_Resource_Catalog_Product
{
        /**
    * Products page number
    *
    * @var int
    */
    protected $_productsPageNumber = 1;

     /**
     * Removed functionality
     *
     * @return Mage_Sitemap_Model_Resource_Catalog_Product
     */
    protected function _loadEntities()
    {
        return $this;
    }

     /**
     * Load and prepare nex page of entities
     *
     * @param int $page
     * @return array
     */
    public function getNextEntitiesPage()
    {
        $entities = array();
        $this->_select->limitPage($this->_productsPageNumber, 50000);
        $query = $this->_getWriteAdapter()->query($this->_select);
        while ($row = $query->fetch()) {
            $entity = $this->_prepareObject($row);
            $entities[$entity->getId()] = $entity;
        }
        $this->_productsPageNumber++;

        return $entities;
    }

    /**
     * Get product collection array
     *
     * @param int $storeId
     * @return array
     */
    public function getCollection($storeId , $ignoreUnRewritten = false)
    {
        /* @var $store Mage_Core_Model_Store */
        $store = Mage::app()->getStore($storeId);
        if (!$store) {
            return false;
        }

        $this->_select = $this->_getWriteAdapter()->select()
            ->from(array('main_table' => $this->getMainTable()), array($this->getIdFieldName()))
            ->join(
                array('w' => $this->getTable('catalog/product_website')),
                'main_table.entity_id = w.product_id',
                array()
            )
            ->where('w.website_id=?', $store->getWebsiteId());

        $storeId = (int)$store->getId();

        /** @var $urlRewrite Mage_Catalog_Helper_Product_Url_Rewrite_Interface */
        $urlRewrite = $this->_factory->getProductUrlRewriteHelper();
        $urlRewrite->joinTableToSelect($this->_select, $storeId, $ignoreUnRewritten);

        $this->_addFilter($storeId, 'visibility',
            Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds(), 'in'
        );
        $this->_addFilter($storeId, 'status',
            Mage::getSingleton('catalog/product_status')->getVisibleStatusIds(), 'in'
        );

        return $this->_loadEntities();
    }
}
