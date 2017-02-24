<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Pim\Bundle\CatalogBundle\tests\integration\Completeness\AbstractCompletenessPerAttributeTypeIntegration;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for metric attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MetricAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeIntegration
{
    public function testMetric()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_metric',
            AttributeTypes::METRIC
        );

        $this->configureMetricFamilyForAttribute('a_metric', 'Length');

        $productFull = $this->createProductWithStandardValues(
            $family,
            'product_full',
            [
                'values' => [
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => ['amount' => 12, 'unit' => 'METER'],
                        ],
                    ],
                ],
            ]
        );
        $this->assertComplete($productFull);

        $productZeroAmount = $this->createProductWithStandardValues(
            $family,
            'product_full',
            [
                'values' => [
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => ['amount' => 0, 'unit' => 'METER'],
                        ],
                    ],
                ],
            ]
        );
        $this->assertComplete($productZeroAmount);
    }

    public function testIncompleteMetric()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_metric',
            AttributeTypes::METRIC
        );

        $this->configureMetricFamilyForAttribute('a_metric', 'Length');

        $productNullData = $this->createProductWithStandardValues(
            $family,
            'product_full',
            [
                'values' => [
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => null,
                        ],
                    ],
                ],
            ]
        );
        $this->assertNotComplete($productNullData);

        $productNullAmount = $this->createProductWithStandardValues(
            $family,
            'product_full',
            [
                'values' => [
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => ['amount' => null, 'unit' => 'METER'],
                        ],
                    ],
                ],
            ]
        );
        $this->assertNotComplete($productNullAmount);

        $productNullAmountAndUnit = $this->createProductWithStandardValues(
            $family,
            'product_full',
            [
                'values' => [
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => ['amount' => null, 'unit' => null],
                        ],
                    ],
                ],
            ]
        );
        $this->assertNotComplete($productNullAmountAndUnit);
    }

    private function configureMetricFamilyForAttribute($code, $metricFamily)
    {
        $metric = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($code);
        $metric->setMetricFamily($metricFamily);
        $this->get('pim_catalog.saver.attribute')->save($metric);
    }
}
