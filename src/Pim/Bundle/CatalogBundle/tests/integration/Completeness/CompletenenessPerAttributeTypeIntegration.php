<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for each attribute type
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenenessPerAttributeTypeIntegration extends TestCase
{

    public function testNumber()
    {
        $familyFactory = $this->get('pim_catalog.factory.family');
        $familySaver = $this->get('pim_catalog.saver.family');
        $attributeRequirementFactory = $this->get('pim_catalog.factory.attribute_requirement');
        $channelRepository = $this->get('pim_catalog.repository.channel');
        $productBuilder = $this->get('pim_catalog.builder.product');
        $productUpdater = $this->get('pim_catalog.updater.product');
        $productSaver = $this->get('pim_catalog.saver.product');
        $attributeFactory = $this->get('pim_catalog.factory.attribute');
        $attributeSaver = $this->get('pim_catalog.saver.attribute');

        $attribute = $attributeFactory->createAttribute(AttributeTypes::NUMBER);
        $attribute->setCode('a_number_integer');
        $attributeSaver->save($attribute);

        $channel = $channelRepository->findOneByIdentifier('ecommerce');

        $family = $familyFactory->create();
        $family->setCode('another_family');

        $attributeRequirement = $attributeRequirementFactory->createAttributeRequirement($attribute, $channel, true);
        $family->addAttribute($attribute);
        $family->addAttributeRequirement($attributeRequirement);
        $familySaver->save($family);

        $productFull = $productBuilder->createProduct('product_full', $family->getCode());
        $productUpdater->update($productFull, [
            'values' => [
                'a_number_integer' => [
                    'locale' => null,
                    'scope' => null,
                    'data' => 10
                ]
            ]
        ]);
        $productSaver->save($productFull);

        $productEmpty = $productBuilder->createProduct('product_empty', $family->getCode());
        $productSaver->save($productEmpty);

        $completenessesProductFull = $productFull->getCompletenesses()->toArray();
        $completenessesProductEmpty = $productEmpty->getCompletenesses()->toArray();

        $this->assertNotNull($completenessesProductFull);
        $this->assertNotNull($completenessesProductEmpty);

        $this->assertCount(1, $completenessesProductFull);
        $this->assertCount(1, $completenessesProductEmpty);

        $completenessFull = current($completenessesProductFull);
        $this->assertNull($completenessFull->getLocale());
        $this->assertNull($completenessFull->getScope());
        $this->assertEquals(100, $completenessFull->getRatio());
        $this->assertEquals(2, $completenessFull->getRequiredCount());
        $this->assertEquals(0, $completenessFull->getMissingCount());
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
