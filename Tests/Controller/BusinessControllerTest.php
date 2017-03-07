<?php

namespace Aescarcha\BusinessBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class BusinessControllerTest extends WebTestCase
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


    public function testCreate()
    {
        $crawler = $this->client->request(
                         'POST',
                         '/businesses',
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'),
                         '{"name":"my unit test"}'
                         );
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals( 'my unit test', $response['data']['name'] );
        $this->assertEquals( 1, $response['data']['user_id'] );
        $this->assertContains( '/businesses/', $response['data']['links']['self']['uri'] );
    }

    public function testCreateNoData()
    {
        $crawler = $this->client->request('POST', '/businesses', [], [], array('CONTENT_TYPE' => 'application/json'), '{"description":"my unit test"}');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals( 'Symfony\Component\Validator\ConstraintViolation', $response['error']['type'] );
        $this->assertEquals( 'c1051bb4-d103-4f74-8988-acbcafc7fdc3', $response['error']['code'] );
        $this->assertEquals( 'name', $response['error']['property'] );
        $this->assertEquals( 'This value should not be blank.', $response['error']['message'] );
        $this->assertEquals( '', $response['error']['doc_url'] );
    }

    public function testGet()
    {
        $id = $this->getOneEntity()->getId();

        $crawler = $this->client->request(
                         'GET',
                         '/businesses/' . $id,
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals( 1, $response['data']['user_id'] );
        $this->assertEquals( 'Fixtured business', $response['data']['name'] );
        $this->assertEquals( 'Fake description', $response['data']['description'] );
        $this->assertContains( $id, $response['data']['thumbnail'] );
        $this->assertContains( 'jpg', $response['data']['thumbnail'] );
        $this->assertContains( 'images.waiter', $response['data']['thumbnail'] );
        $this->assertEquals( '/businesses/' . $id, $response['data']['links']['self']['uri'] );
    }


    public function testGetNotFound()
    {
        $crawler = $this->client->request(
                         'GET',
                         '/businesses/11112',
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'));

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testIndex()
    {
        $crawler = $this->client->request(
                         'GET',
                         '/businesses',
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertGreaterThan(1, count($response['data']));
        $this->assertEquals( 'Fixtured business', $response['data'][0]['name'] );
        $this->assertEquals( 'Fake description', $response['data'][0]['description'] );
        $this->assertContains( '/businesses/', $response['data'][0]['links']['self']['uri'] );
    }


    public function testIndexPagination()
    {
        $crawler = $this->client->request(
                         'GET',
                         '/businesses?limit=1',
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, count($response['data']));
        $this->assertEquals( 'Fixtured business', $response['data'][0]['name'] );
        $this->assertEquals( 'Fake description', $response['data'][0]['description'] );
        $this->assertContains( '/businesses/', $response['data'][0]['links']['self']['uri'] );
        $this->assertEquals( 0, $response['meta']['cursor']['current']);
        $this->assertEquals( 0, $response['meta']['cursor']['prev']);
        $this->assertEquals( 1, $response['meta']['cursor']['next']);
        $this->assertEquals( 1, $response['meta']['cursor']['count']);
    }

    public function testIndexPagination2(){
        $crawler = $this->client->request(
                                          'GET',
                                          '/businesses?cursor=1&previous=0&limit=1',
                                          array(),
                                          array(),
                                          array('CONTENT_TYPE' => 'application/json'));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, count($response['data']));
        $this->assertEquals( 'Fixtured business2', $response['data'][0]['name'] );
        $this->assertEquals( 'Fake description2', $response['data'][0]['description'] );
        $this->assertContains( '/businesses/', $response['data'][0]['links']['self']['uri'] );
        $this->assertEquals( 1, $response['meta']['cursor']['current']);
        $this->assertEquals( 0, $response['meta']['cursor']['prev']);
        $this->assertEquals( 2, $response['meta']['cursor']['next']);
        $this->assertEquals( 1, $response['meta']['cursor']['count']);
    }

    public function testIndexFiltered(){
        $crawler = $this->client->request(
                                          'GET',
                                          '/businesses?longitude=40.486946&latitude=-3.704084&distance=10', //we can find 40.486944, -3.724083
                                          array(),
                                          array(),
                                          array('CONTENT_TYPE' => 'application/json'));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, count($response['data']));
        $this->assertEquals( 'Fixtured business', $response['data'][0]['name'] );
        $this->assertEquals( 'Fake description', $response['data'][0]['description'] );
        $this->assertContains( '/businesses/', $response['data'][0]['links']['self']['uri'] );
        $this->assertEquals( 0, $response['meta']['cursor']['current']);
        $this->assertEquals( 0, $response['meta']['cursor']['prev']);
        $this->assertEquals( 10, $response['meta']['cursor']['next']);
        $this->assertEquals( 1, $response['meta']['cursor']['count']);
    }


    public function testIndexFilteredNoneFound(){
        $crawler = $this->client->request(
                                          'GET',
                                          '/businesses?longitude=35.486946&latitude=-3.704084&distance=10',
                                          array(),
                                          array(),
                                          array('CONTENT_TYPE' => 'application/json'));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(0, count($response['data']));
    }

    public function testDelete()
    {
        $entity = $this->getOneEntity();
        $crawler = $this->client->request(
                                          'DELETE',
                                          '/businesses/' . $entity->getId(),
                                          array(),
                                          array(),
                                          array('CONTENT_TYPE' => 'application/json'));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals( $entity->getName() , $response['data']['name'] );
        $this->assertEquals( $entity->getDescription(), $response['data']['description'] );
        $this->assertEquals( '/businesses/' . $entity->getId(), $response['data']['links']['self']['uri'] );

        $this->client = static::createClient();
        $this->login();
        $crawler = $this->client->request(
                         'GET',
                         '/businesses',
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $response['data']);

        $this->client = static::createClient();
        $this->login();
        $crawler = $this->client->request(
                                          'GET',
                                          '/businesses/' . $entity->getId(),
                                          array(),
                                          array(),
                                          array('CONTENT_TYPE' => 'application/json'));
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdate()
    {
        $entity = $this->getOneEntity();
        $crawler = $this->client->request(
                         'PATCH',
                         '/businesses/' . $entity->getId(),
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'),
                         '{"name":"my unit test edited"}'
                         );
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals( $entity->getUser()->getId(), $response['data']['user_id'] );
        $this->assertEquals( 'my unit test edited', $response['data']['name'] );
        $this->assertContains( '/businesses/' . $entity->getId(), $response['data']['links']['self']['uri'] );
    }

    public function testUpdateBadData()
    {
        $entity = $this->getOneEntity();
        $crawler = $this->client->request('PATCH', '/businesses/' . $entity->getId(), [], [], array('CONTENT_TYPE' => 'application/json'), '{"description":"my unit test"}');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals( 'Symfony\Component\Validator\ConstraintViolation', $response['error']['type'] );
        $this->assertEquals( 'c1051bb4-d103-4f74-8988-acbcafc7fdc3', $response['error']['code'] );
        $this->assertEquals( 'name', $response['error']['property'] );
        $this->assertEquals( 'This value should not be blank.', $response['error']['message'] );
        $this->assertEquals( '', $response['error']['doc_url'] );
    }

    public function testGetWithAssets()
    {
      $id = $this->getOneEntity()->getId();

      $crawler = $this->client->request(
                                        'GET',
                                        '/businesses/' . $id . '?embed=businessAssets',
                                        array(),
                                        array(),
                                        array('CONTENT_TYPE' => 'application/json'));

      $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
      $response = json_decode($this->client->getResponse()->getContent(), true);
      $this->assertEquals( 1, $response['data']['user_id'] );
      $this->assertEquals( 'Fixtured business', $response['data']['name'] );
      $this->assertEquals( 'Fake description', $response['data']['description'] );
      $this->assertEquals( 'Fake description', $response['data']['description'] );
      $this->assertEquals( 'fixtured bar 2', $response['data']['businessAssets']['data'][0]['title'] );
      $this->assertEquals( 36, strlen($response['data']['businessAssets']['data'][0]['id']) );
      $this->assertContains($response['data']['businessAssets']['data'][0]['id'], $response['data']['businessAssets']['data'][0]['path'] );
      $this->assertContains($id, $response['data']['businessAssets']['data'][0]['path'] );
      $this->assertEquals( '/businesses/' . $id, $response['data']['links']['self']['uri'] );
    }

    public function testGetFromUser()
    {
        $userId = $this->getOneEntity()->getUser()->getId();

        $crawler = $this->client->request(
                         'GET',
                         '/users/' . $userId . '/businesses',
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals( 1, $response['data'][0]['user_id'] );
        $this->assertEquals( 'Fixtured business', $response['data'][0]['name'] );
        $this->assertEquals( 'Fake description', $response['data'][0]['description'] );
        $this->assertContains( $response['data'][0]['id'], $response['data'][0]['thumbnail'] );
        $this->assertContains( 'jpg', $response['data'][0]['thumbnail'] );
        $this->assertContains( 'images.waiter', $response['data'][0]['thumbnail'] );
        $this->assertEquals( '/businesses/' . $response['data'][0]['id'], $response['data'][0]['links']['self']['uri'] );
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
