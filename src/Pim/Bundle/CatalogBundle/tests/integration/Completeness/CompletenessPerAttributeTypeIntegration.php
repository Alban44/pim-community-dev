<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Checks that the completeness has been well calculated for each attribute type of the PIM.
 *
 * We test from the minimal catalog that contains only one channel with one activated locale.
 * For each attribute type, we create an attribute. Then, we create a family where the attribute is required.
 * We create two products of this family, one with the required attribute filled in, the other without.
 * Finally we test the completeness calculation of those two products.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessPerAttributeTypeIntegration extends AbstractCompletenessIntegration
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

    public function testDate()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_date',
            AttributeTypes::DATE
        );

        $productFull = $this->createProductWithStandardValues(
            $family,
            'product_full',
            [
                'values' => [
                    'a_date' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => '2012-08-05',
                        ],
                    ],
                ],
            ]
        );

        $productEmpty = $this->createProductWithStandardValues($family, 'product_empty');

        $this->assertComplete($productFull);
        $this->assertNotComplete($productEmpty);
    }

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

    public function testIdentifier()
    {
        //
    }

    public function testImage()
    {
        //
    }

    public function testMetric()
    {
        //
    }

    public function testNumber()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_number_integer',
            AttributeTypes::NUMBER
        );

        $productFull = $this->createProductWithStandardValues(
            $family,
            'product_full',
            [
                'values' => [
                    'a_number_integer' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 10
                        ],
                    ]
                ]
            ]
        );

        $productEmpty = $this->createProductWithStandardValues($family, 'product_empty');

        $this->assertComplete($productFull);
        $this->assertNotComplete($productEmpty);
    }

    public function testOption()
    {
        //
    }

    public function testOptions()
    {
        //
    }

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

    public function testText()
    {
        //
    }

    public function testTextArea()
    {
        //
    }

    public function testReferenceDataSimple()
    {
        //
    }

    public function testReferenceDataMulti()
    {
        //
    }

    /**
     * Here, the identifier and the attribute should be filled in.
     * Which means, there should be 0 missing, and 2 required.
     *
     * @param ProductInterface $product
     */
    private function assertComplete(ProductInterface $product)
    {
        $completenesses = $product->getCompletenesses()->toArray();
        $this->assertNotNull($completenesses);
        $this->assertCount(1, $completenesses);

        $completeness = current($completenesses);

        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals('en_US', $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals('ecommerce', $completeness->getChannel()->getCode());
        $this->assertEquals(100, $completeness->getRatio());
        $this->assertEquals(2, $completeness->getRequiredCount());
        $this->assertEquals(0, $completeness->getMissingCount());
    }

    /**
     * Here, only the identifier should be filled in.
     * Which means, there should be 1 missing, and 2 required.
     *
     * @param ProductInterface $product
     */
    private function assertNotComplete(ProductInterface $product)
    {
        $completenesses = $product->getCompletenesses()->toArray();
        $this->assertNotNull($completenesses);
        $this->assertCount(1, $completenesses);

        $completeness = current($completenesses);

        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals('en_US', $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals('ecommerce', $completeness->getChannel()->getCode());
        $this->assertEquals(50, $completeness->getRatio());
        $this->assertEquals(2, $completeness->getRequiredCount());
        $this->assertEquals(1, $completeness->getMissingCount());
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
