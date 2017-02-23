<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness;

use Akeneo\Test\Integration\Configuration;
use Pim\Component\Catalog\AttributeTypes;

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
            AttributeTypes::TEXT
        );

        $product = $this->createProductWithStandardValues(
            $family,
            'product_full',
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
    }

    public function testLocaleSpecific()
    {
        $fr = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('fr_FR');

        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT,
            [$fr]
        );

        $productEmptyNoLocale = $this->createProductWithStandardValues($family, 'product_empty_no_locale');

        $productEmptyLocaleEmpty = $this->createProductWithStandardValues(
            $family,
            'product_empty_locale_empty',
            [
                'values' => [
                    'a_text' => [
                        // en_US should throw an error
                        /*
                        [
                            'locale' => 'en_US',
                            'scope'  => null,
                            'data'   => 'just a text'
                        ],
                        */
                        [
                            'locale' => 'fr_FR',
                            'scope'  => null,
                            'data'   => null
                        ],
                    ]
                ]
            ]
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
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getMinimalCatalogPath()],
            true
        );
    }
}
