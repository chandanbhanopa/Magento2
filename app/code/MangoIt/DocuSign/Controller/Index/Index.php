<?php
namespace MangoIt\DocuSign\Controller\Index;



use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Action\Context;
use \Magento\Framework\UrlInterface;


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


    public function __construct(Context $context) {

        parent::__construct($context);

        
        
    }

    public function execute() {
        $objectManager = ObjectManager::getInstance();
        $orderModel = $objectManager->create("\Magento\Sales\Model\Order");
        $order = $orderModel->load(18);

        $orderTotal = $order->getGrandTotal();
        $productIds = array();
        $productArray = array();
        
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
                            "qty" =>$simpleOptionValue['value'],
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
                        $bundleFinalArray[] = $optionValue['value'][0]; 
                    }
                }

                $finalArray['options'] = $bundleFinalArray;
                $productArray[$_item->getId()][] = $finalArray;
            } else if($productType == "configurable") {
                $productArray[$_item->getId()][] = $finalArray;
            }
        }


         $this->orderHtml($productArray,"1001",date("d-m-Y"));
        


    }

    public function orderHtml($orderItems = array(), $orderId = '', $orderDate = '') {

        //print_r($orderItems);
        //die;
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
            foreach($orderItems as $productArray):
                 foreach($productArray as $product):
                    $finalHtml .=  '<tr>';
                    $finalHtml .= ' <td style=" color:#4d4843;font-size: 16px;"><strong>'.$product['name'].'</strong></td>';
                    $finalHtml .= '<td style=" color:#4d4843;f">'.$product['sku'].'</td>';
                    $finalHtml .= '<td style=" color:#4d4843;"><strong>'.$product['price'].'</strong></td>';
                    $finalHtml .= '<td style=" color:#4d4843;font-weight: bold;">'.$product['quantity'].'</td>';
                    $finalHtml .= '<td style=" font-weight: bold;color:#4d4843;">$'.$product['quantity']*$product['price'].'</td>';
                    $finalHtml .=  '<\tr>';
                
                $total += $product['quantity']*$product['price'];
                endforeach;
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


