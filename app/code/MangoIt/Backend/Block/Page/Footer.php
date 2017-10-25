<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MangoIt\Backend\Block\Page;

/**
 * Adminhtml footer block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Footer extends \Magento\Backend\Block\Page\Footer
{
    /**
     * @var string
     */
    protected $_template = 'MangoIt_Backend::page/footer.phtml';

    /**
     * @return void
     */
    protected function _construct()
    {
      
        $this->setShowProfiler(true);
    }
}
