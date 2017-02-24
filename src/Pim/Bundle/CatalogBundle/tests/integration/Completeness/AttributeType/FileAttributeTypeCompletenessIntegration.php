<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Pim\Bundle\CatalogBundle\tests\integration\Completeness\AbstractCompletenessPerAttributeTypeIntegration;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for file attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FileAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeIntegration
{
    public function testFile()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_file',
            AttributeTypes::FILE
        );

        $productFull = $this->createProductWithStandardValues(
            $family,
            'product_full',
            [
                'values' => [
                    'a_file' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => $this->getFixturePath('akeneo.txt'),
                        ],
                    ],
                ],
            ]
        );

        $productEmpty = $this->createProductWithStandardValues($family, 'product_empty');

        $this->assertComplete($productFull);
        $this->assertNotComplete($productEmpty);
    }
}
