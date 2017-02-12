<?php

namespace Aescarcha\BusinessBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class BusinessAssetControllerTest extends WebTestCase
{
    protected $manager;
    protected $client;

    public function setUp()
    {
        $classes = array(
            'Aescarcha\UserBundle\DataFixtures\ORM\LoadUserData',
            'Aescarcha\BusinessBundle\DataFixtures\ORM\LoadBusinessData',
        );
        $this->loadFixtures($classes, null, 'doctrine', \Doctrine\Common\DataFixtures\Purger\ORMPurger::PURGE_MODE_TRUNCATE);
        $this->client = static::createClient();
        $this->manager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->login();
    }

    
    protected function loadFixtures(array $classNames, $omName = null, $registryName = 'doctrine', $purgeMode = null)
    {
        $container = $this->getContainer();
        /** @var ManagerRegistry $registry */
        $registry = $container->get($registryName);
        /** @var ObjectManager $om */
        $om = $registry->getManager($omName);
        $connection = $om->getConnection();
        if ($connection->getDriver() instanceof \Doctrine\DBAL\Driver\AbstractMySQLDriver) {
            $connection->exec(sprintf('SET foreign_key_checks=%s', 0));
        }
        parent::loadFixtures($classNames, $omName , $registryName , $purgeMode);
        if ($connection->getDriver() instanceof \Doctrine\DBAL\Driver\AbstractMySQLDriver) {
            $connection->exec(sprintf('SET foreign_key_checks=%s', 1));
        }
    }


    public function testUploadBusinessImage()
    {
        $entity = $this->getOneEntity();
        $image = base64_encode(file_get_contents(__DIR__ . '/bar.jpg'));
        $crawler = $this->client->request('POST',
                                          '/businesses/' . $entity->getId() . '/assets',
                                          [],
                                          [],
                                          array('CONTENT_TYPE' => 'application/json'),
                                            '{
                                            "file": "' . $image . '",
                                            "title":"The bar pic",
                                            "isThumb": false
                                            }'
                                        );
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals( 36, strlen($response['data']['id']) );
        $this->assertEquals( 'The bar pic', $response['data']['title'] );
        $this->assertEquals( false, $response['data']['isThumb'] );
        $this->assertEquals( $entity->getId(), $response['data']['businessId'] );
        $this->assertContains( '/' . $response['data']['id'], $response['data']['path'] );
        $this->assertContains( '/businesses/', $response['data']['links']['self']['uri'] );
    }

    private function getOneEntity()
    {
        return $this->manager->getRepository('AescarchaBusinessBundle:Business')->findAll()[0];
    }

    /**
     * Fake Login, @todo move this to use auth token
     * @param  string $userName
     */
    protected function login( $userName = 'Alvaro')
    {
        $session = $this->client->getContainer()->get('session');
        $container = $this->client->getContainer();
        $userManager = $container->get('fos_user.user_manager');
        $loginManager = $container->get('fos_user.security.login_manager');
        $firewallName = $container->getParameter('fos_user.firewall_name');
        $user = $userManager->findUserBy(array('username' => $userName));
        $loginManager->loginUser($firewallName, $user);
        $container->get('session')->set('_security_' . $firewallName,
                                        serialize($container->get('security.token_storage')->getToken()));
        $container->get('session')->set('_locale', $user->getLocale());

        $container->get('session')->save();
        $this->client->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie($session->getName(), $session->getId()));
        return $user;
    }


}
