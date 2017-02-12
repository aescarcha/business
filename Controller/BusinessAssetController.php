<?php

namespace Aescarcha\BusinessBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

use Aescarcha\BusinessBundle\Entity\Business;
use Aescarcha\BusinessBundle\Entity\BusinessAsset;
use Aescarcha\BusinessBundle\Transformer\BusinessAssetTransformer;

use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\Annotations\Delete;

class BusinessAssetController extends FOSRestController
{

    /**
     * @Post("/businesses/{business}/assets")
     * @ApiDoc(
     *  resource=true,
     *  description="Create a new Business Asset Object",
     *  input="Aescarcha\BusinessBundle\Entity\BusinessAsset",
     *  output="Aescarcha\BusinessBundle\Entity\BusinessAsset",
     *  statusCodes={
     *         201="Returned when create is successful",
     *         400="Returned when data is invalid",
     *     }
     * )
     */
    public function postBusinessesAssetAction( Request $request, Business $business )
    {
        return $this->newAction( $request, $business );
    }


    protected function newAction( Request $request, Business $business )
    {
        $this->checkRights($business);
        $entity = new BusinessAsset();
        $validator = $this->get('validator');
        $fractal = new Manager();

        $entity->setTitle($request->request->get('title'));
        $entity->setIsThumb(intval($request->request->get('isThumb')));
        $entity->setBusiness($business);
        $file = $this->handleFile($request->request->get('file'));

        $errors = $validator->validate($entity);
        if ( count($errors) === 0 ) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $resource = new Item($entity, new BusinessAssetTransformer);
            $view = $this->view($fractal->createData($resource)->toArray(), 201);
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
            throw $this->createAccessDeniedException( "You do this." );
        }
    }

    protected function handleFile( string $fileData )
    {
        $fileData = base64_decode($fileData);
        $file = tmpfile();
        if ($file === false) 
            throw new \Exception('File can not be opened.');

        $path = stream_get_meta_data($file)['uri'];
        file_put_contents($path, $fileData);

        $uploadedFile = new UploadedFile($path, $path, null, null, null, true);
        return $uploadedFile;
    }


}
