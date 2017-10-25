<?php
/**
 * @author MangoIt Team
 * @package MangoIt_Checkoutfield
 */

namespace MangoIt\Checkoutfield\Component\Form;

class AttributeMerger extends \Magento\Checkout\Block\Checkout\AttributeMerger
{
    /**
     * Map form element
     *
     * @var array
     */
    protected $formElementMap = [
        'input'       => 'MangoIt_Checkoutfield/js/form/element/abstract',
        'radios'      => 'MangoIt_Checkoutfield/js/form/element/abstract',
        'select'      => 'MangoIt_Checkoutfield/js/form/element/select',
        'date'        => 'MangoIt_Checkoutfield/js/form/element/date',
        'datetime'    => 'MangoIt_Checkoutfield/js/form/element/date',
        'textarea'    => 'MangoIt_Checkoutfield/js/form/element/textarea',
        'checkboxes'  => 'MangoIt_Checkoutfield/js/form/element/checkboxes',
        'multiselectimg'  => 'MangoIt_Checkoutfield/js/form/element/checkboxes',
        'selectimg'  => 'MangoIt_Checkoutfield/js/form/element/abstract',
        'multiselect' => 'MangoIt_Checkoutfield/js/form/element/multiselect',
    ];

    /**
     * Merge additional address fields for given provider
     *
     * @param array $elements
     * @param string $providerName name of the storage container used by UI component
     * @param string $dataScopePrefix
     * @param array $fields
     * @return array
     */
    public function merge($elements, $providerName, $dataScopePrefix, array $fields = [])
    {
        foreach ($elements as $attributeCode => $attributeConfig) {
            $additionalConfig = isset($attributeConfig['config']) ? $attributeConfig : [];
            if (!$this->isFieldVisible($attributeCode, $attributeConfig, $additionalConfig)) {
                continue;
            }
            $config = $this->getFieldConfig(
                $attributeCode,
                $attributeConfig,
                $additionalConfig,
                $providerName,
                $dataScopePrefix
            );
            $fields[$attributeCode] = $config;
        }
        return $fields;
    }
}
