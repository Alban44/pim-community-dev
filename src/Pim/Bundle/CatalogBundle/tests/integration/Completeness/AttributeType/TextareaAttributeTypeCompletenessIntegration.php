<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Pim\Bundle\CatalogBundle\tests\integration\Completeness\AbstractCompletenessPerAttributeTypeIntegration;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for textarea attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TextareaAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeIntegration
{
    public function testTextarea()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text_area',
            AttributeTypes::TEXTAREA
        );

        $productFull = $this->createProductWithStandardValues(
            $family,
            'product_full',
            [
                'values' => [
                    'a_text_area' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'foo bar',
                        ],
                    ],
                ],
            ]
        );

        $this->assertComplete($productFull);
    }

    public function testEmptyTextarea()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text_area',
            AttributeTypes::TEXTAREA
        );

        $productNull = $this->createProductWithStandardValues(
            $family,
            'product_null',
            [
                'values' => [
                    'a_text_area' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => null,
                        ],
                    ],
                ],
            ]
        );

        $productEmpty = $this->createProductWithStandardValues(
            $family,
            'product_empty',
            [
                'values' => [
                    'a_text_area' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => '',
                        ],
                    ],
                ],
            ]
        );

        $productWithoutValue = $this->createProductWithStandardValues($family, 'product_without_values');

        $this->assertNotComplete($productNull);
        $this->assertNotComplete($productEmpty);
        $this->assertNotComplete($productWithoutValue);
    }
}
