<?php
namespace MangoIt\Checkoutfield\Setup;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use \Magento\Framework\Exception\AlreadyExistsException;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var \Magento\Catalog\Model\Product\AttributeSet\BuildFactory
     */
    protected $buildFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime $dateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\App\ResourceConnection $connection
     * @todo delete from here
     */
    protected $connection;

    /**
     * @var \Magento\Framework\Translate $translate
     */
    protected $translate;
    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $_attrOptionCollectionFactory;
    /**
     * @var \Magento\Eav\Model\AttributeManagement
     */
    private $attributeManagement;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    private $groupListFactory;

    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param \Magento\Framework\Registry                                                  $coreRegistry
     * @param \Magento\Catalog\Model\Product\AttributeSet\BuildFactory                     $buildFactory
     * @param \Magento\Customer\Model\AttributeFactory                                     $attributeFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory    $groupCollectionFactory
     * @param \Magento\Framework\Filter\FilterManager                                      $filterManager
     * @param \Magento\Catalog\Helper\Product                                              $productHelper
     * @param \Magento\Eav\Model\AttributeManagement                                       $attributeManagement
     * @param \Magento\Framework\Stdlib\DateTime                                           $dateTime
     * @param \Magento\Framework\App\ResourceConnection                                    $connection
     * @param \Magento\Framework\Translate                                                 $translate
     * @param GroupRepositoryInterface                                                     $groupRepository
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory   $attrOptionCollectionFactory
     * @param SearchCriteriaBuilder                                                        $searchCriteriaBuilder
     * @param \Magento\Eav\Model\Config                                                    $eavConfig
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory    $groupListFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Product\AttributeSet\BuildFactory $buildFactory,
        \Magento\Customer\Model\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Eav\Model\AttributeManagement $attributeManagement,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\App\ResourceConnection $connection,
        \Magento\Framework\Translate $translate,
        GroupRepositoryInterface $groupRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupListFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->translate = $translate;
        $this->connection = $connection;
        $this->buildFactory = $buildFactory;
        $this->filterManager = $filterManager;
        $this->productHelper = $productHelper;
        $this->attributeFactory = $attributeFactory;
        $this->validatorFactory = $validatorFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->dateTime = $dateTime;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->attributeManagement = $attributeManagement;
        $this->eavConfig = $eavConfig;
        $this->groupListFactory = $groupListFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $setup->startSetup();
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $this->_entityTypeId = $_objectManager->create(
            'Magento\Eav\Model\Entity'
        )->setType(
            \Magento\Customer\Model\Customer::ENTITY
        )->getTypeId();
       
        $data = array(
            'frontend_label' => array(
                '0' => 'Account Number',
            ),
            'attribute_code' => 'account_number',
            'stores' => array(
                '0' => 1,
            ),
            'frontend_input' => 'select',
            'default_value_text' => '',
            'default_value_yesno' => 0,
            'default_value_date' => '',
            'default_value_textarea' => '',
            'required_on_front' => 0,
            'frontend_class' => '',
            'used_in_order_grid' => 1,
            'on_order_view' => 1,
            'is_visible_on_front' => 1,
            'account_filled' => 0,
            'used_in_product_listing' => 1,
            'billing_filled' => 0,
            'on_registration' => 0,
            'sorting_order' => '12',
            /*'option' => array(
                'order' => array(
                    'option_0' => 1,
                    'option_1' => 2,
                ),
                'value' => array(
                    'option_0' => array(
                        '0' => '12354'
                    ),
                    'option_1' => array(
                        '0' => '12345'
                    )
                )
            ),*/
            'dropdown_attribute_validation' => ''
        );
        $id = null;
        if (!$data) {
            return;
        }
        $addToSet = false;
        /** @var $model \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
        $model = $this->attributeFactory->create();
    
        if ($model->getIsUserDefined() === null || $model->getIsUserDefined() != 0) {
            $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
        }

        $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);

        if (!$defaultValueField && 'statictext' == $data['frontend_input']) {
            $defaultValueField = 'default_value_textarea';
        }
        if ($defaultValueField) {
            $data['default_value'] = $data[$defaultValueField];
        }

        //$data['required_on_front'] = 0;
        
        if ($model->getIsUserDefined() === null || $model->getIsUserDefined() != 0) {
            $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
        }

        if (!isset($data['apply_to'])) {
            $data['apply_to'] = [];
        }

        $data = $this->setSourceModel($data);

        if (!empty($data['customer_groups'])) {
            $data['customer_groups'] = implode(',', $data['customer_groups']);
        } else {
            $data['customer_groups'] = '';
        }

        $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
        if ($defaultValueField) {
            $data['default_value'] = $data[$defaultValueField];
        }

        $data['store_ids'] = '';
        $data['sort_order'] = $data['sorting_order'] + 1000;//move attributes to the bottom

        if ($data['stores']) {
            if (is_array($data['stores'])) {
                $data['store_ids'] = implode(',', $data['stores']);
            } else {
                $data['store_ids'] = $data['stores'];
            }
            unset($data['stores']);
        }

        

        $model->addData($data);
        $isNewCustomerGroupOptions = $this->_addOptionsForCustomerGroupAttribute($model);
        if (!$id) {
            $model->setEntityTypeId($this->_entityTypeId);
            $model->setIsUserDefined(1);
            $addToSet = true;
        }

        $usedInForms = $this->getUsedFroms($model);
        $model->setData('used_in_forms', $usedInForms);
        try {
            $model->save();
            if (('multiselectimg' === $data['frontend_input'] || 'selectimg' === $data['frontend_input'])
                && array_key_exists('default', $data)
                && is_array($data['default'])
            ) {
                $this->_saveDefaultValue($model, $data['default']);
            }
            if ($isNewCustomerGroupOptions) {
                $this->_saveCustomerGroupIds($model);
            }

            if ($addToSet) {
                $attributeSetId = $this->eavConfig->getEntityType('customer')
                    ->getDefaultAttributeSetId();
                /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection $collection */
                $collection = $this->groupListFactory->create();
                $collection->setAttributeSetFilter($attributeSetId);
                $collection->addFilter('attribute_group_code', 'general');

                $this->attributeManagement->assign(
                    'customer',
                    $attributeSetId,
                    $collection->getFirstItem()->getId(),
                    $model->getAttributeCode(),
                    null
                );
            }
        } catch (Exception $e) {
            throw new Exception("Error Processing Request".$e->getMessage(), 1);
        }
        $setup->endSetup();
    }

    protected function _addOptionsForCustomerGroupAttribute(&$model){
        $data = $model->getData();
        if(( (array_key_exists('type_internal', $data) && $data['type_internal'] == 'selectgroup')
            || (array_key_exists('frontend_input', $data) && $data['frontend_input'] == 'selectgroup')
            )
            && !array_key_exists('option', $data)
        ) {
            $values = [
                'order' => [],
                'value' => []
            ];
            $customerGroups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            $i = 0;
            foreach ($customerGroups as $item) {
                $name = 'option_' . $i++;
                $values['value'][$name] = [
                    0 => $item->getCode()
                ];
                $values['order'][$name] = $item->getId();
                $values['group_id'][$name] = $item->getId();
            }
            array_shift($values['value']);
            array_shift($values['order']);
            array_shift($values['group_id']);
            $data['option'] = $values;
            $model->setData($data);

            return true;
        }
        return false;
    }

    protected function getUsedFroms($attribute){
        $usedInForms = [
            'adminhtml_customer',
        ];
        if($attribute->getIsVisibleOnFront() == '1'){
            $usedInForms[] = 'customer_account_edit';
        }
        if($attribute->getOnRegistration() == '1'){
            $usedInForms[] = 'customer_account_create';
            $usedInForms[] = 'customer_attributes_registration';
        }
        if($attribute->getUsedInProductListing()){
            $usedInForms[] = 'adminhtml_checkout';
            $usedInForms[] = 'customer_attributes_checkout';

        }
        return $usedInForms;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function setSourceModel($data)
    {
        if (array_key_exists('type_internal', $data)
            && $data['type_internal'] == 'selectgroup') {
            $data['frontend_input'] = 'selectgroup';
        }
        switch ($data['frontend_input']) {
            case 'boolean':
                $data['source_model']
                    = 'Magento\Eav\Model\Entity\Attribute\Source\Boolean';
                break;
            case 'multiselectimg':
            
            case 'select':
            case 'checkboxes':
            case 'multiselect':
            case 'radios':
                $data['source_model']
                    = 'Magento\Eav\Model\Entity\Attribute\Source\Table';
                $data['backend_model']
                    = 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend';
                break;
            case 'file':
                $data['type_internal'] = 'file';
                $data['backend_type'] = 'varchar';
                break;
            case 'selectgroup':
                $data['type_internal'] = 'selectgroup';
                $data['frontend_input']= 'select';
                $data['source_model']
                    = 'Magento\Eav\Model\Entity\Attribute\Source\Table';
                $data['backend_model']
                    = 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend';
                //$data['backend_type'] = 'varchar';
                break;
        }

        return $data;
    }

    protected function _saveDefaultValue($object, $defaultValue)
    {
        if ($defaultValue !== null) {
            $bind = ['default_value' => implode(',', $defaultValue)];
            $where = ['attribute_id = ?' => $object->getId()];
            $this->connection->getConnection()->update($this->connection->getTableName('eav_attribute'), $bind, $where);
        }
    }

    protected function _saveCustomerGroupIds($model)
    {
        $data = $model->getData();
        if($data['type_internal'] == 'selectgroup'
                || $data['frontend_input'] == 'selectgroup'
        ) {
            $options = $this->_attrOptionCollectionFactory->create()->setAttributeFilter(
                $model->getId()
            )->setPositionOrder(
                'asc',
                true
            )->load();

            $customerGroups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            $i = 1;
            foreach($options as $option) {
                if(array_key_exists($i, $customerGroups)) {
                    $group = $customerGroups[$i++];
                    if($group->getCode() == $option->getValue()) {
                        $option->setGroupId($group->getId());
                        $option->save();
                    }
                }
            }
        }
    }
}