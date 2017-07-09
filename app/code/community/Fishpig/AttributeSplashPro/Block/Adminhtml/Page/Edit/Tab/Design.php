<?php
/**
 * @category    Fishpig
 * @package     Fishpig_AttributeSplash
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_AttributeSplashPro_Block_Adminhtml_Page_Edit_Tab_Design extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	 * Add the design elements to the form
	 *
	 * @return $this
	 */
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('splash_')->setFieldNameSuffix('splash');
        
		$this->setForm($form);

		$fieldset = $form->addFieldset('splash_design_page_layout', array(
			'legend'=> $this->helper('adminhtml')->__('Page Layout'),
			'class' => 'fieldset-wide',
		));

		$fieldset->addField('page_layout', 'select', array(
			'name' => 'page_layout',
			'label' => $this->__('Page Layout'),
			'title' => $this->__('Page Layout'),
			'values' => Mage::getSingleton('splash/system_config_source_layout_update')->toOptionArray(),
		));
		
		$fieldset->addField('layout_update_xml', 'editor', array(
			'name' => 'layout_update_xml',
			'label' => $this->__('Layout Update XML'),
			'title' => $this->__('Layout Update XML'),
			'style' => 'width:600px;',
		));
		
		$form->setValues($this->_getFormData());
		
		return parent::_prepareForm();;
	}
	
	/**
	 * Retrieve the data used for the form
	 *
	 * @return array
	 */
	protected function _getFormData()
	{
		return ($page = Mage::registry('splash_page')) !== null ? $page->getData() : array();
	}
}
