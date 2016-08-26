<?php

namespace Aescarcha\BusinessBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class BusinessControllerTest extends WebTestCase
{
    public function setUp()
    {
        $classes = array(
            'Aescarcha\BusinessBundle\DataFixtures\ORM\LoadBusinessData',
        );
        $this->loadFixtures($classes);
    }

    public function testCreate()
    {
        $client = static::createClient();

        $crawler = $client->request(
                         'POST',
                         '/businesses',
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'),
                         '{"name":"my unit test"}'
                         );
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals( 'my unit test', $response['data']['name'] );
        $this->assertContains( '/businesses/', $response['data']['links']['self']['uri'] );
    }

    public function testCreateNoData()
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/businesses', [], [], array('CONTENT_TYPE' => 'application/json'), '{"description":"my unit test"}');
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals( 'Symfony\Component\Validator\ConstraintViolation', $response['error']['type'] );
        $this->assertEquals( 'c1051bb4-d103-4f74-8988-acbcafc7fdc3', $response['error']['code'] );
        $this->assertEquals( 'name', $response['error']['property'] );
        $this->assertEquals( 'This value should not be blank.', $response['error']['message'] );
        $this->assertEquals( '', $response['error']['doc_url'] );
    }

    public function testGet()
    {
        $client = static::createClient();

        $crawler = $client->request(
                         'GET',
                         '/businesses/1',
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'));

        // $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), true);
        dump($response);
        $this->assertEquals( 'Fixtured business', $response['data']['name'] );
        $this->assertEquals( 'Fake description', $response['data']['description'] );
        $this->assertContains( '/businesses/1', $response['data']['links']['self']['uri'] );
    }


    public function testGetNotFound()
    {
        $client = static::createClient();

        $crawler = $client->request(
                         'GET',
                         '/businesses/11112',
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'));

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

}
