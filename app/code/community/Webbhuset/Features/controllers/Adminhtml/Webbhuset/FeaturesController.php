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
}
