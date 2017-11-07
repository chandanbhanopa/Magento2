<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
# File Location: vendor/magento/module-checkout/Controller/Onepage/Success.php
namespace Magento\Checkout\Controller\Onepage;

class Success extends \Magento\Checkout\Controller\Onepage
{
    /**
     * Order success action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {




        if( $_SERVER['REMOTE_ADDR'] == "103.231.46.197" ) {

            $this->prepareData();

            $session = $this->getOnepage()->getCheckout();
            echo "<pre>";
            $quoteData = $session->getQuote();
            $data = $quoteData->getData(); /*Quote data */
            
            $quoteItem = $quoteData->getItems(); /* Product in quote*/

            $productArray = array();  

            foreach($quoteItem as $item){

                $_item = $item->getProduct();
                

                $product = $item->getProduct();
                $productType = $item->getProduct()->getTypeId();
                $productId = $item->getProduct()->getId();

                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                
                $productArray[$_item->getId()][] = array(
                    "type" =>$_item->getTypeId(),
                    "sku" => $_item->getSku(),
                    "name" => $_item->getName(),
                    "quantity"=> $_item->getQtyOrdered(),
                    "price" => $_item->getPrice(),
                    "options"=> $options
                ); 

                if(isset($options['attributes_info'])) {

                }
                if(isset($options['options'])) {

                }

                //print_r($options);

                


                
                if($productType == "configurable"){



                } else if($productType == "bundle"){


                } else {


                }





                
            }

            print_r($productArray);
            die;
            //$session->clearQuote();
        } else {

            $session = $this->getOnepage()->getCheckout();
            if (!$this->_objectManager->get('Magento\Checkout\Model\Session\SuccessValidator')->isValid()) {
                return $this->resultRedirectFactory->create()->setPath('checkout/cart');
            }
            //$session->clearQuote();
        //@todo: Refactor it to match CQRS
            $resultPage = $this->resultPageFactory->create();
            $this->_eventManager->dispatch(
                'checkout_onepage_controller_success_action',
                ['order_ids' => [$session->getLastOrderId()]]
            );


            return $resultPage;
        } 

    }

    private function prepareData(){

        $session = $this->getOnepage()->getCheckout();
        $orderId = $session->getLastOrderId();
        $objectManager = Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $orderData = $order->load($orderId)     

        foreach ($order->getAllItems() as $_item) {
                //$productArray[] = $_item->getName(); 
            $productArray[] = array(
                "sku" => $_item->getSku(),
                "name" => $_item->getName(),
                "quantity"=> $_item->getQtyOrdered(),
                "price" => $_item->getPrice()

            );              
        } 
    }
}
