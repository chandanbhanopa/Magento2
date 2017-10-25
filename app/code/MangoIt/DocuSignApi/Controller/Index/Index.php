<?php

namespace MangoIt\DocuSignApi\Controller\Index;

use Magento\Framework\App\Action\Context;
use \MangoIt\DocuSignApi\Helper\Data;

class Index extends \Magento\Framework\App\Action\Action {

	protected $helper;

	public function __construct(Context $context, Data $helper){
		$this->helper = $helper;
		parent::__construct($context);
	}

	public function execute() {
		
		$this->helper->apiCall();


	}
}