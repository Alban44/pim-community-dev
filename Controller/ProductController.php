<?php
namespace Pim\Bundle\ProductBundle\Controller;

use Pim\Bundle\ProductBundle\Entity\AttributeGroup;
use Pim\Bundle\ProductBundle\Manager\MediaManager;
use Symfony\Component\HttpFoundation\File\File;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\ProductBundle\Entity\Product;
use Pim\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Component\HttpFoundation\Request;
use YsTools\BackUrlBundle\Annotation\BackUrl;

/**
 * Product Controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductController extends Controller
{

    /**
     * Get product manager
     * @return FlexibleManager
     */
    protected function getProductManager()
    {
        $pm = $this->container->get('product_manager');
        // force data locale if provided
        $dataLocale = $this->getRequest()->get('dataLocale');
        $pm->setLocale($dataLocale);
        // force data scope if provided
        $dataScope = $this->getRequest()->get('dataScope');
        $dataScope = ($dataScope) ? $dataScope : 'ecommerce';
        $pm->setScope($dataScope);

        return $pm;
    }

    /**
     * List product attributes
     * @param Request $request
     *
     * @Route("/index.{_format}",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @return template
     */
    public function indexAction(Request $request)
    {
        /** @var $gridManager ProductDatagridManager */
        $gridManager = $this->get('pim_product.product_grid_manager');
        $datagrid = $gridManager->getDatagrid();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'PimProductBundle:Product:index.html.twig';
        }

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Get dedicated PIM filesystem
     *
     * @return MediaManager
     */
    protected function getMediaManager()
    {
        return $this->container->get('pim_media_manager');
    }

    /**
     * Create product
     *
     * @param string $dataLocale data locale
     * @param string $dataScope  data scope
     *
     * @Route("/create/{dataLocale}/{dataScope}", defaults={"dataLocale" = null, "dataScope" = null})
     * @Template("PimProductBundle:Product:edit.html.twig")
     *
     * @return array
     */
    public function createAction($dataLocale, $dataScope)
    {
        $entity = $this->getProductManager()->createFlexible(true);

        return $this->editAction($entity, $dataLocale, $dataScope);
    }

    /**
     * Edit product
     *
     * @param Product $entity     product
     * @param string  $dataLocale data locale
     * @param string  $dataScope  data scope
     *
     * @Route(
     *     "{id}/edit",
     *     requirements={"id"="\d+"}
     * )
     * @Template
     *
     * @return array
     */
    public function editAction($id)
    {
        $entity = $this->getProductManager()->localizedFind($id);
        if (!$entity) {
            throw $this->createNotFoundException(sprintf(
                'Product with id %d could not be found.', $id
            ));
        }

        $request = $this->getRequest();

        // create form
        $entClassName = $this->getProductManager()->getFlexibleName();
        $valueClassName = $this->getProductManager()->getFlexibleValueName();
        $form = $this->createForm(new ProductType($entClassName, $valueClassName), $entity);
        $groups = $this->getDoctrine()->getRepository('PimProductBundle:AttributeGroup')->findAllWithVirtualGroup();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                // TODO: to move to a relevant method in media manager + use listener
                $index = 0;
                // upload files if exist
                foreach ($entity->getValues() as $value) {
                    if ($value->getMedia() !== null) {
                        // upload file
                        if ($value->getMedia()->getFile() !== null) {
                            $filename = $entity->getSku() .'-'. $value->getAttribute()->getCode() .'-'.
                                        $value->getLocale() .'-'. $value->getScope() .'-'. time() .'-'.
                                        $value->getMedia()->getFile()->getClientOriginalName();

                            $this->getMediaManager()->upload($value->getMedia(), $filename);
                        } elseif ($value->getMedia()->getFile() === null &&
                                (!$value->getMedia()->getId() ||
                                $form->get('values')->get($index)->get('media')->get('remove')->getData() === true)) {
                            // unkink media if exists
                            if ($this->getMediaManager()->fileExists($value->getMedia())) {
                                $this->getMediaManager()->delete($value->getMedia());
                            }
                            // remove value if empty file
                            $value->setMedia(null);
                        }
                    }
                    $index++;
                }

                $this->getProductManager()->save($entity);

                $this->get('session')->getFlashBag()->add('success', 'Product successfully saved');
                $params = array(
                    'id'         => $entity->getId(),
                    'dataLocale' => $request->query->get('dataLocale'),
                    'dataScope'  => $request->query->get('dataScope')
                );

                return $this->redirect($this->generateUrl('pim_product_product_edit', $params));
            }
        }

        return array(
            'form'       => $form->createView(),
            'groups'     => $groups,
            'dataLocale' => $request->query->get('dataLocale', 'en_US'),
            'dataScope'  => $request->query->get('dataScope')
        );
    }

    /**
     * Remove product
     *
     * @param Product $entity
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     *
     * @BackUrl("back")
     *
     * @return array
     */
    public function removeAction(Product $entity)
    {
        $em = $this->getProductManager()->getStorageManager();
        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Product successfully removed');

        return $this->redirect($this->generateUrl('pim_product_product_index'));
    }
}
