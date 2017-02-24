<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\CatalogBundle\tests\integration\Completeness\AbstractCompletenessPerAttributeTypeIntegration;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for simple reference data attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataSimpleAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeIntegration
{
    public function testSimpleSelectReferenceData()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_simple_select_reference_data',
            AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT
        );

        $productFull = $this->createProductWithStandardValues(
            $family,
            'product_full',
            [
                'values' => [
                    'a_simple_select_reference_data' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'zomp',
                        ],
                    ],
                ],
            ]
        );


        $this->assertComplete($productFull);
    }

    public function testEmptySimpleSelectReferenceData()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_simple_select_reference_data',
            AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT
        );

        $productNull = $this->createProductWithStandardValues(
            $family,
            'product_empty',
            [
                'values' => [
                    'a_simple_select_reference_data' => [
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
                    'a_simple_select_reference_data' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => '',
                        ],
                    ],
                ],
            ]
        );

        $productWithoutValues = $this->createProductWithStandardValues($family, 'product_without_values');

        $this->assertNotComplete($productNull);
        $this->assertNotComplete($productEmpty);
        $this->assertNotComplete($productWithoutValues);
    }

    /**
     * {@inheritdoc}
     */
    protected function createAttribute($code, $type)
    {
        $attribute = parent::createAttribute($code, $type);

        $attribute->setReferenceDataName('color');
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getMinimalCatalogPath(), Configuration::getReferenceDataFixtures()],
            true
        );
    }
}
