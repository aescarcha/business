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
        ],
        [
            'name' => 'Fixtured business2',
            'description' => 'Fake description2',
        ]
    ];

    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $key => $data) {
            $entity = new Business();
            $entity->setName($data['name']);
            $entity->setDescription($data['description']);
            $manager->persist($entity);
        }

        $manager->flush();
    }
}