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
}
