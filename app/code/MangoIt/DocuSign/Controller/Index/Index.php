<?php
namespace MangoIt\DocuSign\Controller\Index;



use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Action\Context;
use \Magento\Framework\UrlInterface;
use \Magento\Framework\App\Filesystem\DirectoryList;


class Index extends \Magento\Framework\App\Action\Action {

    /**
     * @var \MangoIt\DocuSign\Controller\objectManager
     */
    private $objectManager;

    /**
     * @var \MangoIt\DocuSign\Controller\scopeConfig
     */
    private $scopeConfig;

    /**
     * @var \MangoIt\DocuSign\Controller\mode
     */

    private $liveMode;

    /**
     * @var \MangoIt\DocuSign\Controller\sandboxEndPoint
     */

    private $sandboxEndPoint;
    /**
     * @var \MangoIt\DocuSign\Controller\liveEndPoint
     */    
    private $liveEndPoint;
    
    /**
     * @var \MangoIt\DocuSign\Controller\apiUserName
     */

    private $apiUserName;

    /**
     * @var \MangoIt\DocuSign\Controller\apiPassword
     */

    private $apiPassword;
    /**
     * @var \MangoIt\DocuSign\Controller\integratorKey
     */

    private $integratorKey;

    /**
     * @var \MangoIt\DocuSign\Controller\templateId
     */

    private $templateId;
    
    /**
     * @var \MangoIt\DocuSign\Controller\subject
     */

    private $subject;    

    private $url; 

    private $customerSession;
    
    private $customerMapVariable = array();

    private $filePath;

    private $pdfDir;

    private $base64dir;



    public function __construct(Context $context) {

        parent::__construct($context);

        
        
    }

    public function execute() {

        
       // $this->backupCode();
        $this->stepFirst();
        die;


    }

    public function backupCode(){

        /*Directory Setup*/
        $objectManager = ObjectManager::getInstance();

        $fileSystem = $objectManager->get('\Magento\Framework\Filesystem');

        $mediaPath  =   $fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        

        #PDF DIRECTORY PATH
        $pdfDir = $mediaPath."order_pdf_summery";

        #BASE64 DIRECTORY PATH
        $base64dir = $mediaPath."order_base64_summery";

        if(!file_exists($pdfDir)){
            mkdir($pdfDir, 0777);
        }

        if(!file_exists($base64dir)) {
            mkdir($base64dir, 0777);
        }


        /******************/


        $orderModel = $objectManager->create("\Magento\Sales\Model\Order");
        
        $order = $orderModel->load(18);
        $orderId = $order->getId();

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
                    "quantity"=> number_format($_item->getQtyOrdered(),1),
                    "price" => number_format($_item->getPrice(), 2)
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

                    //$finalArray['options'] = array($bundleFinalArray);
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
            $mpdf = new \Mpdf\Mpdf(['tempDir' =>'/home/www/devicedesk/pub/media/mpdf_temp']);
            $mpdf->WriteHTML($orderPDF);
            #To download
            //$mpdf->output("order_".$orderId.".pdf", "D");
            #To save
            $pdfFile = $pdfDir."/order_".$orderId.".pdf";
            $b = is_writable($pdfFile);
            
            $mpdf->output("/home/www/devicedesk/pub/media/order_pdf_summery/order_".$orderId.".pdf", "F");
          

            chmod($pdfFile, 0777);

            $pdfData = file_get_contents($pdfFile);
            // alternatively specify an URL, if PHP settings allow
            $base64Sring = base64_encode($pdfData);

            //$base64File = $base64dir."/base64_".$orderId.".txt";

            //echo $base64File;
            //chmod($base64File, 777);

            //$int = file_put_contents($base64File, $base64Sring);


            /** End PDF code **/

            $productIdStr = "Please find order summery";
            $CustomFields = $objectManager->create('MangoIt\DocuSignCustomFields\Model\CustomField');
            $docusingData = $CustomFields->getCollection()->getData();
            $mappingData = array();
            if(isset($docusingData[0])){
                $mappingData = json_decode($docusingData[0]['docusing_data'],true); 
            }


            $dataArray['accountno'] = '123456';
            $dataArray['address'] = $addressStr;
            $dataArray['company'] = $companyName;
            $dataArray['contactname'] = $customerName;
            $dataArray['email'] = $customerEmail;
            $dataArray['orderid'] = $orderId;
            $dataArray['items'] = $productIdStr;
            $dataArray['phone'] = $telephone;
            $dataArray['orderdate'] = $order->getCreatedAt();

            $customFields = array();
            foreach ($mappingData as $mappingField) {
                if(isset($dataArray[$mappingField["docusing_map_value"]])) {
                    $customFields[] = array(
                        "tabLabel"=> $mappingField["docusing_label"],
                        "value"=>$dataArray[$mappingField["docusing_map_value"]]
                    );
                }
            }
        }

            $helper = $objectManager->get('MangoIt\DocuSign\Helper\Data');
            $helper->createEnvelop(18, $customFields);


    }

    public function orderHtml($orderItems = array(), $pdfOrderVariable = array()) {

        $om = ObjectManager::getInstance();
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
        <span style="font-family:Arial; font-size:24px;color:#4d4843"><strong>Order #'.$pdfOrderVariable['orderId'].'</strong></span>
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
        $shipping_handling = $pdfOrderVariable['shippingAndHandling'];   
        $tax = $pdfOrderVariable['taxAmount'];
        $grandTotal = 0;

        if(!empty($orderItems)):
            foreach($orderItems as $productArray):
                 foreach($productArray as $product):
                    $finalHtml .=  '<tr>';
                    $finalHtml .= ' <td style="width:50%; color:#4d4843;font-size: 16px;vertical-align: top;"><strong>'.$product['name'].'</strong></td>';
                    if($product['type'] == "simple" ) {
                        if(!empty($product['options'])) {
                            $finalHtml .='<table>';
                            foreach($product['options'] as $option) :
                                $finalHtml.= '<tr><td><strong style="color:#000;">'.$option['title'].'</strong></td></tr>';
                                $finalHtml.= '<tr><td>'.$option['value'].'</td></tr>';
                            endforeach;
                            $finalHtml .='</table>';
                                
                        }
                    }

                    if($product['type'] == "configurable" ) {
                        if(!empty($product['options'])) {
                            $finalHtml .='<table>';
                            foreach($product['options'] as $option) :
                                $finalHtml.= '<tr><td><strong style="color:#000;">'.$option['title'].'</strong></td></tr>';
                                $finalHtml.= '<tr><td>'.$option['value'].'</td></tr>';
                            endforeach;
                            $finalHtml .='</table>';
                                
                        }
                    }


                    if($product['type'] == "bundle" ) {
                        if(!empty($product['options'])) {
                            $finalHtml .='<table>';
                            foreach($product['options'] as $option) :
                                $finalHtml.= '<tr><td><strong style="color:#000;">'.$option['title'].'</strong></td></tr>';
                                $finalHtml.= '<tr><td>'.(int)$option['qty'].'&nbsp;x&nbsp;'.$option['title'].'&nbsp;'.$option['price'].'</td></tr>';
                            endforeach;
                            $finalHtml .='</table>';
                                
                        }
                    }


                    $finalHtml .= '<td style="vertical-align: top;color:#4d4843;f">'.$product['sku'].'</td>';
                    $finalHtml .= '<td style="vertical-align: top;color:#4d4843;"><strong>'.$product['price'].'</strong></td>';
                    $finalHtml .= '<td style="vertical-align: top;color:#4d4843;font-weight: bold;">'.(int)$product['quantity'].'</td>';
                    $subTotal = number_format($product['quantity']*$product['price'],2);
                    $finalHtml .= '<td style="vertical-align: top;font-weight: bold;color:#4d4843;">$'.$subTotal.'</td>';
                    $finalHtml .=  '<\tr>';
                
                $total += $product['quantity']*$product['price'];
                endforeach;
            endforeach;
        endif;                                    

        $finalHtml .= '<tr><td><br/></td></tr>
        <tr>
        <td style="border-top:1px solid #ddd;font-size: 18px;"></td>
        <td style=" border-top:1px solid #ddd;font-size: 18px; padding-right: 70px;text-align:right;" colspan="3"><span style="float: right;">Sub Total</span></td>
        <td style=" color:#4d4843; border-top:1px solid #ddd;font-size: 18px;">$'.number_format($total,2).'</td>
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
        $finalHtml .= '<td style="width:10%;border-bottom:1px solid #ddd;font-size: 18px; font-weight: bold;font-size: 20px;">$'.$pdfOrderVariable['grandTotal'] .'</td>
        </tr>

        </table>

        </td>
        </tr>
        </table>
        </body>
        </html>';
        return $finalHtml;    

    }

    private function stepFirst(){

        $objectManager = ObjectManager::getInstance();
        $helper = $objectManager->get('MangoIt\DocuSign\Helper\Data');
        $helper->createEnvelop();
        //echo "<pre>";
        //print_r(get_class_methods($helper));
        die;

    }





}


