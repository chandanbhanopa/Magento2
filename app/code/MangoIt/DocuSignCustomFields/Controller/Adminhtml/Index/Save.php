<?php 

namespace MangoIt\DocuSignCustomFields\Controller\Adminhtml\Index;

class Save extends \Magento\Backend\App\Action {
	
	public function execute() {
		$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
		$helper = $object_manager->get('MangoIt\DocuSignCustomFields\Helper\Data');
		$apiField = $helper->getCustiomFields();

		/*Model Object*/
		

		//$CustomFields->setData();
		/**
		field_id
		label
		type
		map_value
		created_at
		updated_at
		*/
		//echo "<pre><br>";
		$postData = $this->getRequest()->getParams();

		
		
		

		
		$saveFieldArray = array();
		foreach($postData as $key=>$postField){
			if(isset($apiField[$key])) {
				$saveFieldArray[] = array(
					'docusing_id'=>$apiField[$key]['id'],
					'docusing_label'=> $apiField[$key]['label'],
					'docusing_type'=> $apiField[$key]['type'],
					'docusing_map_value'=> $postField
				);				
			}
		}
		
		$data = array("docusing_data"=>json_encode($saveFieldArray));
		$CustomFields = $this->_objectManager->create('MangoIt\DocuSignCustomFields\Model\CustomField');
		$dataIfExist = $CustomFields->getCollection()->getData();
		if(!empty($dataIfExist)) {
			$CustomFields->setId($dataIfExist[0]['id']);
			$CustomFields->setDocusingData(json_encode($saveFieldArray));
			$CustomFields->save();
			$this->messageManager->addSuccess(__('Document field updated'));
	    	$this->_redirect('*/*/');
        	return;
		}

		$CustomFields->setData($data);
	    $CustomFields->save();
	    $this->messageManager->addSuccess(__('Document field saved'));
	    $this->_redirect('*/*/');
        return;

		
	}
}