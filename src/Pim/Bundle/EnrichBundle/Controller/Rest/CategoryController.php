<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\Classification\Updater\CategoryUpdater;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\EnrichBundle\Twig\CategoryExtension;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryController
{
    /** @var CategoryRepositoryInterface */
    protected $repository;

    /** @var CategoryExtension */
    protected $twigExtension;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var CategoryUpdater */
    protected $updater;

    /** @var SaverInterface */
    protected $saver;

    /** @var RemoverInterface */
    protected $remover;

    /** @var SimpleFactoryInterface  */
    protected $categoryFactory;

    /**
     * @param CategoryRepositoryInterface $repository
     * @param CategoryExtension           $twigExtension
     * @param NormalizerInterface         $normalizer
     * @param CategoryUpdater             $updater
     * @param SaverInterface              $saver
     * @param RemoverInterface            $remover
     * @param SimpleFactoryInterface      $categoryFactory
     */
    public function __construct(
        CategoryRepositoryInterface $repository,
        CategoryExtension $twigExtension,
        NormalizerInterface $normalizer,
        CategoryUpdater $updater,
        SaverInterface $saver,
        RemoverInterface $remover,
        SimpleFactoryInterface $categoryFactory
    ) {
        $this->repository = $repository;
        $this->twigExtension = $twigExtension;
        $this->normalizer = $normalizer;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->remover = $remover;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * List children categories
     *
     * @param Request $request    The request object
     * @param int     $identifier The parent category identifier
     *
     * @return array
     */
    public function listSelectedChildrenAction(Request $request, $identifier)
    {
        $parent = $this->repository->findOneByIdentifier($identifier);

        if (null === $parent) {
            return new JsonResponse(null, 404);
        }

        $selectedCategories = $this->repository->getCategoriesByCodes($request->get('selected', []));
        if (0 !== $selectedCategories->count()) {
            $tree = $this->twigExtension->listCategoriesResponse(
                $this->repository->getFilledTree($parent, $selectedCategories),
                $selectedCategories
            );
        } else {
            $tree = $this->twigExtension->listCategoriesResponse(
                $this->repository->getFilledTree($parent, new ArrayCollection([$parent])),
                new ArrayCollection()
            );
        }

        // Returns only children of the given category without the node itself
        if (!empty($tree)) {
            $tree = $tree[0]['children'];
        }

        return new JsonResponse($tree);
    }

    /**
     * List root categories
     *
     * @return JsonResponse
     */
    public function listAction()
    {
        $categories = $this->repository->findBy(
            [
                'parent' => null,
            ]
        );

        return new JsonResponse(
            $this->normalizer->normalize($categories, 'internal_api')
        );
    }

    /**
     * @param string $identifier
     *
     * @return JsonResponse
     */
    public function getAction($identifier)
    {
        $category = $this->repository->findOneByIdentifier($identifier);

        $normalizedCategory = $this->normalizer->normalize($category, 'internal_api');

        return new JsonResponse($normalizedCategory);
    }

    /**
     * Saves new category
     *
     * @param Request $request
     *
     * @throws PropertyException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @return JsonResponse
     */
    public function postAction(Request $request)
    {
        $category = $this->categoryFactory->create();

        return $this->saveCategory($category, $request);
    }

    /**
     * Updates category
     *
     * @param Request $request
     * @param string  $code
     *
     * @throws HttpExceptionInterface
     * @throws PropertyException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @return JsonResponse
     */
    public function putAction(Request $request, $code)
    {
        $category = $this->getCategory($code);

        return $this->saveCategory($category, $request);
    }

    /**
     * Removes category
     *
     * @param $code
     *
     * @throws HttpExceptionInterface
     * @throws \InvalidArgumentException
     *
     * @return JsonResponse
     */
    public function removeAction($code)
    {
        $category = $this->getCategory($code);
        $this->remover->remove($category);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param $code
     *
     * @throws HttpExceptionInterface
     *
     * @return CategoryInterface
     */
    protected function getCategory($code)
    {
        $category = $this->repository->findOneBy(
            [
                'code' => $code,
            ]
        );

        if (null === $category) {
            throw new NotFoundHttpException(
                sprintf('Category with code %s does not exist.', $code)
            );
        }

        return $category;
    }

    /**
     * @param CategoryInterface $category
     * @param Request          $request
     *
     * @throws \LogicException
     * @throws PropertyException
     * @throws \InvalidArgumentException
     *
     * @return JsonResponse
     */
    protected function saveCategory($category, $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->updater->update($category, $data);

        //TODO ALBAN => This is usefull????
        /*$violations = $this->validator->validate($category);

        if (0 < $violations->count()) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = [
                    'message' => $violation->getMessage()
                ];
            }

            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }*/

        $this->saver->save($category);

        return new JsonResponse(
            $this->normalizer->normalize(
                $category,
                'internal_api'
            )
        );
    }
}
