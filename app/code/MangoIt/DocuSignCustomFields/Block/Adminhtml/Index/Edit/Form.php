<?php

namespace MangoIt\DocuSignCustomFields\Block\Adminhtml\Index\Edit;

use MangoIt\DocuSignCustomFields\Helper\Data;
/**
 * Class Form
 * @package MangoIt\DocuSignCustomFields\Block\Adminhtml\Post\Edit
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic  {
    /**
     * Prepare form
     *
     * @return $this
     */
    private $om;
    protected function _prepareForm() {


      $this->om = \Magento\Framework\App\ObjectManager::getInstance();
      $helper = $this->om->get('MangoIt\DocuSignCustomFields\Helper\Data');
      $apiData = $helper->getCustiomFields();

      /** @var \Magento\Framework\Data\Form $form */
      $form = $this->_formFactory->create(
        [
          'data' => [
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
          ]
        ]
      );

      $fieldset = $form->addFieldset(
        'base_fieldset',
        [
          'legend' => __('DocuSIGN Fields'),
          'class'  => 'fieldset-wide'
        ]
      );

      $fieldsData  =  $this->loadDbData();
      //die;
      foreach($apiData as $fields){
        $fieldset->addField(
          $fields['id'],
          'text',
          [
            'name'  => $fields['label'],
            'label' => $fields['label'],
            'required' => true,
            'note' => $fields['label'],
            'value'=> isset($fieldsData[$fields['label']]) ? $fieldsData[$fields['label']] : ""
          ]
        ); 
      }

      $form->setUseContainer(true);
      $this->setForm($form);
      return parent::_prepareForm();
    }

    private function loadDbData(){
      $CustomFields = $this->om->create('MangoIt\DocuSignCustomFields\Model\CustomField');
      $data = $CustomFields->getCollection()->getData();
      $finalArray = array();
      if(count($data) > 0) {
        $resultData = json_decode($data[0]['docusing_data'], true);
        if($resultData){
          foreach($resultData as $value) {
            $finalArray[$value['docusing_label']] = $value['docusing_map_value'];
          }
          
        }
      }

      return $finalArray;



    }
  }
