<?php
require_once (Mage::getModuleDir('controllers', 'Mage_Adminhtml')
    . DS . 'SitemapController.php');
class Webbhuset_Features_SitemapController
    extends Mage_Adminhtml_SitemapController
{
    /**
     * Delete action
     */
    public function deleteAction()
    {
        // check if we know what should be deleted
        if ($id = $this->getRequest()->getParam('sitemap_id')) {
            try {
                // init model and delete
                $model = Mage::getModel('sitemap/sitemap');
                $model->setId($id);
                // init and load sitemap model

                /* @var $sitemap Mage_Sitemap_Model_Sitemap */
                $model->load($id);
                $fileNameFullPath = $model->getPreparedFilename();
                $trimmedPath = trim($fileNameFullPath, '.xml');
                foreach (glob($trimmedPath . '-[0-9][0-9]*.xml') as $file) {
                    // delete sitemap subfile
                    if ($model->getSitemapFilename() && file_exists($file)){
                        unlink($file);
                    }
                }
                // delete sitemap Indexfile
                if ($model->getSitemapFilename() && file_exists($fileNameFullPath)){
                    unlink($fileNameFullPath);
                }
                $model->delete();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('sitemap')->__('The sitemap has been deleted.')
                    );
                // go to grid
                $this->_redirect('*/*/');
                
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // go back to edit form
                $this->_redirect('*/*/edit', array('sitemap_id' => $id));
                
                return;
            }
        }
        // display error message
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('sitemap')->__('Unable to find a sitemap to delete.')
            );
        // go to grid
        $this->_redirect('*/*/');
    }
}