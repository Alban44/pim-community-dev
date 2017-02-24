<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Pim\Bundle\CatalogBundle\tests\integration\Completeness\AbstractCompletenessPerAttributeTypeIntegration;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for price collection attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PriceCollectionAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeIntegration
{
    public function testPriceCollection()
    {
        $euro = $this->get('pim_catalog.repository.currency')->findOneByIdentifier('EUR');
        $ecommerce = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        $ecommerce->addCurrency($euro);
        $this->get('pim_catalog.saver.channel')->save($ecommerce);

        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_price_collection',
            AttributeTypes::PRICE_COLLECTION
        );

        $productFull = $this->createProductWithStandardValues(
            $family,
            'product_full',
            [
                'values' => [
                    'a_price_collection' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                [
                                    'amount'   => 42,
                                    'currency' => 'USD',
                                ],
                                [
                                    'amount'   => 69,
                                    'currency' => 'EUR',
                                ],
                            ]
                        ],
                    ]
                ]
            ]
        );

        $productEmptyNoCurrency = $this->createProductWithStandardValues($family, 'product_empty_no_currency');

        $productEmptyCurrencies = $this->createProductWithStandardValues(
            $family,
            'product_empty_currencies',
            [
                'values' => [
                    'a_price_collection' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                [
                                    'amount'   => null,
                                    'currency' => 'USD',
                                ],
                                [
                                    'amount'   => null,
                                    'currency' => 'EUR',
                                ],
                            ]
                        ],
                    ]
                ]
            ]
        );

        $productEmptyMissingCurrency = $this->createProductWithStandardValues(
            $family,
            'product_empty_missing_currency',
            [
                'values' => [
                    'a_price_collection' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                [
                                    'amount'   => 67,
                                    'currency' => 'USD',
                                ],
                            ]
                        ],
                    ]
                ]
            ]
        );

        $this->assertComplete($productFull);
        $this->assertNotComplete($productEmptyNoCurrency);
        $this->assertNotComplete($productEmptyCurrencies);
        $this->assertNotComplete($productEmptyMissingCurrency);
    }
}
