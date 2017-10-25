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

        $this->objectManager = ObjectManager::getInstance();
        $scopeConfigInterface = 'Magento\Framework\App\Config\ScopeConfigInterface';
        $this->scopeConfig = $this->objectManager->get($scopeConfigInterface);

        /*URL interface*/
        $this->url = $this->objectManager->get('\Magento\Framework\UrlInterface');
        $loginUrl =  $this->url->getUrl('customer/account/login');
        /* Get user session*/
        $this->customerSession = $this->objectManager->get('Magento\Customer\Model\Session');

        $customer = $this->customerSession->getCustomer()->getData();
        $customerMapVariable['email'] = $customer['email'];
        $customerMapVariable['contact_name'] = $customer['firstname']." ".$customer['lastname'] ;
        $customerAddress = $this->getCustomerAddress($customer['default_shipping']);
        $contactNumber = array_shift($customerAddress);
        $companyName = array_shift($customerAddress);
        $customerMapVariable['address'] = implode(", ", $customerAddress);
        $customerMapVariable['phone_number'] = $contactNumber;
        $customerMapVariable['compnay_name'] = $companyName;
        $customerMapVariable['reference_number'] = "10001";
        if(!$this->customerSession->isLoggedIn()) {
            //$this->_redirect($loginUrl);
           //return;
        }
        
        
        $this->liveMode = $this->scopeConfig->getValue('docusing_settings/api_settings/sandbox_mode');
        $this->sandboxEndPoint = $this->scopeConfig->getValue('docusing_settings/api_settings/api_sandbox_hostname');
        $this->liveEndPoint = $this->scopeConfig->getValue('docusing_settings/api_settings/api_live_hostname');
        $this->apiUserName = $this->scopeConfig->getValue('docusing_settings/api_settings/api_user_name');
        $this->apiPassword = $this->scopeConfig->getValue('docusing_settings/api_settings/api_password');
        $this->integratorKey = $this->scopeConfig->getValue('docusing_settings/api_settings/api_integrator_key');

        $this->templateId = $this->scopeConfig->getValue('docusing_settings/mangoit_template_settings/template_id');
        $this->subject = $this->scopeConfig->getValue('docusing_settings/mangoit_template_settings/subject');

        
    }

    public function execute() {
        # lib\internal\LoadLibrary.php
        $ob = new \LoadLibrary();
        $ob->loadData();
        $this->signatureRequestFromTemplate();
        //$this->testapi();
    }

    public function signatureRequestFromTemplate() {
        /*Admin setting required*/
        $username = $this->apiUserName;
        /*Admin setting required*/
        $password = $this->apiPassword;
        /*Admin setting required*/
        $integrator_key = $this->integratorKey;  

        $loggedInUserEmail = $this->customerSession->getCustomer()->getEmail();
        $loggedInUsername = $this->customerSession->getCustomer()->getName();
        $roleName = "DS Sender";

        /*Template variable*/

        $emailTemplateId = $this->templateId;
        $emailTemplateSubject = $this->subject;
        // change to production (www.docusign.net) before going live
        /*Admin setting required*/
        if( $this->liveMode ){
            $host = $this->sandboxEndPoint;
        } else {
            $host = $this->sandboxEndPoint;
        }
        
        // create configuration object and configure custom auth header
        $config = new \DocuSign\eSign\Configuration();
        $config->setHost($host);
        
        $config->addDefaultHeader("X-DocuSign-Authentication", "{\"Username\":\"" . $username . "\",\"Password\":\"" . $password . "\",\"IntegratorKey\":\"" . $integrator_key . "\"}");

        // instantiate a new docusign api client
        $apiClient = new \DocuSign\eSign\ApiClient($config);
        $accountId = null;
        
        try {
            //*** STEP 1 - Login API: get first Account ID and baseURL

            $authenticationApi = new \DocuSign\eSign\Api\AuthenticationApi($apiClient);
            $options = new \DocuSign\eSign\Api\AuthenticationApi\LoginOptions();
            $loginInformation = $authenticationApi->login($options);
            if(isset($loginInformation) && count($loginInformation) > 0)
            {
                $loginAccount = $loginInformation->getLoginAccounts()[0];
                $host = $loginAccount->getBaseUrl();
                $host = explode("/v2",$host);
                $host = $host[0];
                
                // UPDATE configuration object
                $config->setHost($host);
                
                // instantiate a NEW docusign api client (that has the correct baseUrl/host)
                $apiClient = new \DocuSign\eSign\ApiClient($config);
                
                if(isset($loginInformation))
                {
                    $accountId = $loginAccount->getAccountId();
                    
                    if(!empty($accountId))
                    {
                        //*** STEP 2 - Signature Request from a Template
                        // create envelope call is available in the EnvelopesApi
                        $envelopeApi = new \DocuSign\eSign\Api\EnvelopesApi($apiClient);
                        
                        // assign recipient to template role by setting name, email, and role name.  Note that the
                        // template role name must match the placeholder role name saved in your account template.
                        $templateRole = new  \DocuSign\eSign\Model\TemplateRole();

                        /*Registered user email id*/
                        $templateRole->setEmail($loggedInUserEmail);
                        /*Registered user name*/
                        $templateRole->setName($loggedInUsername);
                        /*Role name*/
                        $templateRole->setRoleName($roleName);             

                        // instantiate a new envelope object and configure settings
                        $envelop_definition = new \DocuSign\eSign\Model\EnvelopeDefinition();
                        
                        /* Admin setting for subject */
                        $envelop_definition->setEmailSubject($emailTemplateSubject);
                        /* Admin setting for template id */
                        $envelop_definition->setTemplateId($emailTemplateId);

                        $envelop_definition->setTemplateRoles(array($templateRole));
                        
                        // set envelope status to "sent" to immediately send the signature request
                        $envelop_definition->setStatus("sent");

                        // optional envelope parameters
                        $options = new \DocuSign\eSign\Api\EnvelopesApi\CreateEnvelopeOptions();
                        $options->setCdseMode(null);
                        $options->setMergeRolesOnDraft(null);
                        $envelop_summary = $envelopeApi->createEnvelope($accountId, $envelop_definition, $options);
                        
                        if(!empty($envelop_summary))
                        {
                            echo "$envelop_summary";
                        }
                    }
                }
            }
        }
        catch (\DocuSign\eSign\ApiException $ex)
        {
            echo "Exception: " . $ex->getMessage() . "\n";
        }
    }


    public function getCustomerAddress($addressId = "") {

        $addressArray = array();    
        if( $addressId ){
            $address = $this->objectManager->create("Magento\Customer\Model\Address");
            $addressData = $address->load($addressId);
            $addressArray['telephone'] = $addressData->getData('telephone');
            $addressArray['company'] = $addressData->getData('company');
            $addressArray['street'] = $addressData->getData('street');
            $addressArray['city'] = $addressData->getData('city');
            $addressArray['region'] = $addressData->getData('region');
            $addressArray['country_id'] = $addressData->getData('country_id');
            $addressArray['postcode'] = $addressData->getData('postcode');
            
            return $addressArray;
        }

    }

    public function getFormatedAddress( $address = array() ) {
        return implode(", ", $address);
    }


}


