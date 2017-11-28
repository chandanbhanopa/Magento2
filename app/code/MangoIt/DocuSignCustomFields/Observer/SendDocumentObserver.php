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
		$helper = $objectManager->get('MangoIt\DocuSign\Helper\Data');
		$helper->apiAuthentication();
		//$helper = $objectManager->get('MangoIt\DocuSignCustomFields\Helper\Data');

		#Directory Object
		$directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');

		$dataArray = array();
		
		$orderId = $observer->getEvent()->getOrderIds();
		$order = $this->order->load($orderId);
		$orderId = $order->getId();
		$customerData = array(
                            "name"=>$order->getCustomerName(), 
                            "email"=>$order->getCustomerEmail()
                        );
		#values that will be shown on pdf file	
		$pdfOrderVariable = array(
			"orderId"=>$order->getIncrementId(),
			"orderDate"=>$order->getCreatedAt(),
			"shippingAndHandling"=>number_format($order->getShippingAmount(),2),
			"taxAmount"=>number_format($order->getBaseTaxAmount(),2),
			"grandTotal"=>number_format($order->getBaseGrandTotal(),2)
		);  
	
		
	
		$shipping_address = $order->getBillingAddress()->getData();
		
		
		if ($shipping_address) {
			
			$firstname = (!empty($shipping_address['firstname'])) ? $shipping_address['firstname'] : '';
			$middlename = (!empty($shipping_address['middlename'])) ? " ".$shipping_address['middlename'] : '';
			$lastname = (!empty($shipping_address['lastname'])) ? " ".$shipping_address['lastname'] : '';
			$customerName = $firstname.$middlename.$lastname;
			$customerEmail = $shipping_address['email'];
			$telephone = $shipping_address['telephone'];
			$companyName = $shipping_address['company'];

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
			/* Start foreach loop for preparing array*/
			foreach ($order->getAllVisibleItems() as $key => $_item) {

				
				$productOptions = array();
				$productOptions = $_item->getProductOptions();
				$simpleProductOptions = array();
				$bundleProductOptions = array();
				$productType = $_item->getProductType();

				$finalArray =  array(
					"item_id"=>$_item->getId(),
					"type"=>$_item->getProductType(),
					"sku" => $_item->getSku(),
					"name" => $_item->getName(),
					"quantity"=> $_item->getQtyOrdered(),
                    "price" => $_item->getPrice()
				);



				if( $productType == "simple" ) {
					if(isset($productOptions['options'])) {
						$simpleProductOptions = $productOptions['options'];
					}
					$simpleOptionsFinalArray = array();
					if(count($simpleProductOptions) > 0 ){
						foreach($simpleProductOptions as $simpleOptionKey => $simpleOptionValue) {
							$simpleOptionsFinalArray[] =  array(
								"title" =>$simpleOptionValue['label'],
								"value" =>$simpleOptionValue['value'],
								"qty" =>'',
								"price" =>''
							);       
						}
					}
					$finalArray['options'] = $simpleOptionsFinalArray;
					$productArray[$_item->getId()][] = $finalArray;
				} else if( $productType == "virtual") {
					$productArray[$_item->getId()][] = $finalArray;
				} else if($productType == "bundle") {
					if(isset($productOptions['bundle_options'])) {
						$bundleProductOptions = $productOptions['bundle_options'];
					}
					$bundleFinalArray = array();
					if(count($bundleProductOptions) > 0 ) {
						foreach($bundleProductOptions as $optionKey => $optionValue) {
							 $bundleFinalArray[$optionKey] = array(
                                "title"=>$optionValue['value'][0]['title'],
                                "value"=>"",
                                "qty" =>number_format($optionValue['value'][0]['qty'],1),
                                "price"=>number_format($optionValue['value'][0]['price'],2)

                            );

						}
					}

					$finalArray['options'] = $bundleFinalArray;
					$productArray[$_item->getId()][] = $finalArray;
				} else if($productType == "configurable") {
					$configurableOption = array();
					$configurableFinalArray = array();
					if(isset($productOptions['attributes_info'])) {
						$configurableOption = $productOptions['attributes_info'];
					}

					if(count($configurableOption) > 0 ) {
						foreach($configurableOption as $configurableOptionKey => $configurableOptionValue) {
							$configurableFinalArray[] = array(
                                                            "title"=>$configurableOptionValue['label'],
                                                            "value"=>$configurableOptionValue['value'],
                                                            "qty" =>"",
                                                            "price"=>""
                                                        ); 

						}
					}
					$finalArray['options'] = $configurableFinalArray;
					$productArray[$_item->getId()][] = $finalArray;
				} else{
					$productArray[$_item->getId()][] = $finalArray;
				}
			}
			
			
			/* End foreach*/


			/** Create html for pdf file**/
			
			$orderPDF = $this->orderHtml($productArray, $pdfOrderVariable);

			$pdfTempDir = $directory->getPath('media')."/mpdf_temp";

            $mpdf = new \Mpdf\Mpdf(['tempDir' =>$pdfTempDir,'default_font_size' => 25]);
			$mpdf->WriteHTML($orderPDF);
			$mpdf->SetDisplayMode('fullpage');

			$pdfFilePath = $directory->getPath('media')."/order_pdf_summery/order_".$orderId.".pdf";
			$mpdf->output($pdfFilePath, "F");
			
			/** End PDF code **/
			//$productStr = explode("\r\n", $productArray);
			//$productArray['total'] = $orderTotal;

			$productIdStr = "See Order Summery";
			


			$CustomFields = $objectManager->create('MangoIt\DocuSignCustomFields\Model\CustomField');
			$docusingData = $CustomFields->getCollection()->getData();
			$mappingData = array();
			if(isset($docusingData[0])){
				$mappingData = json_decode($docusingData[0]['docusing_data'],true);	
			}


			$customerOb = $objectManager->get("\Magento\Customer\Api\CustomerRepositoryInterface");
            $customer = $customerOb->getById($order->getCustomerId());

			$dataArray['accountno'] = '123456';
			$dataArray['address'] = $addressStr;
			$dataArray['company'] = $companyName;
			$dataArray['contactname'] = $customerName;
			$dataArray['email'] = $customerEmail;
			$dataArray['orderid'] = $order->getIncrementId();
			$dataArray['items'] = $productIdStr;
			$dataArray['phone'] = $telephone;
			$dataArray['orderdate'] = $order->getCreatedAt();

			$customFields = array();
			$customerAttributes = $customer->getCustomAttributes();
			foreach ($mappingData as $mappingField) {
				if(isset($dataArray[$mappingField["docusing_map_value"]])) {
					$customFields[] = array(
						"tabLabel"=> $mappingField["docusing_label"],
						"value"=>$dataArray[$mappingField["docusing_map_value"]]
					);
				} else {
                    if(array_key_exists($mappingField["docusing_map_value"], $customer->getCustomAttributes())){
                        $customerAttribute = $customer->getCustomAttribute($mappingField["docusing_map_value"])->getValue();
                        $customFields[] = array(
                            "tabLabel"=> $mappingField["docusing_label"],
                            "value"=>$customerAttribute
                        );
                    }
                        
                }
			}
			 
			/* API call */
	        $helper->createEnvelop($orderId, $customFields, $customerData);
		}

		
	}

    /**
     * Index action
     * @param $orderItems array array of itmes 
     * @param $orderId order id
     * @param $orderDate date of order
     * @return type string html of prepared
     * @return Page
     */       
    public function orderHtml($orderItems = array(), $pdfOrderVariable = array()) {

        // $om = ObjectManager::getInstance();
        // $storeManager = $om->get('Magento\Store\Model\StoreManagerInterface');
        // $imgname = 'sales_express.png';
        // $image = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $imgname;

        /* Added dynamic image code*/
        /* Will pick the magento image */
        $om = ObjectManager::getInstance();
        $imageObj= $om->get('\Magento\Theme\Block\Html\Header\Logo');
        $image = $imageObj->getLogoSrc();


        $finalHtml = '<!DOCTYPE html>
        <html>
        <head>
        <title>Order Summery</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <meta name="keywords" content="">
        </head>
        <body style="margin: auto; background:#FFF;font-family:Arial">
        <table width="150%" height="100%" border="0" cellspacing="5" cellpadding="0" align="center" style="margin:auto; background:#fff;">
        <tr>
        <td style="width: 100%">
        <table style="width: 100%">
        <tr>
        <td style="text-align: left;">
        <span style="width:100px; display:inline-block; "><img src = "'.$image.'" style="max-width:40%;"/></span><br/>
        </td>
        </tr>
        <tr>
        <td><br/></td>
        </tr>
        <tr>
        <td>
        <span style="font-family:Arial; font-size:20px;color:#4d4843"><strong>Proposal #'.$pdfOrderVariable['orderId'].'</strong></span>
        </td>
        </tr>

        </table>
        <br/>
        <table style="width: 100%;overflow: wrap;" cellpadding="5" cellspacing="0">
        <tr>
        <td style="width:40%; border-bottom:1px solid #ddd;font-size:13px;color:#4d4843;"><strong>Product Name</strong></td>
        <td style="width:20%; border-bottom:1px solid #ddd;font-size: 13px;color:#4d4843;"><strong>SKU</strong></td>
        <td style="width:12%; border-bottom:1px solid #ddd;font-size: 13px; color:#4d4843;"><strong>Price</strong></td>
        <td style="width:10%; border-bottom:1px solid #ddd;font-size: 13px;color:#4d4843; "><strong>Qty</strong></td>
        <td style="width:20%; border-bottom:1px solid #ddd;font-size: 13px;color:#4d4843;"><strong>Subtotal</strong></td>

        </tr>';

        $total = 0; 
        $shipping_handling = $pdfOrderVariable['shippingAndHandling'];   
        $tax = $pdfOrderVariable['taxAmount'];
        $grandTotal = 0;

        if(!empty($orderItems)):
            foreach($orderItems as $productArray):
                 foreach($productArray as $product):
                    $finalHtml .=  '<tr>';
                    $finalHtml .= ' <td style="width:50%;color:#000;font-size: 12px;vertical-align: top;"><strong>'.$product['name'].'</strong></td>';
                    if($product['type'] == "simple" ) {
                        if(!empty($product['options'])) {
                            $finalHtml .='<table style="overflow: wrap;">';
                            foreach($product['options'] as $option) :
                                // $finalHtml.= '<tr><td style="font-size: 12px;><strong style="color:#000;">'.$option['title'].'</strong></td></tr>';
                                $finalHtml.= '<tr><td style="font-size: 13px;">'.$option['value'].'</td></tr>';
                            endforeach;
                            $finalHtml .='</table>';
                                
                        }
                    }

                    if($product['type'] == "configurable" ) {
                        if(!empty($product['options'])) {
                            $finalHtml .='<table style="overflow: wrap;">';
                            foreach($product['options'] as $option) :
                                // $finalHtml.= '<tr><td style="font-size: 12px;><strong style="color:#000;">'.$option['title'].'</strong></td></tr>';
                                $finalHtml.= '<tr><td style="font-size: 13px;">'.$option['value'].'</td></tr>';
                            endforeach;
                            $finalHtml .='</table>';
                                
                        }
                    }


                    if($product['type'] == "bundle" ) {
                        if(!empty($product['options'])) {
                            $finalHtml .='<table style="overflow: wrap;">';
                            foreach($product['options'] as $option) :
                                // $finalHtml.= '<tr><td style="font-size: 12px;"><strong style="color:#000;">'.$option['title'].'</strong></td></tr>';
                                $finalHtml.= '<tr><td style="font-size: 13px;color:#000">'.(int)$option['qty'].'&nbsp;x&nbsp;'.$option['title'].'&nbsp;</td></tr>';
                            endforeach;
                            $finalHtml .='</table>';
                                
                        }
                    }


                    $finalHtml .= '<td style="vertical-align: top;color:#000;font-size:12px;">'.$product['sku'].'</td>';
                    $finalHtml .= '<td style="vertical-align: top;color:#000;font-size:12px;"><strong>'.number_format($product['price'],2).'</strong></td>';
                    $finalHtml .= '<td style="vertical-align: top;color:#000;font-weight: bold;font-size: 12px;">'.(int)$product['quantity'].'</td>';
                    $subTotal =  $product['quantity']*$product['price'];
                    $finalHtml .= '<td width="25%" style="vertical-align: top;font-weight: bold;color:#000;font-size:12px;">$'.number_format($subTotal, 2).'</td>';
                    $finalHtml .=  '<\tr>';
                
                $total += $product['quantity']*$product['price'];
                endforeach;
            endforeach;
        endif;                                    

        $finalHtml .= '<tr><td><br/></td></tr>
        <tr>
        <td style="border-top:1px solid #ddd;font-size:11px;"></td>
        <td style=" border-top:1px solid #ddd;font-size:12px;padding-right:70px;text-align:right;" colspan="3">
        <span style="float: right;font-size:12px">Sub Total</span></td>
        <td style=" color:#000; border-top:1px solid #ddd;font-size:12px;">$'.number_format($total,2).'</td>
        </tr>
        <tr>
        <td> </td>
        <td colspan="3" style="padding-right: 70px;text-align:right;"><span style="float: right; text-align:right;font-size:12px">Shipping &amp; Handling</span></td>
        <td style="width:50%; font-size: 12px;">$'.$shipping_handling.'</td>
        </tr>
        <tr>
        <td> </td>
        <td style="padding-right: 70px;text-align:right; ont-size: 12px;" colspan="3" ><span style="float: right;font-size:12px;">Tax</span></td>
        <td style="font-size:12px;">$'.$tax.'</td>
        </tr>
        <tr>
        <td style=" border-bottom:1px solid #ddd;font-size: 11px;"></td>
        <td style=" border-bottom:1px solid #ddd;font-size: 12px; padding-right: 70px;text-align:right;" colspan="3"><span style="float: right; font-weight: bold;font-size: 12px;">Grand Total</span></td>';
        $finalHtml .= '<td style="width:10%;border-bottom:1px solid #ddd;font-weight: bold;font-size: 12px;">$'.$pdfOrderVariable['grandTotal'] .'</td>
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