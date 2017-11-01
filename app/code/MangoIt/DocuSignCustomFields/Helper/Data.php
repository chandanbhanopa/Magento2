<?php 
namespace MangoIt\DocuSignCustomFields\Helper;

use Magento\Framework\App\ObjectManager;
use \Magento\Framework\UrlInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    /**
     * @var \MangoIt\DocuSign\Controller\objectManager
     */
    private $objectManager;

    /**
     * @var \MangoIt\DocuSign\Controller\scopeConfig
     */
    public $scopeConfig;

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

    //private $templateId ="28535de0-b158-435f-8d40-4c006c149e85";
    private $templateId;
    
    /**
     * @var \MangoIt\DocuSign\Controller\subject
     */

    private $subject;    

    private $url; 

    private $customerSession;

    public $scopeConfigInterface;

    private $apiBaseUrl;

    private $accountId;
    
    private $config = array();


    public function __construct(){
        
    }


    public function apiCall() {

        $this->registerValue();

        $this->apiAuthentication();
        
        //$this->getCustiomFields();
        //$this->postData();
        //$this->checkpost();

    }





    private function registerValue() {

        $this->objectManager = ObjectManager::getInstance();
        $this->scopeConfigInterface = 'Magento\Framework\App\Config\ScopeConfigInterface';
        $this->scopeConfig = $this->objectManager->get($this->scopeConfigInterface);

        $this->liveMode = $this->scopeConfig->getValue('docusing_settings/api_settings/sandbox_mode');
        $this->sandboxEndPoint = $this->scopeConfig->getValue('docusing_settings/api_settings/api_sandbox_hostname');
        $this->liveEndPoint = $this->scopeConfig->getValue('docusing_settings/api_settings/api_live_hostname');

        $this->apiUserName = $this->scopeConfig->getValue('docusing_settings/api_settings/api_user_name');
        $this->apiPassword = $this->scopeConfig->getValue('docusing_settings/api_settings/api_password');
        $this->integratorKey = $this->scopeConfig->getValue('docusing_settings/api_settings/api_integrator_key');

        $this->accountId = $this->scopeConfig->getValue('docusing_settings/api_settings/account_id');

        $this->templateId = $this->scopeConfig->getValue('docusing_settings/mangoit_template_settings/template_id');
        $this->subject = $this->scopeConfig->getValue('docusing_settings/mangoit_template_settings/subject');

    }

    private function apiAuthentication() {
        error_reporting(E_ALL);
        ini_set("dispaly_errors", 1);

        $this->apiUserName = "neeta.anylinuxwork@gmail.com";
        $this->apiPassword = "test@1234";
        $this->integratorKey = "7d717775-d616-4021-85cb-4fa14581b249";

        $url = "https://demo.docusign.net/restapi/v2/login_information";

        // construct the authentication header:
        $header = "<DocuSignCredentials><Username>" . $this->apiUserName . "</Username><Password>" . $this->apiPassword . "</Password><IntegratorKey>" . $this->integratorKey . "</IntegratorKey></DocuSignCredentials>";

        // STEP 1 - Login (to retrieve baseUrl and accountId)

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-DocuSign-Authentication: $header"));
        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ( $status != 200 ) {
            return (['ok' => false, 'errMsg' => "Error calling DocuSign, status is: " . $status]);
        }

        $response = json_decode($json_response, true);
        $accountId = $response["loginAccounts"][0]["accountId"];
        $baseUrl = $response["loginAccounts"][0]["baseUrl"];
        $this->apiBaseUrl = $response["loginAccounts"][0]["baseUrl"];
        curl_close($curl);



        // STEP 2 - Create and send envelope with one recipient, one tab, and one document
        
        // $curl = curl_init($baseUrl . "/envelopes/" . $this->templateId . "/documents" );
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-DocuSign-Authentication: $header"));
        // $json_response = curl_exec($curl);
        // $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // if ( $status != 200 ) {
        //     echo "error calling webservice, status is:" . $status;
        //     exit(-1);
        // }
        // $response = json_decode($json_response, true);
        // curl_close($curl);

        // $documentName = $response['envelopeDocuments'][0]['name'];
        // //--- display results
        // echo "Envelope has following document(s) information...\n";
        // echo "<pre>";
        // print_r($response); echo "\n";




        #https://demo.docusign.net/restapi/v2/accounts/3654315/envelopes/9faec732-8ed2-4e1f-9f73-3cfa6442b2ec/custom_fields
        //$curl1 = curl_init("https://demo.docusign.net/restapi/v2/accounts/3654315/documents/1/fields");
        //$curl1 = curl_init($baseUrl . "/envelopes/" . $this->templateId . "/tab_definitions" );
        



        
        
    }

    public function getCustiomFields() {

        $this->registerValue();
        $this->apiAuthentication();
        //$this->apiUserName = "neeta.anylinuxwork@gmail.com";
        //$this->apiPassword = "test@1234";
        //$this->integratorKey = "7d717775-d616-4021-85cb-4fa14581b249";
        #https://demo.docusign.net/restapi/v2/accounts/3654315/tab_definitions?custom_tab_only=true
        #//demo.docusign.net/restapi/v2/accounts/3654315/envelopes/9faec732-8ed2-4e1f-9f73-3cfa6442b2ec/
        $header = "<DocuSignCredentials><Username>" . $this->apiUserName . "</Username><Password>" . $this->apiPassword . "</Password><IntegratorKey>" . $this->integratorKey . "</IntegratorKey></DocuSignCredentials>";

        $curl = curl_init($this->apiBaseUrl."/tab_definitions?custom_tab_only=true");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-DocuSign-Authentication: $header"));
        
        
        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ( $status != 200 ) {
            echo "error calling webservice, status is:" . $status;
            exit(-1);
        }
        $response = json_decode($json_response, true);
        curl_close($curl);
        //--- display results
        //echo "Envelope has following document(s) information...\n";
        //echo "<pre>";
        //print_r($response['tabs']); echo "\n";
        $fields = array();
        foreach($response['tabs'] as $key=>$value){
            $fields[$value["tabLabel"]]= array(
                "id"=>$value["customTabId"],
                "label"=>$value["tabLabel"], 
                "type"=>$value["type"]

            );
        }
        return $fields;
    }

    private function getHeader() {
        $header  = "<DocuSignCredentials>";
        $header .=     "<Username>" . $this->apiUserName . "</Username>";
        $header .=     "<Password>" . $this->apiPassword . "</Password>";
        $header .=     "<IntegratorKey>" . $this->integratorKey . "</IntegratorKey>";
        $header .=  "</DocuSignCredentials>";
        return $header;
    }

    public function postData() {



        $this->apiUserName = "neeta.anylinuxwork@gmail.com";
        $this->apiPassword = "test@1234";
        $this->integratorKey = "7d717775-d616-4021-85cb-4fa14581b249";

        $url = "https://demo.docusign.net/restapi/v2/login_information";

        // construct the authentication header:
        $header = "<DocuSignCredentials><Username>" . $this->apiUserName . "</Username><Password>" . $this->apiPassword . "</Password><IntegratorKey>" . $this->integratorKey . "</IntegratorKey></DocuSignCredentials>";

        // STEP 1 - Login (to retrieve baseUrl and accountId)

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-DocuSign-Authentication: $header"));
        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ( $status != 200 ) {
            return (['ok' => false, 'errMsg' => "Error calling DocuSign, status is: " . $status]);
        }

        $response = json_decode($json_response, true);
        $accountId = $response["loginAccounts"][0]["accountId"];
        $baseUrl = $response["loginAccounts"][0]["baseUrl"];
        $this->apiBaseUrl = $response["loginAccounts"][0]["baseUrl"];
        curl_close($curl);


        echo $baseUrl;
        die;


    // Send to the /envelopes end point, which is relative to the baseUrl received above. 
        $curl = curl_init($this->apiBaseUrl."/envelopes");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $reqData);                                                                  
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($reqData),
            "X-DocuSign-Authentication: $header" )                                                                       
    );

        $json_response = curl_exec($curl);


        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ( $status != 201 ) {
            echo "Error calling DocuSign, status is:" . $status . "\nerror text: ";
            print_r($json_response); echo "\n";
            exit(-1);
        }
        $response = json_decode($json_response, true);

        echo "<pre>";
        print_r($response); 
        die();       
        

    }



    public function checkpost($customFields = array()) {

        $this->registerValue();
        // Input your info here:
        $email = $this->apiUserName;        // your account email (also where this signature request will be sent)
        $password = $this->apiPassword;      // your account password
        $integratorKey = $this->integratorKey;     // your account integrator key, found on (Preferences -> API page)
        $recipientName = "Test";     // provide a recipient (signer) name
        $templateId =  $this->templateId;       // provide a valid templateId of a template in your account

        //$templateRoleName = "signer";  // use same role name that exists on the template in the console
        $templateRoleName = "signer";  // use same role name that exists on the template in the console
        
        // construct the authentication header:
        $header = "<DocuSignCredentials><Username>" . $email . "</Username><Password>" . $password . "</Password><IntegratorKey>" . $integratorKey . "</IntegratorKey></DocuSignCredentials>";
        
        /////////////////////////////////////////////////////////////////////////////////////////////////
        // STEP 1 - Login (to retrieve baseUrl and accountId)
        /////////////////////////////////////////////////////////////////////////////////////////////////
        $url = "https://demo.docusign.net/restapi/v2/login_information";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-DocuSign-Authentication: $header"));
        
        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        if ( $status != 200 ) {
            echo "error calling webservice, status is:" . $status;
            exit(-1);
        }
        
        $response = json_decode($json_response, true);
        $accountId = $response["loginAccounts"][0]["accountId"];
        $baseUrl = $response["loginAccounts"][0]["baseUrl"];
        curl_close($curl);




    // --- display results
        echo "\naccountId = " . $accountId . "<br\>baseUrl = " . $baseUrl . "\n";

        if(empty($customFields)){
            $customFields = array(
                array(
                    "tabLabel"=>"ddaccount",
                    "value"=>"985631477"
                ),
                array( 
                    "tabLabel"=>"ddaddress",
                    "value"=>"14, Old Avanue"
                ),
                array(
                    "tabLabel"=>"ddcompany",
                    "value"=>"Device Desk"
                ),
                array(
                    "tabLabel"=>"ddcontactname",
                    "value"=>"Magento Contact Name"
                ),
                array(
                    "tabLabel"=>"ddemail",
                    "value"=>"abc@gmail.com"
                ),
                array(
                    "tabLabel"=>"ddorder",
                    "value"=>"Magento order"
                ),
                array(
                    "tabLabel"=>"ddorderblock",
                    "value"=>"Magento Order Items"
                ),
                array(
                    "tabLabel"=>"ddphone",
                    "value"=>"12345678"
                ),
                array(
                    "tabLabel"=>"ddsubmitdate",
                    "value"=>"14 Oct 2017"
                )
            );
        }
        
        $documents = array(
                "documentId" => 1,
                "name" =>"Order summery",
                "order"=>1,
                "pages"=>1,
                "documentBase64"=>''
        );
    /////////////////////////////////////////////////////////////////////////////////////////////////
    // STEP 2 - Create and envelope using one template role (called "Signer1") and one recipient
    /////////////////////////////////////////////////////////////////////////////////////////////////
        $data = array(
            "accountId" => $accountId, 
            "emailSubject" => "DocuSign API - Signature Request from Template",
            "templateId" => $templateId, 
            "templateRoles" => array( 
                array( 
                    //"email" => "greg.rudakov@devicedesk.com", 
                    "email" => "neeta.anylinuxwork@gmail.com", 
                    "roleName" => $templateRoleName ,
                    "tabs" => array(
                        "textTabs"=> $customFields
                    )
                )
            ),
            "status" => "sent"
        );                                                                    

        $data_string = json_encode($data);  
        echo "<br>";
        echo $data_string;
        //die;
        //$data_string =  $reqData;
        $curl = curl_init($baseUrl . "/envelopes" );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);                                                                  
        curl_setopt($curl, CURLOPT_HTTPHEADER, array( 
            'Content-Type: application/json', 
            'Content-Length: ' . strlen($data_string),
            "X-DocuSign-Authentication: $header" )                                                                       
    );

        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ( $status != 201 ) {
            echo "error calling webservice, status is:" . $status . "\nerror text is --> ";
            print_r($json_response); echo "\n";
            exit(-1);
        }

        $response = json_decode($json_response, true);
        $envelopeId = $response["envelopeId"];

    // --- display results

        echo "<br/>Document is sent! Envelope ID = " . $envelopeId . "\n\n"; 

    }


    public function curlRequest(){

    }


    /* Create an Envelop and return its id */
    /* Use POST method */
    /* Endpoint: https://demo.docusign.net/restapi/v2/accounts/<account ID>/envelopes */
    public function createEnvelop() {

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


    public function addDocumentToEnvelop(){

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
    

    public function applyTemplateToAddedDocument(){

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
    public function sendRequest(){

    }



}