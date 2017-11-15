<?php

namespace MangoIt\DocuSign\Helper;

use Magento\Framework\App\ObjectManager;
use \Magento\Framework\UrlInterface;
use Magento\Framework\App\Helper\Context;
use \Magento\Framework\App\Filesystem\DirectoryList;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

	private $config;

	private $apiSuffix = "/v2/accounts/";

    

    public function __construct(Context $context){

        parent::__construct($context);


        $this->objectManager = ObjectManager::getInstance();
        $this->scopeConfigInterface = 'Magento\Framework\App\Config\ScopeConfigInterface';
        $this->scopeConfig = $this->objectManager->get($this->scopeConfigInterface);
        $this->config['sandbox_mode']=$this->scopeConfig->getValue('docusing_settings/api_settings/sandbox_mode');
        $this->config['api_sandbox_hostname']=$this->scopeConfig->getValue('docusing_settings/api_settings/api_sandbox_hostname');
        $this->config['api_live_hostname']=$this->scopeConfig->getValue('docusing_settings/api_settings/api_live_hostname');
        $this->config['api_user_name']=$this->scopeConfig->getValue('docusing_settings/api_settings/api_user_name');
        $this->config['api_password']=$this->scopeConfig->getValue('docusing_settings/api_settings/api_password');
        $this->config['api_integrator_key']=$this->scopeConfig->getValue('docusing_settings/api_settings/api_integrator_key');
        $this->config['account_id']=$this->scopeConfig->getValue('docusing_settings/api_settings/account_id');
        $this->config['send_attachment']=$this->scopeConfig->getValue('docusing_settings/api_settings/send_attachment');
        $this->config['template_id']=$this->scopeConfig->getValue('docusing_settings/mangoit_template_settings/template_id');
        $this->config['subject'] = $this->scopeConfig->getValue('docusing_settings/mangoit_template_settings/subject');
        $this->config['recipient_email'] = $this->scopeConfig->getValue("docusing_settings/mangoit_template_settings/recipient_email");
        $this->config['recipient_full_name'] = $this->scopeConfig->getValue("docusing_settings/mangoit_template_settings/recipient_full_name");
        

    }

	
    /* Create an Envelop and return its id */
    /* Use POST method */
    /* Endpoint: https://demo.docusign.net/restapi/v2/accounts/<account ID>/envelopes */
    public function createEnvelop( $orderId = 18, $customFields = array(), $customerEmail) {
       


        $templateRoleName = "signer";
        $status = "created";
        
        #set header
        $header = "<DocuSignCredentials><Username>" . $this->config['api_user_name'] . "</Username><Password>" . $this->config['api_password'] . "</Password><IntegratorKey>" . $this->config['api_integrator_key'] . "</IntegratorKey></DocuSignCredentials>";
        if($this->config['sandbox_mode']){
            $baseUrl = $this->config['api_sandbox_hostname'];    
        } else {
            $baseUrl = $this->config['api_live_hostname'];    
        }
        
        $baseUrl = $baseUrl.$this->apiSuffix.$this->config['account_id'];
        /** Request formate**/
        $data = array(
            "accountId" => $this->config['account_id'], 
            "emailSubject" => "DocuSign API - Signature Request from Template",
            "templateId" => $this->config['template_id'], 
            "templateRoles" => array( 
                array( 
                    "email" => $this->config['recipient_email'],
                    "name" => $this->config['recipient_full_name'],
                    "roleName" => $templateRoleName ,
                    "tabs" => array(
                        "textTabs"=> $customFields
                    )
                )
            ),
            "status" => $status
        );  
        /*Convert into json*/
        $data_string = json_encode($data); 

        $curl = curl_init($baseUrl . "/envelopes" );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);                
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 
            'Content-Type: application/json', 
            'Content-Length: ' . strlen($data_string),
            "X-DocuSign-Authentication: $header")                                   
        );
        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $response = json_decode($json_response, true);
        $response['orderId'] = $orderId;
        
        #************* Creating logs *******************#
        $logArray = array("step"=>"Creating draft","method"=>"POST","parameters"=>$data,"response"=>$response);
        $this->logGeneration($logArray);
        #************* End logs *******************#


        if($status == "201") {
           $this->addDocumentToEnvelop($response);
        } else if($status == 200) {

        } else {
           //echo "Error calling DocuSign, status is: " . $status; 
        }


       

        
    }

   /**
    Step 2: Add the additional document(s)
    Endpoint: https://demo.docusign.net/restapi/v2/accounts/<account ID>/envelopes/<envelope ID>/documents
    Method : PUT

    Now that the draft is created, 
    you can add all the documents by making a PUT request to the following.
    Please note that you’ll need to convert the file binary to base64

    {

    "documents": [{

        "documentId": "2",

        "name": "Additional Document 1.pdf",

        "order": "2",

        "pages": "1",

        "documentBase64": "<insert base64 content here>"

    }]

    }

    */


    public function addDocumentToEnvelop($envelopeResponse = array()){
        #https://demo.docusign.net/restapi/v2/accounts/<account ID>/envelopes/<envelope ID>/documents
        #https://demo.docusign.net/restapi/v2/accounts/3654315/envelopes/9faec732-8ed2-4e1f-9f73-3cfa6442b2ec/documents
        //$baseUrl = $baseUrl."envelopes/".$envelopId."/documents";
        if(empty($envelopeResponse)){
            //echo "Invalid request";
            //die;
        }

        $orderId = $envelopeResponse['orderId']; 

        $header = "<DocuSignCredentials><Username>" . $this->config['api_user_name'] . "</Username><Password>" . $this->config['api_password'] . "</Password><IntegratorKey>" . $this->config['api_integrator_key'] . "</IntegratorKey></DocuSignCredentials>";
        if($this->config['sandbox_mode']){
            $baseUrl = $this->config['api_sandbox_hostname'];    
        } else {
            $baseUrl = $this->config['api_live_hostname'];    
        }
        

       
        $baseUrl = $baseUrl.$this->apiSuffix.$this->config['account_id'].$envelopeResponse['uri']."/documents";
        
        //$baseUrl = $baseUrl.$this->apiSuffix.$this->config['account_id']."/envelopes/".$this->config['template_id']."/documents/1";


        /**Load pdf file **/
        /*Directory Setup*/
        $objectManager = ObjectManager::getInstance();

        $fileSystem = $objectManager->get('\Magento\Framework\Filesystem');

        $mediaPath  =   $fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        

        #PDF DIRECTORY PATH
        $pdfDir = $mediaPath."order_pdf_summery";
        $pdfFile = $pdfDir."/order_".$orderId.".pdf";
        $orderFileName = "Summary_".$orderId.".pdf";
        
        $pdfData = file_get_contents($pdfFile);
        // alternatively specify an URL, if PHP settings allow
        $base64Sring = base64_encode($pdfData);
       
        $data = array("status"=>"template");
        $data['documents'][] = array(
            "name" => $orderFileName,
            "documentId"=>2,
            "documentBase64" => $base64Sring
        ); 

    
        $data_string = json_encode($data);  
        $curl = curl_init($baseUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT"); // note the PUT here
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 
            'Content-Type: application/json', 
            "Content-Disposition: form-data; filename=$orderFileName; documentid=1; fileExtension='pdf'",
            'Content-Length: ' . strlen($data_string),
            "X-DocuSign-Authentication: $header",
            'X-HTTP-Method-Override: PUT'
            )                                   
        );
        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response = json_decode($json_response, true);
        $response['status'] = $status;

        #************* Creating logs *******************#
        $logArray = array("step"=>"Adding document to envelope ","method"=>"PUT","parameters"=>array(
            "name" => $orderFileName,
            "documentId"=>2,
        ),"response"=>$response);
        $this->logGeneration($logArray);
        #************* End logs *******************#

        if($response['status'] == 200) {
           //$this->applyTemplateToAddedDocument($response);
            $this->updateRecipient($envelopeResponse['uri']);
            $this->sendRequest($envelopeResponse['uri']);
           
        } else {
          // echo "<br>Error calling DocuSign, status is: " . $status; 
        }


    }

    /**
    Step 3: Apply the original template to each added document
    Endpoint: https://demo.docusign.net/restapi/v2/accounts/<account ID>/envelopes/<envelope ID>/documents/<document ID>/templates

    Method: POST

    {

    "documentTemplates": [{

        "templateId": "1345312b-f341-abc3-9178-44h15f7d1gha",

        "documentId": "2",

        "documentStartPage": "1",

        "documentEndPage": "1"

    }]

    }

    */
    

    public function applyTemplateToAddedDocument($apiResponse = array()){

        # https://demo.docusign.net/restapi/v2/accounts/<account ID>/envelopes/<envelope ID>/documents/<document ID>/templates

       
        if($this->config['sandbox_mode']){
            $baseUrl = $this->config['api_sandbox_hostname'];    
        } else {
            $baseUrl = $this->config['api_live_hostname'];    
        }

        if(empty($apiResponse)){
          //return "Please try again";
        }
        
        $header = "<DocuSignCredentials><Username>" . $this->config['api_user_name'] . "</Username><Password>" . $this->config['api_password'] . "</Password><IntegratorKey>" . $this->config['api_integrator_key'] . "</IntegratorKey></DocuSignCredentials>";


        foreach($apiResponse['envelopeDocuments'] as $envelope) {
            $baseUrl =  $baseUrl.$this->apiSuffix.$this->config['account_id'].$envelope['uri']."/templates";
            
            $data['documentTemplates'][] = array(
                "templateId" => $this->config['template_id'],
                "documentId"=> $envelope['documentId'],
                "documentStartPage"=> "1",
                "documentEndPage"=> "6"
            );


            $data_string = json_encode($data);  
            $curl = curl_init($baseUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT"); // note the PUT here
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array( 
                'Content-Type: application/json', 
                'Content-Length: ' . strlen($data_string),
                "X-DocuSign-Authentication: $header",
                'X-HTTP-Method-Override: PUT'
                )                                   
            );
            $json_response = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $response = json_decode($json_response, true);
            $response['status'] = $status;
            
            echo "<pre>";
            print_r($response);
                


        }
        //$data = array("status"=>"template");
         
        

       
        
    }

    /**
    Step 4: Send the envelope

    With the documents added and templates re-applied, simply do a PUT on the envelope to send it out.

    Endpoint: https://demo.docusign.net/restapi/v2/accounts/<account ID>/envelopes/<envelope ID>

    Method: PUT

    {

    "status": "sent"

    }

    */
    public function sendRequest($envalopUrl = ""){
        #https://demo.docusign.net/restapi/v2/accounts/<account ID>/envelopes/<envelope ID>
        if($this->config['sandbox_mode']){
            $baseUrl = $this->config['api_sandbox_hostname'];    
        } else {
            $baseUrl = $this->config['api_live_hostname'];    
        }

        if(empty($apiResponse)){
           
        }
        
        $header = "<DocuSignCredentials><Username>" . $this->config['api_user_name'] . "</Username><Password>" . $this->config['api_password'] . "</Password><IntegratorKey>" . $this->config['api_integrator_key'] . "</IntegratorKey></DocuSignCredentials>";

        $baseUrl =  $baseUrl.$this->apiSuffix.$this->config['account_id'].$envalopUrl;
        

        $data = array("status"=>"sent");
        $data_string = json_encode($data);  
        $curl = curl_init($baseUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT"); // note the PUT here
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 
            'Content-Type: application/json', 
            'Content-Length: ' . strlen($data_string),
            "X-DocuSign-Authentication: $header",
            'X-HTTP-Method-Override: PUT'
            )                                   
        );
        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response = json_decode($json_response, true);
        $response['status'] = $status;

        #************* Creating logs *******************#
        $logArray = array("step"=>"Document Sent","method"=>"PUT","parameters"=>$data,"response"=>$response);
        $this->logGeneration($logArray);
        #************* End logs *******************#

        
    }

    private function logGeneration($data) {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/docusing.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(json_encode($data));
    }


    public function updateRecipient( $envelopeUrl = "" ) {
       
         #https://demo.docusign.net/restapi/v2/accounts/<ACCOUNTID>/envelopes/<ENVELOPEID>/recipients
        if($this->config['sandbox_mode']){
            $baseUrl = $this->config['api_sandbox_hostname'];    
        } else {
            $baseUrl = $this->config['api_live_hostname'];    
        }

        $header = "<DocuSignCredentials><Username>" . $this->config['api_user_name'] . "</Username><Password>" . $this->config['api_password'] . "</Password><IntegratorKey>" . $this->config['api_integrator_key'] . "</IntegratorKey></DocuSignCredentials>";

        $baseUrl = $baseUrl.$this->apiSuffix.$this->config['account_id'].$envelopeUrl."/recipients";

        $data['signers'][] = array(
            "email" => "neeta.anylinuxwork@gmail.com",
            "name"=>"Neeta B",
            "recipientId" => 1
        );

        $data['carbonCopies'][] = array(
            "name" => "Greg Rudakov",
            "email" => "greg.rudakov@devicedesk.com", 
            "recipientId" => 2
        );
         

    
        $data_string = json_encode($data);  
        $curl = curl_init($baseUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT"); // note the PUT here
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 
            'Content-Type: application/json', 
            'Content-Length: ' . strlen($data_string),
            "X-DocuSign-Authentication: $header",
            'X-HTTP-Method-Override: PUT'
            )                                   
        );
        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response = json_decode($json_response, true);
        $response['status'] = $status;
        
        #************* Creating logs *******************#
        $logArray = array("step"=>"Document Sent","method"=>"PUT","parameters"=>$data,"response"=>$response);
        $this->logGeneration($logArray);
        #************* End logs *******************#




    }
   


}