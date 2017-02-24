<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractCompletenessIntegration extends TestCase
{
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

    /**
     * @param string $code
     * @param string $type
     *
     * @return AttributeInterface
     */
    private function createAttribute($code, $type)
    {
        $attributeFactory = $this->get('pim_catalog.factory.attribute');
        $attributeSaver = $this->get('pim_catalog.saver.attribute');

        $attribute = $attributeFactory->createAttribute($type);
        $attribute->setCode($code);
        $attributeSaver->save($attribute);

        return $attribute;
    }

    /**
     * @param FamilyInterface $family
     * @param string          $code
     * @param array           $standardValues
     *
     * @return ProductInterface
     */
    protected function createProductWithStandardValues(FamilyInterface $family, $code, $standardValues = [])
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($code, $family->getCode());
        $this->get('pim_catalog.updater.product')->update($product, $standardValues);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * @param string            $familyCode
     * @param string            $channelCode
     * @param string            $attributeCode
     * @param string            $attributeType
     * @param bool              $localisable
     * @param bool              $scopable
     * @param LocaleInterface[] $localesSpecific
     *
     * @return FamilyInterface
     */
    protected function createFamilyWithRequirement(
        $familyCode,
        $channelCode,
        $attributeCode,
        $attributeType,
        $localisable = false,
        $scopable = false,
        array $localesSpecific = []
    ) {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($channelCode);
        $attribute = $this->createAttribute($attributeCode, $attributeType);
        $attribute->setLocalizable($localisable);
        $attribute->setScopable($scopable);
        foreach ($localesSpecific as $locale) {
            $attribute->addAvailableLocale($locale);
        }

        $requirement = $this->get('pim_catalog.factory.attribute_requirement')
            ->createAttributeRequirement($attribute, $channel, true);

        $family = $this->get('pim_catalog.factory.family')->create();
        $family->setCode($familyCode);
        $family->addAttribute($attribute);
        $family->addAttributeRequirement($requirement);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }
}
