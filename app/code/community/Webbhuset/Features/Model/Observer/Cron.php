<?php

/**
 * Cron observer.
 *
 * @copyright Copyright (C) 2016 Webbhuset AB
 * @author Webbhuset AB <info@webbhuset.se>
 */
class Webbhuset_Features_Model_Observer_Cron
{
    /**
     * Shuffles store category product position.
     *
     * @access public
     * @return void
     */
    public function shuffleStoreCategories()
    {
        $stores = Mage::app()->getStores();

        foreach ($stores as $store) {
            if (!Mage::getStoreConfigFlag('whfeatures/category/shuffle_products', $store)) {
                continue;
            }

            Mage::getResourceSingleton('whfeatures/category')->shuffleStoreCategoryProducts($store);
        }
    }
}
