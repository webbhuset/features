<?php

/**
 * Shuffle button widget.
 *
 * @copyright Copyright (C) 2016 Webbhuset AB
 * @author Webbhuset AB <info@webbhuset.se>
 */
class Webbhuset_Features_Block_Adminhtml_System_Config_ShuffleButton
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Set template to itself.
     *
     * @return Webbhuset_Marketplace_Block_Adminhtml_Fyndiq_Pushsettings
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!$this->getTemplate()) {
            $this->setTemplate('webbhuset/features/system/shuffle-store.phtml');
        }

        return $this;
    }

    /**
     * Returns ajax url.
     *
     * @access public
     * @return string
     */
    public function getAjaxUrl()
    {
        $config = Mage::getSingleton('adminhtml/config_data');
        $url = $this->getUrl(
            '*/webbhuset_features/shuffleStores',
            [
                'scope'     => $config->getScope(),
                'scope_id'  => $config->getScopeId(),
            ]
        );

        return $url;
    }

    /**
     * Returns button label depending on configuration scope.
     *
     * @access public
     * @return string
     */
    public function getButtonLabel()
    {
        $config     = Mage::getSingleton('adminhtml/config_data');
        $scope      = $config->getScope();
        $scopeId    = $config->getScopeId();

        switch ($scope) {
            case 'stores':
                $store = Mage::app()->getStore($scopeId);
                $label = $this->__('Suffle products in store %s', $store->getName());
                break;

            case 'websites':
                $website = Mage::app()->getWebsite($scopeId);
                $label = $this->__('Suffle products in website %s', $website->getName());
                break;

            default:
                $label = $this->__('Suffle products in all stores');
        }

        return $label;
    }

    /**
     * Unset some non-related element parameters.
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Get the button and scripts contents.
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }
}
