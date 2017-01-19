<?php 
namespace Aescarcha\BusinessBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Aescarcha\BusinessBundle\Entity\Business;

class LoadBusinessData implements FixtureInterface
{
    protected $data = [
        [
            'name' => 'Fixtured business',
            'description' => 'Fake description',
            'longitude' => 40.486944,
            'latitude' => -3.724083,
        ],
        [
            'name' => 'Fixtured business2',
            'description' => 'Fake description2',
            'longitude' => 41.486944,
            'latitude' => -2.724083,
        ]
    ];

    public function load(ObjectManager $manager)
    {
        $user = $manager->getRepository('AescarchaUserBundle:User')->find(1);
        foreach ($this->data as $key => $data) {
            $entity = new Business();
            $entity->setName($data['name']);
            $entity->setDescription($data['description']);
            $entity->setUser($user);
            $entity->setLongitude($data['longitude']);
            $entity->setLatitude($data['latitude']);
            $manager->persist($entity);
        }

        $manager->flush();
    }
}