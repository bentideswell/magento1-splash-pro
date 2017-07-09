<?php
/**
 * @category    Fishpig
 * @package    Fishpig_AttributeSplashPro
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_AttributeSplashPro_Block_Layer_View extends Mage_Catalog_Block_Layer_View
{
	/**
	 * Get layer object
	 *
	 * @return Mage_Catalog_Model_Layer
	*/
	public function getLayer()
	{
		return Mage::getSingleton('splash/layer');
	}
	
	/**
	 * Ensure the default Magento blocks are used
	 *
	 * @return $this
	 */
    protected function _initBlocks()
    {
    	parent::_initBlocks();
        
		$this->_stateBlockName = 'Mage_Catalog_Block_Layer_State';
		$this->_categoryBlockName = 'Mage_Catalog_Block_Layer_Filter_Category';
		$this->_attributeFilterBlockName = 'Mage_Catalog_Block_Layer_Filter_Attribute';
		$this->_priceFilterBlockName = 'Mage_Catalog_Block_Layer_Filter_Price';
		$this->_decimalFilterBlockName = 'Mage_Catalog_Block_Layer_Filter_Decimal';

        if (Mage::helper('splash')->isFishPigSeoInstalledAndActive()) {
			$this->_categoryBlockName = 'Fishpig_FSeo_Block_Catalog_Layer_Filter_Category';
			$this->_attributeFilterBlockName = 'Fishpig_FSeo_Block_Catalog_Layer_Filter_Attribute';
			$this->_priceFilterBlockName = 'Fishpig_FSeo_Block_Catalog_Layer_Filter_Price';
			$this->_decimalFilterBlockName = 'Fishpig_FSeo_Block_Catalog_Layer_Filter_Decimal';
		}
        
        return $this;
    }
    
    /**
     * Remove the price filter
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
		parent::_prepareLayout();

		return $this->unsetChild('price_filter');
    }
    
    /**
     * Retrieve the filterable attributes but remove the price (decimal) attributes
     *
     * @return
     */
	protected function _getFilterableAttributes()
	{
		if (!$this->hasData('_filterable_attributes')) {
			$attributes = parent::_getFilterableAttributes();
			
			foreach($attributes as $key => $attribute) {
				if ($attribute->getAttributeCode() === 'price' || $attribute->getBackendType() === 'decimal') {
					$attributes->removeItemByKey($key);					
				}
			}
		}
		
		return parent::_getFilterableAttributes();
	}
}
