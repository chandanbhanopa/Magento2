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
			$orderTotal = $order->getGrandTotal();
			$productIds = array();
			$productArray = array();

			foreach ($order->getAllItems() as $_item) {
				//$productArray[] = $_item->getName(); 
				$productArray[] = array(
					"sku" => $_item->getSku(),
					"name" => $_item->getName(),
					"quantity"=> $_item->getQtyOrdered(),
					"price" => $_item->getPrice()
				);				
			}
			echo "<pre>";
			print_r($productArray);
			die;
			//$productStr = explode("\r\n", $productArray);
			//$productArray['total'] = $orderTotal;

			$productIdStr = implode("\r\n", $productArray);
			


			$CustomFields = $objectManager->create('MangoIt\DocuSignCustomFields\Model\CustomField');
			$docusingData = $CustomFields->getCollection()->getData();
			$mappingData = array();
			if(isset($docusingData[0])){
				$mappingData = json_decode($docusingData[0]['docusing_data'],true);	
			}

			//echo "<pre>";
			//print_r($mappingData);
			//echo "<br>";

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




			$helper->checkpost($customFields, );
			//echo "<pre>";
			//print_r($customFields);

		}

		die;
	}

    /**
     * Index action
     * @param $orderItems array array of itmes 
     * @param $orderId order id
     * @param $orderDate date of order
     * @return type string html of prepared
     * @return Page
     */       
    public function orderHtml($orderItems = array(), $orderId = '', $orderDate = '') {


    	$om = \Magento\Framework\App\ObjectManager::getInstance();
    	$storeManager = $om->get('Magento\Store\Model\StoreManagerInterface');
    	$imgname = 'sales_express.png';
    	$image = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $imgname;

    	$finalHtml = '<!DOCTYPE html>
    	<html>
    	<head>
    	<title>Order Summery</title>
    	<meta name="viewport" content="width=device-width, initial-scale=1">
    	<meta http-equiv="content-type" content="text/html; charset=utf-8">
    	<meta name="keywords" content="">
    	</head>
    	<body style="margin: auto; background:#cecece;font-family:Arial">
    	<table width="100%" height="100%" border="0" cellspacing="30" cellpadding="0" align="center" style="margin:auto; background:#fff">
    	<tr>
    	<td style="width: 100%">
    	<table style="width: 100%">
    	<tr>
    	<td style="text-align: left;">
    	<span><img src = "'.$image.'"/></span><br/>
    	</td>
    	</tr>
    	<tr>
    	<td><br/></td>
    	</tr>
    	<tr>
    	<td>
    	<span style="font-family:Arial; font-size:24px;color:#4d4843"><strong>Order #'.$orderId.'</strong></span>
    	</td>
    	</tr>

    	</table>
    	<br/>
    	<table style="width: 100%;" cellpadding="10" cellspacing="0">
    	<tr>
    	<td style="width:63%; border-bottom:1px solid #ddd;font-size:18px;color:#4d4843;"><strong>Product Name</strong></td>
    	<td style="width:20%; border-bottom:1px solid #ddd;font-size: 18px;color:#4d4843;"><strong>SKU</strong></td>
    	<td style="width:10%; border-bottom:1px solid #ddd;font-size: 18px; color:#4d4843;"><strong>Price</strong></td>
    	<td style="width:10%; border-bottom:1px solid #ddd;font-size: 18px;color:#4d4843; "><strong>Qty</strong></td>
    	<td style="width:10%; border-bottom:1px solid #ddd;font-size: 18px;color:#4d4843;"><strong>Subtotal</strong></td>

    	</tr>';


    	$total = 0; 
    	$shipping_handling = 20;   
    	$tax = 10;
    	$grandTotal = 0;

    	if(!empty($orderItems)):
    		foreach($orderItems as $product):
    			$finalHtml .=  '<tr>
    			<td style=" color:#4d4843;font-size: 16px;"><strong>'.$product['name'].'</strong></td>
    			<td style=" color:#4d4843;f">'.$product['sku'].'</td>
    			<td style=" color:#4d4843;"><strong>'.$product['price'].'</strong></td>
    			<td style=" color:#4d4843;font-weight: bold;">'.$product['quantity'].'</td>
    			<td style=" font-weight: bold;color:#4d4843;">$'.$product['quantity']*$product['price'].'</td>
    			</tr>';
    			$total += $product['quantity']*$product['price'];
    		endforeach;
    	endif;                                    

    	$finalHtml .= '<tr><td><br/></td></tr>
    	<tr>
    	<td style="border-top:1px solid #ddd;font-size: 18px;"></td>
    	<td style=" border-top:1px solid #ddd;font-size: 18px; padding-right: 70px;text-align:right;" colspan="3"><span style="float: right;">Sub Total</span></td>
    	<td style=" color:#4d4843; border-top:1px solid #ddd;font-size: 18px;">$'.$total.'</td>
    	</tr>
    	<tr>
    	<td> </td>
    	<td colspan="3" style="padding-right: 70px;text-align:right;"><span style="float: right; text-align:right;">Shipping &amp; Handling</span></td>
    	<td style="width:10%;">$'.$shipping_handling.'</td>
    	</tr>
    	<tr>
    	<td> </td>
    	<td style="padding-right: 70px;text-align:right;" colspan="3" ><span style="float: right;">Tax</span></td>
    	<td >$'.$tax.'</td>
    	</tr>
    	<tr>
    	<td style=" border-bottom:1px solid #ddd;font-size: 18px;"></td>
    	<td style=" border-bottom:1px solid #ddd;font-size: 18px; padding-right: 70px;text-align:right;" colspan="3"><span style="float: right; font-weight: bold;font-size: 20px;">Grand Total</span></td>';
    	$grandTotal = $total + $shipping_handling +  $tax ;                      
    	$finalHtml .= '<td style="width:10%;border-bottom:1px solid #ddd;font-size: 18px; font-weight: bold;font-size: 20px;">$'.$grandTotal .'</td>
    	</tr>

    	</table>

    	</td>
    	</tr>
    	</table>
    	</body>
    	</html>';
    	return $finalHtml;    

    }


}