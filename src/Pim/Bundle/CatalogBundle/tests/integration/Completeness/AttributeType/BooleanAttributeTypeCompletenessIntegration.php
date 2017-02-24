<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Pim\Bundle\CatalogBundle\tests\integration\Completeness\AbstractCompletenessPerAttributeTypeIntegration;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Checks that the completeness has been well calculated for boolean attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BooleanAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeIntegration
{
    public function testBoolean()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_boolean',
            AttributeTypes::BOOLEAN
        );

        $productFull = $this->createProductWithStandardValues(
            $family,
            'product_full',
            [
                'values' => [
                    'a_boolean' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => true,
                        ],
                    ],
                ],
            ]
        );

        $productEmpty = $this->createProductWithStandardValues($family, 'product_empty');

        $this->assertComplete($productFull);

        // TODO: This is not as it should be, but inevitable because of PIM-6056
        // TODO: When PIM-6056 is fixed, we should be able to use "assertNotComplete"
        $this->assertComplete($productEmpty);
        $this->assertBooleanValueIsFalse($productEmpty, 'a_boolean', null, null);
    }

    /**
     * For now, when creating an empty boolean product value, it is automatically
     * set to false by the product builder.
     *
     * @todo To remove once PIM-6056 is fixed.
     *
     * @param ProductInterface $product
     * @param string           $attributeCode
     * @param string|null      $channelCode
     * @param string|null      $localeCode
     */
    private function assertBooleanValueIsFalse(ProductInterface $product, $attributeCode, $channelCode, $localeCode)
    {
        $booleanValue = $product->getValue($attributeCode, $channelCode, $localeCode);
        $this->assertFalse($booleanValue->getData());
    }
}
