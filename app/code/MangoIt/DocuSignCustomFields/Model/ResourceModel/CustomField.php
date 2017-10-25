<?php
/**
 * Copyright © 2017 Mangoit. All rights reserved.
 */
namespace MangoIt\DocuSignCustomFields\Model\ResourceModel;

/**
 * Product resource
 */
class CustomField extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('docusing_custom_fields', 'id');
    }

  
}
