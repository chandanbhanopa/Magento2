<?php

namespace MangoIt\DocuSignCustomFields\Block\Adminhtml\Index;

use \Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\ObjectManager;
use \Magento\Framework\App\Filesystem\DirectoryList;

class Help extends \Magento\Backend\Block\Widget\Container {

    public function __construct(Context $context,array $data = []) {
    	parent::__construct($context, $data);
    }

    public function getHelpImage() {
    	/*Directory Setup*/
        $objectManager = ObjectManager::getInstance();

        $fileSystem = $objectManager->get('\Magento\Framework\Filesystem');

        $mediaPath  =   $fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();

        return $mediaPath;



    }	

}
