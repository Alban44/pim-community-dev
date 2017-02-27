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
    public function testCompletePriceCollection()
    {
        $this->addCurrencyToChannel('EUR', 'ecommerce');

        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_price_collection',
            AttributeTypes::PRICE_COLLECTION
        );

        $productComplete = $this->createProductWithStandardValues(
            $family,
            'product_complete',
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
                            ],
                        ],
                    ]
                ],
            ]
        );
        $this->assertComplete($productComplete);

        $productAmountsZero = $this->createProductWithStandardValues(
            $family,
            'product_complete',
            [
                'values' => [
                    'a_price_collection' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                [
                                    'amount'   => 0,
                                    'currency' => 'USD',
                                ],
                                [
                                    'amount'   => 0,
                                    'currency' => 'EUR',
                                ],
                            ],
                        ],
                    ]
                ],
            ]
        );
        $this->assertComplete($productAmountsZero);
    }

    public function testNotCompletePriceCollection()
    {
        $this->addCurrencyToChannel('EUR', 'ecommerce');

        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_price_collection',
            AttributeTypes::PRICE_COLLECTION
        );

        $productWithoutValues = $this->createProductWithStandardValues($family, 'product_without_values');
        $this->assertNotComplete($productWithoutValues);

        $productAmountsNull = $this->createProductWithStandardValues(
            $family,
            'product_amounts_null',
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
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->assertNotComplete($productAmountsNull);

        $productAmountNull = $this->createProductWithStandardValues(
            $family,
            'product_amount_null',
            [
                'values' => [
                    'a_price_collection' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                [
                                    'amount'   => 7,
                                    'currency' => 'USD',
                                ],
                                [
                                    'amount'   => null,
                                    'currency' => 'EUR',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->assertNotComplete($productAmountNull);

        $productCurrencyEmptyString = $this->createProductWithStandardValues(
            $family,
            'product_currency_empty_string',
            [
                'values' => [
                    'a_price_collection' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                [
                                    'amount'   => 7,
                                    'currency' => 'USD',
                                ],
                                [
                                    'amount'   => 87,
                                    'currency' => '',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->assertNotComplete($productCurrencyEmptyString);

        $productMissingPrice = $this->createProductWithStandardValues(
            $family,
            'product_missing_price',
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
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->assertNotComplete($productMissingPrice);
    }

    /**
     * @param string $currencyCode
     * @param string $channelCode
     */
    private function addCurrencyToChannel($currencyCode, $channelCode)
    {
        $euro = $this->get('pim_catalog.repository.currency')->findOneByIdentifier($currencyCode);
        $ecommerce = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($channelCode);
        $ecommerce->addCurrency($euro);
        $this->get('pim_catalog.saver.channel')->save($ecommerce);
    }
}
