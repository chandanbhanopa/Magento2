$ob = \Magento\Framework\App\ObjectManager::getInstance();

        $option = array();
        $option['attribute_id'] = 145;
        $option['value']['Select account number'][0] = 'Select account number';

        /* @var $attr \Magento\Eav\Model\Entity\Attribute */
        $attr = $ob->create('\Magento\Eav\Model\Entity\Attribute'); 
        $attr->load('account_number','attribute_code');
        $attr->addData(array('option' => $option));
        $attr->save();