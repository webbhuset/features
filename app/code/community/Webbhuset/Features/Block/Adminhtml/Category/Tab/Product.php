<?php

/**
 * Cateogry product grid.
 *
 * @copyright Copyright (C) 2016 Webbhuset AB
 * @author Webbhuset AB <info@webbhuset.se>
 */
class Webbhuset_Features_Block_Adminhtml_Category_Tab_Product
    extends Mage_Adminhtml_Block_Catalog_Category_Tab_Product
{
    /**
     * Set product collection.
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @access public
     * @return Webbhuset_Features_Block_Adminhtml_Category_Tab_Product
     */
    public function setCollection($collection)
    {
        Mage::dispatchEvent(
            'whfeatures_category_product_grid_collection',
            [
                'grid'          => $this,
                'collection'    => $collection,
            ]
        );

        return parent::setCollection($collection);
    }

    /**
     * Prepare grid columns
     *
     * @access protected
     * @return Webbhuset_Features_Block_Adminhtml_Category_Tab_Product
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter(
            'type',
            [
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '100px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
            ],
            'sku'
        );

        Mage::dispatchEvent(
            'whfeatures_category_product_grid_columns',
            [
                'grid' => $this,
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Prepare layout.
     *
     * @access protected
     * @return Webbhuset_Features_Block_Adminhtml_Category_Tab_Product
     */
    protected function _prepareLayout()
    {
        $this->setChild('shuffle_products',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Shuffle Product Order'),
                    'onclick'   => $this->getJsObjectName().'.shuffleProducts()',
                ))
        );

        return parent::_prepareLayout();
    }

    /**
     * Returns button html for shuffle button.
     *
     * @access public
     * @return string
     */
    public function getShuffleButtonHtml()
    {

        return $this->getChildHtml('shuffle_products');
    }

    /**
     * Returns main buttons html.
     *
     * @access public
     * @return string
     */
    public function getMainButtonsHtml()
    {
        $html = $this->getShuffleButtonHtml()
              . parent::getMainButtonsHtml();

        return $html;
    }

    /**
     * Returns additional javascript for product grid.
     *
     * @access public
     * @return string
     */
    public function getAdditionalJavaScript()
    {
        $js = (string)$this->getData('additional_java_script');
        $objName = $this->getJsObjectName();
        $categoryId = $this->getCategory()->getId();
        $url = $this->getUrl('*/webbhuset_features/shuffleCategory', ['id' => $categoryId]);

        $js .= <<<JAVASCRIPT
$objName.shuffleProducts = function() {

    var url = '{$url}';
    var lastMatch = false;
    var params = this.url.match(/[^\/]+/g).reduce(function(params, item){
        if (['sort', 'dir', 'filter'].indexOf(item) >= 0) {
            params.push(item);
            lastMatch = true;

            return params;
        }

        if (lastMatch) {
            params.push(item);
            lastMatch = false;
        }
        return params;
    }, []);
    url = url + params.join('/');
    new Ajax.Request(url, {
        loaderArea: this.containerId,
        parameters: this.reloadParams || {},
        evalScripts: true,
        onFailure: this._processFailure.bind(this),
        onComplete: this.initGridAjax.bind(this),
        onSuccess: function(transport) {
                    try {
                        var responseText = transport.responseText.replace(/>\s+</g, '><');

                        if (transport.responseText.isJSON()) {
                            var response = transport.responseText.evalJSON()
                            if (response.error) {
                                alert(response.message);
                            }
                            if(response.ajaxExpired && response.ajaxRedirect) {
                                setLocation(response.ajaxRedirect);
                            }
                        } else {
                            /**
                             * For IE <= 7.
                             * If there are two elements, and first has name, that equals id of second.
                             * In this case, IE will choose one that is above
                             *
                             * @see https://prototype.lighthouseapp.com/projects/8886/tickets/994-id-selector-finds-elements-by-name-attribute-in-ie7
                             */
                            var divId = $(this.containerId);
                            if (divId.id == this.containerId) {
                                divId.update(responseText);
                            } else {
                                $$('div[id="'+this.containerId+'"]')[0].update(responseText);
                            }
                        }
                    } catch (e) {
                        var divId = $(this.containerId);
                        if (divId.id == this.containerId) {
                            divId.update(responseText);
                        } else {
                            $$('div[id="'+this.containerId+'"]')[0].update(responseText);
                        }
                    }

        }.bind(this)
    });
}
JAVASCRIPT;

        return $js;
    }
}
