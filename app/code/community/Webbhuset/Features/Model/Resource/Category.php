<?php

/**
 * Category resource class.
 *
 * @copyright Copyright (C) 2016 Webbhuset AB
 * @author Webbhuset AB <info@webbhuset.se>
 */
class Webbhuset_Features_Model_Resource_Category
    extends Mage_Catalog_Model_Resource_Category
{
    /**
     * Keeps ids of root categories already shuffled.
     *
     * @var mixed
     * @access protected
     */
    protected $_rootCategoriesShuffled = [];

    /**
     * Shuffles the product position order on categories matching ids.
     *
     * @param array $ids
     * @access public
     * @return Webbhuset_Features_Model_Resource_Category
     */
    public function shuffleCategoryProducts(array $categoryIds = null)
    {
        $adapter = $this->_getWriteAdapter();
        $select  = $adapter->select()
            ->from(
                ['cp' => $this->_categoryProductTable],
                [
                    'category_id' => 'cp.category_id',
                    'product_id'  => 'cp.product_id'
                ]
            )
            ->order('cp.category_id, RAND()');

        if ($categoryIds) {
            $select->where('cp.category_id IN (?)', $categoryIds);
        }

        $this->_updatePosition($select);

        return $this;
    }

    /**
     * Shuffles category products in each store.
     *
     * @param mixed $store
     * @access public
     * @return Webbhuset_Features_Model_Resource_Category
     */
    public function shuffleStoreCategoryProducts($store)
    {
        $rootId  = $store->getRootCategoryId();
        $adapter = $this->_getWriteAdapter();

        if (isset($this->_rootCategoriesShuffled[$rootId])) {
            return $this;
        }

        $select = $adapter->select()
            ->from(
                ['cp' => $this->_categoryProductTable],
                ['category_id', 'product_id']
            )
            ->join(
                ['e' => $this->getEntityTable()],
                "e.entity_id = cp.category_id AND e.path LIKE '1/{$rootId}/%'",
                []
            )
            ->order('cp.category_id, RAND()');

        $this->_updatePosition($select);
        $this->_rootCategoriesShuffled[$rootId] = 1;

        return $this;
    }

    /**
     * Updates catagory product position in categories matching select.
     *
     * @param Varien_Db_Select $select
     * @access protected
     * @return void
     */
    protected function _updatePosition($select)
    {
        $adapter = $this->_getWriteAdapter();

        $position = $adapter->select()
            ->from(
                ['src' => $select],
                [
                    'position' => $adapter->getCheckSql(
                        '@lastId = src.category_id',
                        '@r := @r + 1',
                        '(@lastId := src.category_id) * (@r := 0)' // Sets r to 0 and assigns category id to lastId.
                    )
                ]
            )
            ->where('dest.product_id = src.product_id')
            ->where('dest.category_id = src.category_id');

        $adapter->raw_query('SET @r = 0;');
        $adapter->raw_query('SET @lastId = 0;');
        $update = $adapter->updateFromSelect($position, ['dest' => $this->_categoryProductTable]);
        $adapter->query($update);
    }
}
