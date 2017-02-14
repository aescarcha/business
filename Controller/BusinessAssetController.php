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

    /**
     * @Get("/businesses/{business}/assets")
     * @ApiDoc(
     *  resource=true,
     *  description="Get a Business Asset Objects",
     *  output="Aescarcha\BusinessBundle\Entity\BusinessAsset",
     *  requirements={
     *      {"name"="entity", "dataType"="uuid", "description"="Unique id of the business entity"}
     *  },
     *  statusCodes={
     *         200="Returned when entity exists",
     *         404="Returned when entity is not found",
     *     }
     * )
     */
    public function getBusinessAssetsAction( Request $request, Business $business )
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('AescarchaBusinessBundle:BusinessAsset');
        $fractal = new Manager();

        $entities = $repository->findByBusiness( $business )
                            ->getQuery()
                            ->getResult(); 

        $resource = new Collection($entities, new BusinessAssetTransformer);

        $view = $this->view($fractal->createData($resource)->toArray(), 200);
        return $this->handleView($view);
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
        $entity->setorder( $business->getBusinessAssets()->count() + 1 );
        $file = self::handleFile($request->request->get('file'));
        $errors = $file->getError();

        if(!$errors){
            $errors = $validator->validate($entity);
        }

        if ( count($errors) === 0 ) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            //move the file and save the path
            $entity = self::moveFile( $file, $entity, $this->container->hasParameter('image_paths.businesses') ? $this->container->getParameter('image_paths.businesses') : "/var/www/waiter-assets/businesses/" );
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

    protected static function handleFile( string $fileData )
    {
        $fileData = base64_decode($fileData);
        $file = tmpfile();
        if ($file === false) 
            throw new \Exception('File can not be opened.');

        $path = stream_get_meta_data($file)['uri'] . '-nontemp';
        file_put_contents($path, $fileData);

        $size = getimagesize($path); 
        if ($size === false) 
            throw new \Exception('File is not an image.');

        $uploadedFile = new UploadedFile($path, $path, mime_content_type($path), null, null, true);
        return $uploadedFile;
    }

    protected static function moveFile( UploadedFile $file, BusinessAsset $businessAsset, $basePath )
    {
        $partialPath = substr($businessAsset->getBusiness()->getId(), 0,2) . '/' . substr($businessAsset->getBusiness()->getId(), 2, 2) . '/' . $businessAsset->getBusiness()->getId() . '/';
        $finalPath = $basePath . $partialPath;
        $fileName = $businessAsset->getId();

        $file = $file->move( $file->getPathName() . '-existing' , "test");

        $size = getimagesize($file->getPathName()); 

        switch ($size['mime']) { 
            case "image/gif": 
                $fileName .= '.gif';
            break; 
            case "image/jpeg": 
                $fileName .= '.jpg';
            break; 
            case "image/png": 
                $fileName .= '.png';
            break; 
            case "image/bmp": 
                $fileName .= '.bmp';
            break; 
        } 
        $file = $file->move( $finalPath, $fileName);

        $businessAsset->setPath('/' . $partialPath . $fileName);
        $businessAsset->setWidth($size[0]);
        $businessAsset->setHeight($size[1]);
        return $businessAsset;
    }


}
