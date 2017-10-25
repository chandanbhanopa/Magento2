<?php

namespace MangoIt\DocuSignCustomFields\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Framework\App\ObjectManager;


class SendDocumentObserver implements ObserverInterface {

	protected $customerSession;
	protected $customerrepository;
	protected $order;

	public function __construct(
		Session $customerSession,
		CustomerRepositoryInterface $customerrepository,
		Order $order
	)
	{
		$this->customerSession = $customerSession;
		$this->customerrepository = $customerrepository; 
		$this->order = $order;
	}


	public function execute(\Magento\Framework\Event\Observer $observer) {
		$objectManager = ObjectManager::getInstance();
		$helper = $objectManager->get('MangoIt\DocuSignCustomFields\Helper\Data');
		

		$dataArray = array();

		/* Order Data */

		$orderId = $observer->getEvent()->getOrderIds();
		$order = $this->order->load($orderId);
		$orderId = $order->getId();
		$orderDate = $order->getCreatedAt();

		$shipping_address = $order->getShippingAddress()->getData();

		
		if ($shipping_address) {

			$firstname = (!empty($shipping_address['firstname'])) ? $shipping_address['firstname'] : '';
			$middlename = (!empty($shipping_address['middlename'])) ? " ".$shipping_address['middlename'] : '';
			$lastname = (!empty($shipping_address['lastname'])) ? " ".$shipping_address['lastname'] : '';

			$customerName = $firstname.$middlename.$lastname;
			$customerEmail = $shipping_address['email'];
			$telephone = $shipping_address['telephone'];
			$companyName = "MangoIt";

			$addressArray = array();
			$addressArray['street'] = $shipping_address['street'];
			$addressArray['city'] = $shipping_address['city'];
			$addressArray['state'] = $shipping_address['region'];
			$addressArray['postal_code'] = $shipping_address['postcode'];



			$countryCode = $shipping_address['country_id'];

			$country = $objectManager->create('\Magento\Directory\Model\Country')->loadByCode($countryCode);
			$countryName = $country->getName();

			$addressArray['country_name'] = $countryName;

			$addressStr = implode(', ', $addressArray);

			$productIds = array();
			foreach ($order->getAllItems() as $_item) {
				$productIds[] = $_item->getProductId();
			}


			$productIdStr = implode(",", $productIds);


			$CustomFields = $objectManager->create('MangoIt\DocuSignCustomFields\Model\CustomField');
			$docusingData = $CustomFields->getCollection()->getData();
			$mappingData = array();
			if(isset($docusingData[0])){
				$mappingData = json_decode($docusingData[0]['docusing_data'],true);	
			}

			echo "<pre>";
			print_r($mappingData);


			echo "<br>";


			$dataArray['accountno'] = '123456';
			$dataArray['address'] = $addressStr;
			$dataArray['company'] = $companyName;
			$dataArray['contactname'] = $customerName;
			$dataArray['email'] = $customerEmail;
			$dataArray['orderid'] = $orderId;
			$dataArray['items'] = $productIdStr;
			$dataArray['phone'] = $telephone;
			$dataArray['orderdate'] = $orderDate;

			$customFields = array();
			foreach ($mappingData as $mappingField) {
				if(isset($dataArray[$mappingField["docusing_map_value"]])) {
					$customFields[] = array(
							"tabLabel"=> $mappingField["docusing_label"],
							"value"=>$dataArray[$mappingField["docusing_map_value"]]
						);
				}
			}




			$helper->checkpost($customFields);
			//echo "<pre>";
			//print_r($customFields);

		}

		die;
	}
	
}