<?php
/**
 * @category    Fishpig
 * @package    Fishpig_AttributeSplashPro
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_AttributeSplashPro_Adminhtml_SplashController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Determine ACL permissions
	 *
	 * @return bool
	 */
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('attributeSplash/splashpro')
			|| Mage::getSingleton('admin/session')->isAllowed('attributesplash/splashpro');
	}
	
	/**
	 * Display the list of all splash pages
	 *
	 * @return void
	 */
	public function indexAction()
	{
		$this->loadLayout();
		$this->_title('Splash Pro by FishPig');
		$this->renderLayout();
	}
	
	/**
	 * Allow the user to enter a new splash page
	 * This is just a wrapper for the edit action
	 *
	 * @return void
	 */
	public function newAction()
	{
		$this->_forward('edit');
	}
	
	/**
	 * Edit an existing splash page
	 *
	 * @return void
	 */
	public function editAction()
	{
		$titles = array(
			$this->_title('Splash Pro'),
		);

		if (($page = $this->_getPage()) !== false) {
			$titles[] = $this->_title($page->getName());
		}
		else {
			$titles[] = Mage::helper('cms')->__('New Page');
		}

		$this->loadLayout();
		
		foreach($titles as $title) {
			$this->_title($title);
		}
			
		$this->renderLayout();
	}
	
	/**
	 * Retrieve the data for the category tree as JSON
	 *
	 * @return void
	 */
	public function categoriesJsonAction()
	{
        $page = $this->_getPage();

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('splash/adminhtml_page_edit_tab_categories')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
	}

	/**
	 * Save a splash page
	 *
	 * @return void
	 */
	public function saveAction()
	{
		if ($data = $this->getRequest()->getPost('splash')) {
			$page = Mage::getModel('splash/page')
				->setData($data)
				->setId($this->getRequest()->getParam('id', null));

			$categoryIds = trim($this->getRequest()->getPost('category_ids'), ',');
			$categoryOperator = $this->getRequest()->getPost('category_operator', Fishpig_AttributeSplashPro_Model_Resource_Page::FILTER_OPERATOR_DEFAULT);
			
			if ($categoryIds !== '') {
				$categoryFilters = array(
					'ids' => explode(',', $categoryIds),
					'operator' => $categoryOperator,
				);
				
				$page->setCategoryFilters($categoryFilters);
			}

			try {
				$page->save();

				$this->_getSession()->addSuccess(Mage::helper('cms')->__('The page has been saved.'));
			}
			catch (Exception $e) {
				$this->_getSession()->addError($this->__($e->getMessage()));
			}
				
			if ($page->getId() && $this->getRequest()->getParam('back', false)) {
				$this->_redirect('*/*/edit', array('id' => $page->getId()));
				return;
			}
		}
		else {
			$this->_getSession()->addError($this->__('There was no data to save.'));
		}

		$this->_redirect('*/*');
	}
	
	/**
	 * Delete the selected spash page
	 *
	 * @return void
	 */
	public function deleteAction()
	{
		if ($objectId = $this->getRequest()->getParam('id')) {
			$object = Mage::getModel('splash/page')->load($objectId);
			
			if ($object->getId()) {
				try {
					$object->delete();
					$this->_getSession()->addSuccess($this->__('The Splash Page was deleted.'));
				}
				catch (Exception $e) {
					$this->_getSession()->addError($e->getMessage());
				}
			}
		}

		$this->_redirect('*/*');
	}
	
	/*
	 * Retrieve the current page
	 *
	 * @return false|Fishpig_AttributeSplashPro_Model_Page
	 */
	protected function _getPage()
	{
		if (($page = Mage::registry('splash_page')) !== null) {
			return $page;
		}

		$page = Mage::getModel('splash/page')->load($this->getRequest()->getParam('id', 0));

		if ($page->getId()) {
			Mage::register('splash_page', $page);
			return $page;
		}
		
		return false;
	}
	
	/*
	 *
	 *
	 *
	 */
	public function importAction()
	{
		try {
			if (!isset($_FILES['splash_import'])) {
				throw new Exception('Unable to find $_FILES key.');
			}
			
			$file = $_FILES['splash_import'];
			
			if ($file['type'] !== 'text/csv') {
				throw new Exception('Only CSV files are allowed.');
			}
			
			if (!$file['tmp_name']) {
				throw new Exception('There was an error uploading your import file.');
			}
			
			$tempFile = tempnam(Mage::getBaseDir('var'), 'splash-pro-import-') . '.csv';

			if (!move_uploaded_file($file['tmp_name'], $tempFile)) {
				throw new Exception('Unable to upload Splash Pro import file.');
			}

			$f   = fopen($tempFile, 'r');
			$csv = array();			
			
			while(($row = fgetcsv($f, $tempFile)) !== false) {
				$csv[] = $row;
			}
			
			fclose($f);
			unlink($tempFile);
			
			if (count($csv) < 2) {
				throw new Exception('CSV file must contain at least 2 rows (field names and data row).');
			}
			
			$titles = array_shift($csv);
			
			foreach($csv as $key => $value) {
				if (count($titles) !== count($value)) {
					unset($csv[$key]);
					continue;
				}
				
				$csv[$key] = array_combine($titles, $value);
			}

			$rowsImported = $this->_import($csv);
			
			if ($rowsImported === 0) {
				throw new Exception('No rows were imported.');
			}
			
			if ($rowsImported === 1) {
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('1 Splash Page was successfully imported.'));
			}
			else {
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__($rowsImported . ' Splash Pages were successfully imported.'));
			}
		}
		catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		
		return $this->_redirect('*/splash/index');
	}
	
	protected function _import(array $csv)
	{
		$allowedFields = array(
			'page_id',
			'name',
			'short_description',
			'description',
			'url_key',
			'page_title',
			'meta_description',
			'meta_keywords',
			'status',
			'store_id',
		);
		
		$rowsImported  = 0;
		
		foreach($csv as $row) {
			$page = Mage::getModel('splash/page');

			if (!empty($row['page_id'])) {
				if (!$page->load((int)$row['page_id'])->getId()) {
					continue;
				}
			}
			
			$requiresSave = false;
			
			foreach($row as $k => $v) {
				if (!in_array($k, $allowedFields)) {
					continue;
				}
				
				if ($v !== '') {
					$requiresSave = true;
					if ($k === 'store_id') {
						$page->setStores(explode(',', trim($v, ',')));
					}
					else {
						$page->setData($k, $v);
					}
				}
			}
			
			if ($requiresSave) {
				$page->save();
				$rowsImported++;
			}
		}
		
		return $rowsImported;
	}
}