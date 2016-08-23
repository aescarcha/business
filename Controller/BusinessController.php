<?php

namespace Aescarcha\BusinessBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Aescarcha\BusinessBundle\Entity\Business;

class BusinessController extends FOSRestController
{
    public function getBusinessesAction()
    {
        return $this->render('AescarchaBusinessBundle:Default:index.html.twig');
    }

    public function postBusinessesAction( Request $request )
    {
        return $this->newAction( $request );
    }

    protected function newAction( Request $request )
    {
        $entity = new Business();
        $validator = $this->get('validator');

        $entity->setName($request->request->get('name'));
        $entity->setDescription($request->request->get('description'));
        $entity->setLatitude($request->request->get('latitude'));
        $entity->setLongitude($request->request->get('longitude'));

        $errors = $validator->validate($entity);
        if ( count($errors) === 0 ) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $view = $this->view($entity, 200);
            return $this->handleView($view);
        }
        $view = $this->view($errors, 409);
        return $this->handleView($view);
    }

}
