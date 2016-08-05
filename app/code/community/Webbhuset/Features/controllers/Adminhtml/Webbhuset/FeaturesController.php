<?php

/**
 * Feature controller.
 *
 * @copyright Copyright (C) 2016 Webbhuset AB
 * @author Webbhuset AB <info@webbhuset.se>
 */
class Webbhuset_Features_Adminhtml_Webbhuset_FeaturesController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Shuffle category products position.
     *
     * @access public
     * @return void
     */
    public function shuffleCategoryAction()
    {
        $id = $this->getRequest()->getParam('id');

        if (!$id) {
            return;
        }

        Mage::getResourceSingleton('whfeatures/category')->shuffleCategoryProducts([$id]);

        return $this->_forward('grid', 'catalog_category');
    }

    /**
     * Shuffle category products position in a store.
     *
     * @access public
     * @return void
     */
    public function shuffleStoresAction()
    {
        $request    = $this->getRequest();
        $scope      = $request->getParam('scope');
        $scopeId    = $request->getParam('scope_id');

        switch ($scope) {
            case 'stores':
                $stores = [Mage::app()->getStore($scopeId)];
                break;

            case 'websites':
                $stores = Mage::app()->getWebsite($scopeId)->getStores();
                break;

            default:
                $stores = Mage::app()->getStores();
        }

        foreach ($stores as $store) {
            Mage::getResourceSingleton('whfeatures/category')->shuffleStoreCategoryProducts($store);
        }

        $this->getResponse()
            ->setBody('OK');
    }

    /**
     * Check if is allowed by acl.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/categories');
    }
}
