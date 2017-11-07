<?php

namespace MangoIt\DocuSignCustomFields\Controller\Index;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action {

	private $objectManager;
	private $helper;
	private $scopeConfigInterface;


	private $config = array();

	private $apiSuffix = "/v2/accounts/";

	private $apiBaseUrl;

	public function __construct(Context $context) {

		parent :: __construct($context);

		$this->objectManager = ObjectManager::getInstance();
		$this->helper = $this->objectManager->get('\MangoIt\DocuSignCustomFields\Helper\Data');
		$this->scopeConfigInterface = 'Magento\Framework\App\Config\ScopeConfigInterface';
        $this->scopeConfig = $this->objectManager->get($this->scopeConfigInterface);


        $this->config['live_mode'] = $this->scopeConfig->getValue('docusing_settings/api_settings/sandbox_mode');
        $this->config['sandbox_endpoint'] = $this->scopeConfig->getValue('docusing_settings/api_settings/api_sandbox_hostname');
        $this->config['live_endpoint'] = $this->scopeConfig->getValue('docusing_settings/api_settings/api_live_hostname');

        $this->config['api_username'] = $this->scopeConfig->getValue('docusing_settings/api_settings/api_user_name');
        $this->config['api_password'] = $this->scopeConfig->getValue('docusing_settings/api_settings/api_password');
        $this->config['integrator_key'] = $this->scopeConfig->getValue('docusing_settings/api_settings/api_integrator_key');

        $this->config['send_attachment'] = $this->scopeConfig->getValue('docusing_settings/mangoit_template_settings/send_attachment');


        $this->config['account_id'] = $this->scopeConfig->getValue('docusing_settings/api_settings/account_id');

        $this->config['template_id'] = $this->scopeConfig->getValue('docusing_settings/mangoit_template_settings/template_id');
        $this->config['subject'] = $this->scopeConfig->getValue('docusing_settings/mangoit_template_settings/subject');

        $this->apiBaseUrl = $this->config['sandbox_endpoint'].$this->apiSuffix.$this->config['account_id'];

	}

	public function execute() {

		$this->helper->createEnvelop($this->config, $this->apiBaseUrl);

	}

}