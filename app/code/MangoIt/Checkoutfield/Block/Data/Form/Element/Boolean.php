<?php
/**
 * @author MangoIt Team
 * @package MangoIt_Checkoutfield
 */

namespace MangoIt\Checkoutfield\Block\Data\Form\Element;

class Boolean extends \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Boolean
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setValues([
            ['label' => ' ',   'value' => ''],
            ['label' => __('No'),  'value' => '0'],
            ['label' => __('Yes'), 'value' => '1']
        ]);
    }
}


