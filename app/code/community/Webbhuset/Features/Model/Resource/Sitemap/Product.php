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
    protected $_productsPageNumber  = 1;

    /**
     * Attribute code to exclude entities by
     *
     * @var string
     */
    protected $_excludedAttributeCode = 'exclude_from_sitemap';

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
     * Load and prepare next page of entities
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
    public function getCollection($storeId)
    {
        parent::getCollection($storeId);
        /* @var $store Mage_Core_Model_Store */
        $store = Mage::app()->getStore($storeId);
        if (!$store) {
            return false;
        }

        $this->_joinExcludeAttribute($storeId);

        return $this;
    }
    /**
     *  Filter out excluded by exclude attribute
     *
     * @param int $storeId
     * @return void
     */
    protected function _joinExcludeAttribute($storeId)
    {
        $attributeCode          = $this->_excludedAttributeCode;
        $excludeFromSitemapAtt  = Mage::getSingleton('catalog/product')->getResource()->getAttribute($attributeCode);

        if (!$excludeFromSitemapAtt) {
            return;
        }

        $this->_select->joinLeft(
            ['t1_'. $attributeCode => $excludeFromSitemapAtt->getBackend()->getTable()],
            implode(
                ' and ',
                [
                    'main_table.entity_id = t1_'. $attributeCode .'.entity_id',
                    't1_'. $attributeCode.".attribute_id = {$excludeFromSitemapAtt->getId()}"
                ]
            ),
            ['value_id']
        );
        if ($excludeFromSitemapAtt->getIsGlobal() == Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL) {
            $this->_select->where(
                'coalesce(t1_' . $attributeCode . '.value' . ',0) != ?', 1
            );
        } else {
            $ifCase = $this->_select->getAdapter()->getCheckSql('t2_' . $attributeCode . '.value_id > 0',
                't2_' . $attributeCode . '.value', 't1_' . $attributeCode . '.value'
            );
            $this->_select->joinLeft(
                ['t2_' . $attributeCode => $excludeFromSitemapAtt->getBackend()->getTable()],
                $this->_getWriteAdapter()->quoteInto(
                    't1_' . $attributeCode . '.entity_id = t2_' . $attributeCode . '.entity_id AND t1_'
                        . $attributeCode . '.attribute_id = t2_' . $attributeCode . '.attribute_id AND t2_'
                        . $attributeCode . '.store_id = ?', $storeId
                ),
                ['value_id']
            )
            ->where(
                'coalesce(' . $ifCase . ',0)' . ' != ?', 1);
        }
    }
}
