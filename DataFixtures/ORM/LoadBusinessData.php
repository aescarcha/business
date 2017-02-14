<?php 
namespace Aescarcha\BusinessBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Aescarcha\BusinessBundle\Entity\Business;
use Aescarcha\BusinessBundle\Entity\BusinessAsset;

class LoadBusinessData implements FixtureInterface
{
    protected $data = [
        [
            'name' => 'Fixtured business',
            'description' => 'Fake description',
            'longitude' => 40.486944,
            'latitude' => -3.724083,
            'assets' => [
                [
                    'title' => 'fixtured bar 2',
                    'isThumb' => 1
                ]
            ],
        ],
        [
            'name' => 'Fixtured business2',
            'description' => 'Fake description2',
            'longitude' => 41.486944,
            'latitude' => -2.724083,
            'assets' => []
        ]
    ];

    public function load(ObjectManager $manager)
    {
        $user = $manager->getRepository('AescarchaUserBundle:User')->find(1);
        $image = base64_encode(file_get_contents(__DIR__ . '/bar.jpg'));
        foreach ($this->data as $key => $data) {
            $entity = new Business();
            $entity->setName($data['name']);
            $entity->setDescription($data['description']);
            $entity->setUser($user);
            $entity->setLongitude($data['longitude']);
            $entity->setLatitude($data['latitude']);
            $manager->persist($entity);

            foreach($data['assets'] as $assetData){
                $asset = new BusinessAsset();

                $asset->setTitle($assetData['title']);
                $asset->setIsThumb($assetData['isThumb']);
                $asset->setBusiness($entity);
                $manager->persist($asset);

                $partialPath = '/' . substr($entity->getId(), 0,2) .
                        '/' . substr($entity->getId(), 2, 2) . '/' . $entity->getId() . '/';
                $fullPath = '/var/www/waiter-assets/businesses' . $partialPath;
                $fileName = $asset->getId() . '.jpg';
                if(!is_dir( $fullPath )){
                    mkdir($fullPath, 0777, true);
                }

                copy(__DIR__ . '/bar.jpg', $fullPath . $fileName);
                $size = getimagesize($fullPath . $fileName); 

                $asset->setPath($partialPath . $fileName);
                $asset->setWidth($size[0]);
                $asset->setHeight($size[1]);
                $manager->persist($asset);
            }
        }

        $manager->flush();
    }
}