<?php
/**
 * @author MangoIt Team
 * @package MangoIt_Checkoutfield
 */
namespace MangoIt\Checkoutfield\Block\Checkout;

use MangoIt\Checkoutfield\Component\Form\AttributeMapper;
use MangoIt\Checkoutfield\Component\Form\AttributeMerger;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\Session as CustomerSession;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    protected $attributeMetadataDataProvider;

    /**
     * @var \MangoIt\Checkoutfield\Component\Form\AttributeMapper
     */
    protected $attributeMapper;

    /**
     * @var AttributeMerger
     */
    protected $merger;

    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param \MangoIt\Checkoutfield\Component\Form\AttributeMapper $attributeMapper
     * @param AttributeMerger $merger
     */
    public function __construct(
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        AttributeMapper $attributeMapper,
        AttributeMerger $merger,
        CustomerRepository $customerRepository,
        CustomerSession $customerSession
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper = $attributeMapper;
        $this->merger = $merger;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if (!isset($jsLayout['components']['checkout'])) {
            return $jsLayout;
        }

        /** @var \Magento\Eav\Api\Data\AttributeInterface[] $attributes */
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer',
            'customer_attributes_checkout'
        );
        $customerData = [];
        if ($this->customerSession->getCustomerId()) {
            $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
            $customerData = $customer->getCustomAttributes();
        }

        $store = $this->storeManager->getStore()->getId();

        $elements = [];
        foreach ($attributes as $attribute) {
            $stores = $attribute->getStoreIds();
            $stores = explode(',', $stores);

            if (!(
                    !($attribute->getBillingFilled() == '1'
                        && array_key_exists($attribute->getAttributeCode(), $customerData)
                    )
                    || $attribute->getBillingFilled() == '0'
                )) {
                continue;
            }
            $key = $attribute->getAttributeCode();
            $elements[$key] = $this->attributeMapper->map($attribute);
            if (isset($elements[$key]['label'])) {
                $label = $elements[$key]['label'];
                $elements[$key]['label'] = __($label);
                if (isset($customerData[$attribute->getAttributeCode()])) {
                    $elements[$key]['value'] = $customerData[$key]->getValue();
                }
            }
        }
        if (!$elements) {
            return $jsLayout;
        }
        $customer = $this->customerSession->getCustomer();
        if (!$this->customerSession->isLoggedIn() || !$customer->getDefaultShippingAddress()) {
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                      ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
            )) {
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
                ['customer_attributes_renderer'] =
                    [
                        'component' => "customerAttributesCheckoutGuest"
                    ];

                /*$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
                ['customer_attributes_renderer']['children'] = $this->merger->merge(
                    $elements,
                    'checkoutProvider',
                    'shippingAddress.custom_attributes',
                    []
                );*/
            }

        } else {
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                      ['children']['shippingAddress']['children']['before-form']['children']
            )) {
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['before-form']
                ['children']['customer_attributes_renderer'] =
                    [
                        'component' => "customerAttributesCheckout"
                    ];

                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['before-form']
                ['children']['customer_attributes_renderer']['children'] = $this->merger->merge(
                    $elements,
                    'checkoutProvider',
                    'shippingAddress.custom_attributes',
                    []
                );
            }

        }

        return $jsLayout;
    }
}
