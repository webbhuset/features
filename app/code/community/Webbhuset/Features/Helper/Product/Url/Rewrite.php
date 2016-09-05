<?php
/**
 * Product url rewrite helper
 *
 * @copyright   Copyright (C) 2016 Webbhuset AB
 * @author      Webbhuset AB <info@webbhuset.se>
 */
class Webbhuset_Features_Helper_Product_Url_Rewrite
    extends Mage_Catalog_Helper_Product_Url_Rewrite
{
    /**
     * Prepare and return select, left joins category table and sorts by category level to get category url.
     *
     * @param array $productIds
     * @param int $categoryId
     * @param int $storeId
     * @return Varien_Db_Select
     */
    public function getTableSelect(array $productIds, $categoryId, $storeId)
    {
        $useCategoryUrl = Mage::getStoreConfig('whfeatures/urls/add_category_url');

        if ($useCategoryUrl) {
            $select = $this->_connection->select()
                ->from($this->_resource->getTableName('core/url_rewrite'), array('product_id', 'request_path'))
                ->where('store_id = ?', (int)$storeId)
                ->where('is_system = ?', 1)
                ->where('product_id IN(?)', $productIds);
            
            $select->joinLeft($this->_resource->getTableName('catalog/category'), 'entity_id = category_id');

            if ($categoryId) {
                $select->where('category_id = ? OR category_id IS NULL', (int)$categoryId);
                $select->order('category_id ' . Varien_Data_Collection::SORT_ORDER_DESC);
            } else {
                $select->order('level ' . Varien_Data_Collection::SORT_ORDER_DESC);
            }

            return $select;
        }

        return parent::getTableSelect($productIds, $categoryId, $storeId);
    }
}
