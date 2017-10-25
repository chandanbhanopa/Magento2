<?php

namespace MangoIt\DocuSignCustomFields\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;


class CustomField extends AbstractModel {

	public function __construct(
			Context $context,
			Registry $registry,
			AbstractResource $resource = null,
			AbstractDb $resourceCollection = null,
			array $data = []

	) {
		parent::__construct($context, $registry, $resource, $resourceCollection, $data);
	}

	/**
     * @return void
     */
    public function _construct() {
    	
        $this->_init('MangoIt\DocuSignCustomFields\Model\ResourceModel\CustomField');
    }

}