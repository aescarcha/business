<?php

namespace Aescarcha\BusinessBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

use Aescarcha\BusinessBundle\Entity\Business;
use Aescarcha\BusinessBundle\Transformer\BusinessTransformer;
use Aescarcha\BusinessBundle\Transformer\ErrorTransformer;

use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Serializer\ArraySerializer;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class BusinessController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Create a new Business Object",
     *  input="Aescarcha\BusinessBundle\Entity\Business",
     *  output="Aescarcha\BusinessBundle\Entity\Business",
     *  statusCodes={
     *         201="Returned when create is successful",
     *         400="Returned when data is invalid",
     *     }
     * )
     */
    public function postBusinessesAction( Request $request )
    {
        return $this->newAction( $request );
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Update a Business Object",
     *  input="Aescarcha\BusinessBundle\Entity\Business",
     *  output="Aescarcha\BusinessBundle\Entity\Business",
     *  statusCodes={
     *         200="Returned when update is successful",
     *         400="Returned when data is invalid",
     *     }
     * )
     */
    public function patchBusinessesAction( Request $request, Business $entity )
    {
        return $this->updateAction( $request, $entity );
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Finds an displays a Business entity",
     *  output="Aescarcha\BusinessBundle\Entity\Business",
     *  requirements={
     *      {"name"="entity", "dataType"="uuid", "description"="Unique id of the business entity"}
     *  },
     *  statusCodes={
     *         200="Returned when entity exists",
     *         404="Returned when entity is not found",
     *     }
     * )
     */
    public function getBusinessAction(Request $request, Business $entity)
    {
        $fractal = new Manager();
        $fractal->parseIncludes($request->query->get('embed', []));

        $resource = new Item($entity, new BusinessTransformer);
        $view = $this->view($fractal->createData($resource)->toArray(), 200);
        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Retrieves a list of Business entities",
     *  output="Aescarcha\BusinessBundle\Entity\Business",
     *  statusCodes={
     *         200="Returned when entity exists",
     *         404="Returned when entity is not found",
     *     },
     *  parameters={
     *      {"name"="cursor", "dataType"="integer", "required"=false, "description"="Current cursor"},
     *      {"name"="previous", "dataType"="integer", "required"=false, "description"="Previous cursor"},
     *      {"name"="count", "dataType"="integer", "required"=false, "description"="Entities per cursor"}
     *     }
     * )
     */
    public function getBusinessesAction( Request $request )
    {
        $fractal = new Manager();
        $businesses = $this->getDoctrine()->getManager()->getRepository('AescarchaBusinessBundle:Business');

        $currentCursor = $request->query->get('cursor', 0);
        $previousCursor = $request->query->get('previous', 0);
        $limit = $request->query->get('limit', 10);
        $newCursor = $currentCursor + $limit;

        $entities = $businesses->findByConditions($request->query->all())
                            ->setFirstResult( $currentCursor )
                            ->setMaxResults( $limit )
                            ->getQuery()
                            ->getResult(); //Maybe we should turn hydration off for listing queries

        $cursor = new Cursor($currentCursor, $previousCursor, $newCursor, count($entities ));

        $resource = new Collection($entities, new BusinessTransformer);
        $resource->setCursor($cursor);

        $view = $this->view($fractal->createData($resource)->toArray(), 200);
        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Deletes a Business entity",
     *  output="Aescarcha\BusinessBundle\Entity\Business",
     *  requirements={
     *      {"name"="entity", "dataType"="uuid", "description"="Unique id of the business entity"}
     *  },
     *  statusCodes={
     *         200="Returned when entity was deleted",
     *         404="Returned when entity is not found",
     *     }
     * )
     */
    public function deleteBusinessAction(Business $entity)
    {
        $fractal = new Manager();

        $this->checkRights( $entity );

        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();

        $resource = new Item($entity, new BusinessTransformer);
        $view = $this->view($fractal->createData($resource)->toArray(), 200);
        return $this->handleView($view);
    }

    protected function newAction( Request $request )
    {
        $entity = new Business();
        $validator = $this->get('validator');
        $fractal = new Manager();

        $entity->setName($request->request->get('name'));
        $entity->setDescription($request->request->get('description'));
        $entity->setLatitude($request->request->get('latitude'));
        $entity->setLongitude($request->request->get('longitude'));
        $entity->setUser($this->get('security.token_storage')->getToken()->getUser());

        $errors = $validator->validate($entity);
        if ( count($errors) === 0 ) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $resource = new Item($entity, new BusinessTransformer);
            $view = $this->view($fractal->createData($resource)->toArray(), 201);
            return $this->handleView($view);
        }

        //This serializer won't set the "data" namespace for errors
        $fractal->setSerializer(new ArraySerializer());
        $resource = new Item($errors->get(0), new ErrorTransformer);
        $view = $this->view($fractal->createData($resource)->toArray(), 400);

        return $this->handleView($view);
    }

    protected function updateAction( Request $request, Business $entity )
    {
        $this->checkRights( $entity );
        
        $validator = $this->get('validator');
        $fractal = new Manager();

        $entity->setName($request->request->get('name'));
        $entity->setDescription($request->request->get('description'));
        $entity->setLatitude($request->request->get('latitude'));
        $entity->setLongitude($request->request->get('longitude'));

        $errors = $validator->validate($entity);
        if ( count($errors) === 0 ) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $resource = new Item($entity, new BusinessTransformer);
            $view = $this->view($fractal->createData($resource)->toArray(), 200);
            return $this->handleView($view);
        }

        //This serializer won't set the "data" namespace for errors
        $fractal->setSerializer(new ArraySerializer());
        $resource = new Item($errors->get(0), new ErrorTransformer);
        $view = $this->view($fractal->createData($resource)->toArray(), 400);

        return $this->handleView($view);
    }

    protected function checkRights( Business $entity )
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        if($entity->getUser()->getId() !== $user->getId()){
            throw $this->createAccessDeniedException( "You can't delete this entity." );
        }
    }

}
