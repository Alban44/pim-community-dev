<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Pim\Bundle\CatalogBundle\tests\integration\Completeness\AbstractCompletenessPerAttributeTypeIntegration;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for image attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ImageAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeIntegration
{
    public function testImage()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'an_image',
            AttributeTypes::IMAGE
        );

        $productFull = $this->createProductWithStandardValues(
            $family,
            'product_full',
            [
                'values' => [
                    'an_image' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => $this->getFixturePath('akeneo.jpg'),
                        ],
                    ],
                ],
            ]
        );

        $this->assertComplete($productFull);
    }

    public function testEmptyImage()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'an_image',
            AttributeTypes::IMAGE
        );

        $productEmpty = $this->createProductWithStandardValues(
            $family,
            'product_empty',
            [
                'values' => [
                    'an_image' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => null,
                        ],
                    ],
                ],
            ]
        );

        $productWithoutValues = $this->createProductWithStandardValues($family, 'product_without_values');

        $this->assertNotComplete($productEmpty);
        $this->assertNotComplete($productWithoutValues);
    }
}
