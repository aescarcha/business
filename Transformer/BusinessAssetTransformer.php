<?php
namespace Aescarcha\BusinessBundle\Transformer;

use Aescarcha\BusinessBundle\Entity\BusinessAsset;
use League\Fractal;

class BusinessAssetTransformer extends Fractal\TransformerAbstract
{
    public function transform( BusinessAsset $entity )
    {
        return [
            'id'      => $entity->getId(),
            'businessId'   => $entity->getBusiness()->getId(),
            'title'   => $entity->getTitle(),
            'type'   => $entity->getType(),
            'path'   => $entity->getPath(),
            'isThumb'   => (bool)$entity->getIsThumb(),
            'order'   => intval($entity->getOrder()),
            'width'   => $entity->getWidth(),
            'height'   => $entity->getHeight(),
            'links'   => [
                'self' => [
                    'rel' => 'self',
                    'uri' => 'images.waiterproject.com/business-assets/'.$entity->getId(),
                ],
            ],
        ];
    }

}

