<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness;

use Akeneo\Test\Integration\Configuration;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Checks that the completeness has been well calculated for localisable and locale specific attribute types.
 *
 * We test from the minimal catalog that contains only 1 channel. The locales fr_FR and en_US are activated.
 *
 * The completeness calculation is tested for:
 *      - 1 localisable attribute
 *      - 1 locale specific attribute
 *
 * For each test, the we create a family where the attribute is required.
 * Then, we create two products of this family, one with the required attribute filled in, the other without.
 * Finally we test the completeness calculation of those two products.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessForLocalisableAttributeIntegration extends AbstractCompletenessIntegration
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $fr = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('fr_FR');
        $ecommerce = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        $ecommerce->addLocale($fr);
        $this->get('pim_catalog.saver.channel')->save($ecommerce);
    }

    public function testLocalisable()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT,
            true
        );

        $product = $this->createProductWithStandardValues(
            $family,
            'another_product',
            [
                'values' => [
                    'a_text' => [
                        [
                            'locale' => 'en_US',
                            'scope'  => null,
                            'data'   => 'just a text'
                        ],
                        [
                            'locale' => 'fr_FR',
                            'scope'  => null,
                            'data'   => null
                        ],
                    ]
                ]
            ]
        );

        $this->assertComplete($product, 'en_US', 2);
        $this->assertNotComplete($product, 'fr_FR', 2);
    }

    public function testLocaleSpecificNoLocale()
    {
        $fr = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('fr_FR');

        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT,
            true,
            false,
            [$fr]
        );

        $productEmptyNoLocale = $this->createProductWithStandardValues($family, 'product_empty_no_locale');
        $this->assertNotComplete($productEmptyNoLocale, 'fr_FR', 2);
        $this->assertComplete($productEmptyNoLocale, 'en_US', 1);
    }

    public function testLocaleSpecificLocaleEmpty()
    {
        $fr = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('fr_FR');

        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT,
            true,
            false,
            [$fr]
        );

        $productEmptyLocaleEmpty = $this->createProductWithStandardValues(
            $family,
            'product_empty_locale_empty',
            [
                'values' => [
                    'a_text' => [
                        [
                            'locale' => 'fr_FR',
                            'scope'  => null,
                            'data'   => null
                        ],
                    ]
                ]
            ]
        );
        $this->assertNotComplete($productEmptyLocaleEmpty, 'fr_FR', 2);
        $this->assertComplete($productEmptyLocaleEmpty, 'en_US', 1);
    }

    public function testLocaleSpecificComplete()
    {
        $fr = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('fr_FR');

        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT,
            true,
            false,
            [$fr]
        );

        $productFull = $this->createProductWithStandardValues(
            $family,
            'product_full',
            [
                'values' => [
                    'a_text' => [
                        [
                            'locale' => 'fr_FR',
                            'scope'  => null,
                            'data'   => 'juste un texte'
                        ],
                    ]
                ]
            ]
        );
        $this->assertComplete($productFull, 'fr_FR', 2);
        $this->assertComplete($productFull, 'en_US', 1);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getMinimalCatalogPath()],
            true
        );
    }

    /**
     * @param ProductInterface $product
     * @param string           $localeCode
     *
     * @throws \Exception
     * @return CompletenessInterface
     */
    private function getCompletenessByLocaleCode(ProductInterface $product, $localeCode)
    {
        $completenesses = $product->getCompletenesses()->toArray();

        foreach ($completenesses as $completeness) {
            if ($localeCode === $completeness->getLocale()->getCode()) {
                return $completeness;
            }
        }

        throw new \Exception(sprintf('No completeness for the locale "%s"', $localeCode));
    }

    /**
     * @param ProductInterface $product
     * @param string           $localeCode
     * @param int              $requiredCount
     */
    private function assertNotComplete(ProductInterface $product, $localeCode, $requiredCount)
    {
        $completenesses = $product->getCompletenesses()->toArray();
        $this->assertNotNull($completenesses);
        $this->assertCount(2, $completenesses);

        $completeness = $this->getCompletenessByLocaleCode($product, $localeCode);
        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals($localeCode, $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals('ecommerce', $completeness->getChannel()->getCode());
        $this->assertEquals(50, $completeness->getRatio());
        $this->assertEquals($requiredCount, $completeness->getRequiredCount());
        $this->assertEquals(1, $completeness->getMissingCount());
    }

    /**
     * @param ProductInterface $product
     * @param string           $localeCode
     * @param int              $requiredCount
     */
    private function assertComplete(ProductInterface $product, $localeCode, $requiredCount)
    {
        $completenesses = $product->getCompletenesses()->toArray();
        $this->assertNotNull($completenesses);
        $this->assertCount(2, $completenesses);

        $completeness = $this->getCompletenessByLocaleCode($product, $localeCode);
        $this->assertNotNull($completeness->getLocale());
        $this->assertEquals($localeCode, $completeness->getLocale()->getCode());
        $this->assertNotNull($completeness->getChannel());
        $this->assertEquals('ecommerce', $completeness->getChannel()->getCode());
        $this->assertEquals(100, $completeness->getRatio());
        $this->assertEquals($requiredCount, $completeness->getRequiredCount());
        $this->assertEquals(0, $completeness->getMissingCount());
    }
}