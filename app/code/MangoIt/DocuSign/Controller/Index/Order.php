<?php
namespace MangoIt\DocuSign\Controller\Index;



use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Action\Context;
use \Magento\Framework\UrlInterface;


class Order extends \Magento\Framework\App\Action\Action {

     /**
     * @var \MangoIt\DocuSign\Controller\objectManager
     */
     private $objectManager;

    /**
     * @var \MangoIt\DocuSign\Controller\scopeConfig
     */
    public $scopeConfig;

    private $url; 

    private $customerSession;

    public $scopeConfigInterface;

    private $apiBaseUrl;

    private $accountId;
    
    private $config = array();




    public function __construct(Context $context) {
        parent::__construct($context);


        $this->objectManager = ObjectManager::getInstance();
        $this->scopeConfigInterface = 'Magento\Framework\App\Config\ScopeConfigInterface';
        $this->scopeConfig = $this->objectManager->get($this->scopeConfigInterface);

        $this->config['live_mode'] = $this->scopeConfig->getValue('docusing_settings/api_settings/sandbox_mode');
        $this->config['sandbox_endpoint'] = $this->scopeConfig->getValue('docusing_settings/api_settings/api_sandbox_hostname');
        $this->config['live_endpoint'] = $this->scopeConfig->getValue('docusing_settings/api_settings/sandbox_mode');

        $this->config['api_username'] = $this->scopeConfig->getValue('docusing_settings/api_settings/api_user_name');
        $this->config['api_password'] = $this->scopeConfig->getValue('docusing_settings/api_settings/api_password');
        $this->config['integrator_key'] = $this->scopeConfig->getValue('docusing_settings/api_settings/api_integrator_key');


        $this->config['account_id'] = $this->scopeConfig->getValue('docusing_settings/api_settings/account_id');

        $this->config['template_id'] = $this->scopeConfig->getValue('docusing_settings/mangoit_template_settings/template_id');
        $this->config['subject'] = $this->scopeConfig->getValue('docusing_settings/mangoit_template_settings/subject');

    }



    public function execute() {

        //echo "<pre>";
        //print_r($this->config);
        ///echo "</pre>";
        //die();


        $mpdf = new \Mpdf\Mpdf();
        $orderItems = array(
            array(
                'sku' => 'Mobile LED',
                'name' => 'Mobile LED',
                'quantity' => '1.0000',
                'price' => '5.0000'
            ),
            array(
                'sku' => 'Mobile',
                'name' => 'Mobile',
                'quantity' => '1.0000',
                'price' => '100.0000'
            ),
            array(
                'sku' => 'M-cover-360',
                'name' => 'Mobile Soft cover for S3 to save from getting damaged',
                'quantity' => '1.0000',
                'price' => '20.0000'
            ),
            array(
                'sku' => 'Mobile Back cover',
                'name' => 'Mobile Back cover',
                'quantity' => '1.0000',
                'price' => '10.0000'
            )
        );
        
        $orderId = '1000001';
        $orderDate = date('d-m-Y');
        $orderPDF =  $this->orderHtml($orderItems, $orderId, $orderDate);
        $mpdf->WriteHTML($orderPDF);
        $mpdf->output();
        die;

        #PDF DIRECTORY PATH
        $pdfDir = "pub/order_pdf_summery";

        #BASE64 DIRECTORY PATH
        $base64Dir = "pub/order_base64_summery";

        if(!file_exists($pdfDir)){
            mkdir($pdfDir, 0777);
        }

        if(!file_exists($base64Dir)) {
            mkdir($base64Dir, 0777);
        }

        
        $pdfFile = $pdfDir."/pdf_".$orderId.".pdf";
        $mpdf->Output($pdfFile, 'f');

        chmod($pdfFile, 0777);

        $pdfData = file_get_contents($pdfFile);
        // alternatively specify an URL, if PHP settings allow
        $base64Sring = base64_encode($pdfData);

        

        $base64File = $base64Dir."/base64_".$orderId.".txt";
        chmod($base64File, 0777);

        $int = file_put_contents($base64File, $base64Sring);

        if($int > 0 ){
            echo "File saved on ".$base64File;
        } else {
            echo "Error in file creation";
        }

        die;

    }

    public function getPDF($filename = ''){

        if( $filename ) {
            $pdf_base64 = $filename;
            //Get File content from txt file
            $pdf_base64_handler = fopen($pdf_base64,'r');
            $pdf_content = fread ($pdf_base64_handler,filesize($pdf_base64));
            fclose ($pdf_base64_handler);
            //Decode pdf content
            $pdf_decoded = base64_decode ($pdf_content);
            //Write data back to pdf file
            $pdf = fopen ('decoded.pdf','w');
            fwrite ($pdf,$pdf_decoded);
            //close output file
            fclose ($pdf);
            echo 'Done';    
        } else {
            echo "Invalid File Name";
        }

    }

    /**
    @param $orderItems array array of itmes 
    @param $orderId order id
    @param $orderDate date of order
    @return type string html of prepared
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


