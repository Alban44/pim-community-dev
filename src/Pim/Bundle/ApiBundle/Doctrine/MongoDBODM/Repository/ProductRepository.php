<?php

namespace Pim\Bundle\ApiBundle\Doctrine\MongoDBODM\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Pim\Component\Api\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends DocumentRepository implements ProductRepositoryInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /**
     * @param DocumentManager                       $em
     * @param string                                $className
     * @param IdentifiableObjectRepositoryInterface $productRepository
     */
    public function __construct(
        DocumentManager $em,
        $className,
        IdentifiableObjectRepositoryInterface $productRepository
    ) {
        parent::__construct($em, $em->getUnitOfWork(), $em->getClassMetadata($className));

        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->productRepository->findOneByIdentifier($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function searchAfterOffset(ProductQueryBuilderInterface $pqb, $limit, $offset)
    {
        $qb = $pqb->getQueryBuilder();

        return $qb
            ->limit($limit)
            ->skip($offset)
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function count(ProductQueryBuilderInterface $pqb)
    {
        return (int) $pqb->getQueryBuilder()->select('_id')
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return $this->productRepository->getIdentifierProperties();
    }
}
