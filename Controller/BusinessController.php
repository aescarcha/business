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
    public function getBusinessAction(Business $entity)
    {
        $fractal = new Manager();
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
     *     }
     * )
     */
    public function getBusinessesAction()
    {
        $fractal = new Manager();
        $businesses = $this->getDoctrine()->getManager()->getRepository('AescarchaBusinessBundle:Business');
        $entities = $businesses->findAll();
        $resource = new Collection($entities, new BusinessTransformer);
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

}
