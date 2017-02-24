<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Pim\Bundle\CatalogBundle\tests\integration\Completeness\AbstractCompletenessPerAttributeTypeIntegration;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for simple select attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OptionAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeIntegration
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createFamilyWithRequirement(
            'simple_select_family',
            'ecommerce',
            'a_simple_select',
            AttributeTypes::OPTION_SIMPLE_SELECT
        );

        $aSimpleSelect = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('a_simple_select');

        $redOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $redOption->setCode('red_option');
        $redOption->setAttribute($aSimpleSelect);

        $optionSaver = $this->get('pim_catalog.saver.attribute_option');
        $optionSaver->save($redOption);
    }

    public function testOption()
    {
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('simple_select_family');

        $productFull = $this->createProductWithStandardValues(
            $family,
            'product_full',
            [
                'values' => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'red_option',
                        ],
                    ],
                ],
            ]
        );

        $this->assertComplete($productFull);
    }

    public function testEmptyOption()
    {
        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('simple_select_family');

        $productNull = $this->createProductWithStandardValues(
            $family,
            'product_null',
            [
                'values' => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => null,
                        ],
                    ],
                ],
            ]
        );

        $this->assertNotComplete($productNull);
    }
}
