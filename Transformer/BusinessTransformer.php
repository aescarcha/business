<?php
namespace Aescarcha\BusinessBundle\Transformer;

use Aescarcha\BusinessBundle\Entity\Business;
use League\Fractal;

class BusinessTransformer extends Fractal\TransformerAbstract
{
    public function transform( Business $business )
    {
        return [
            'id'      => $business->getId(),
            'name'   => $business->getName(),
            'description'   => $business->getDescription(),
            'longitude'    => (float) $business->getLongitude(),
            'latitude'    => (float) $business->getLatitude(),
            'links'   => [
                'self' => [
                    'rel' => 'self',
                    'uri' => '/businesses/'.$business->getId(),
                ],
            ],
        ];
    }

}

