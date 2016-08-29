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
            'Aescarcha\BusinessBundle\DataFixtures\ORM\LoadBusinessData',
        );
        $this->loadFixtures($classes);
        $this->client = static::createClient();
        $this->manager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
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
        $this->assertEquals( 'Fixtured business', $response['data']['name'] );
        $this->assertEquals( 'Fake description', $response['data']['description'] );
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

    private function getOneEntity()
    {
        return $this->manager->getRepository('AescarchaBusinessBundle:Business')->findAll()[0];
    }

}
