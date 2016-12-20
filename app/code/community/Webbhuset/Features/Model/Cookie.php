<?php
/**
 * Cookie class
 *
 * @copyright   Copyright (C) 2016 Webbhuset AB
 * @author      Webbhuset AB <info@webbhuset.se>
 */
class Webbhuset_Features_Model_Cookie
    extends Mage_Core_Model_Cookie
{
    /**
     * Is https secure request
     * Use secure on all cookies or just adminhtml, based on system setting
     *
     * @return bool
     */
    public function isSecure()
    {
        if ($this->getStore()->isAdmin()) {
            return $this->_getRequest()->isSecure();
        } elseif (Mage::getStoreConfig('whfeatures/cookies/use_secure_cookie')) {
            return $this->_getRequest()->isSecure();
        }

        return false;
    }
}
