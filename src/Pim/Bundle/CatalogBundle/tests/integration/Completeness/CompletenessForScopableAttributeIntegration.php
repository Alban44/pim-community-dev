<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Checks that the completeness has been well calculated for scopable attribute types.
 *
 * We test from the minimal catalog that contains only one channel, with one locale activated.
 *
 * For each test, the we create a family where the scopable attribute is required.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessForScopableAttributeIntegration extends AbstractCompletenessIntegration
{
    public function testScopableComplete()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT,
            false,
            true
        );

        $product = $this->createProductWithStandardValues(
            $family,
            'another_product',
            [
                'values' => [
                    'a_text' => [
                        [
                            'locale' => null,
                            'scope'  => 'ecommerce',
                            'data'   => 'just a text'
                        ],
                    ]
                ]
            ]
        );

        $this->assertComplete($product, 'ecommerce');
    }

    public function testScopableNotComplete()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT,
            false,
            true
        );


        $productNoValue = $this->createProductWithStandardValues($family, 'product_no_value');

        $productValueEmpty = $this->createProductWithStandardValues(
            $family,
            'product_value_empty',
            [
                'values' => [
                    'a_text' => [
                        [
                            'locale' => null,
                            'scope'  => 'ecommerce',
                            'data'   => null
                        ],
                    ]
                ]
            ]
        );

        $this->assertNotComplete($productNoValue, 'ecommerce');
        $this->assertNotComplete($productValueEmpty, 'ecommerce');
    }

    /**
     * @param ProductInterface $product
     * @param string           $channelCode
     */
    private function assertNotComplete(ProductInterface $product, $channelCode)
    {
        $completenesses = $product->getCompletenesses()->toArray();
        $this->assertNotNull($completenesses);
        $this->assertCount(1, $completenesses);

        $completeness = current($completenesses);
        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals('en_US', $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals($channelCode, $completeness->getChannel()->getCode());
        $this->assertEquals(50, $completeness->getRatio());
        $this->assertEquals(2, $completeness->getRequiredCount());
        $this->assertEquals(1, $completeness->getMissingCount());
    }

    /**
     * @param ProductInterface $product
     * @param string           $channelCode
     */
    private function assertComplete(ProductInterface $product, $channelCode)
    {
        $completenesses = $product->getCompletenesses()->toArray();
        $this->assertNotNull($completenesses);
        $this->assertCount(1, $completenesses);

        $completeness = current($completenesses);
        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals('en_US', $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals($channelCode, $completeness->getChannel()->getCode());
        $this->assertEquals(100, $completeness->getRatio());
        $this->assertEquals(2, $completeness->getRequiredCount());
        $this->assertEquals(0, $completeness->getMissingCount());
    }
}
