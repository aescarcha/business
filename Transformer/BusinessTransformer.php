<?php
namespace Aescarcha\BusinessBundle\Transformer;

use Aescarcha\BusinessBundle\Entity\Business;
use Aescarcha\BusinessBundle\Entity\BusinessAsset;
use Aescarcha\BusinessBundle\Transformer\BusinessAssetTransformer;
use League\Fractal;

class BusinessTransformer extends Fractal\TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'businessAssets',
    ];

    public function transform( Business $business )
    {
        return [
            'id'      => $business->getId(),
            'name'   => $business->getName(),
            'user_id'   => $business->getUser()->getId(),
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

    /**
     * Include BusinessAssets
     *
     * @return League\Fractal\ItemResource
     */
    public function includeBusinessAssets(Business $entity)
    {
        $entities = $entity->getBusinessAssets();

        return $this->collection($entities, new BusinessAssetTransformer);
    }


}

