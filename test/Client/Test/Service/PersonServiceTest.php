<?php
use Client\Entity\Person;
use Client\Entity\PersonCreateRequest;
use Client\Entity\PersonCreateRequestData;
use Client\Entity\PersonData;
use Client\Entity\PersonGlobal;
use Client\Entity\PersonTipee;
use Client\HttpClient\HttpClientInterface;
use Client\Service\PersonService;
use Client\Test\TestCase;
use GuzzleHttp\Psr7\Response;

/**
 * Class PersonServiceTest
 */
class PersonServiceTest extends TestCase
{
    /**
     * @test
     */
    public function create_Success()
    {
        $expectedPerson = $this->getPersonCreateRequest();

        $expectedPersonAsJson = $this->serialize($expectedPerson);

        $response = new Response(200);

        $httpClient = $this->prophesize(HttpClientInterface::class);
        $httpClient->post('users/create', $expectedPersonAsJson, [])
                   ->willReturn($response)
                   ->shouldBeCalled();

        /** @var HttpClientInterface $httpClient */
        $personService = new PersonService($this->getSerializer(), $httpClient->reveal());
        $personService->create($expectedPerson);
    }

    /**
     * @test
     */
    public function updatePersonGlobal_Success()
    {
        $totemId = '0102030405060708090';
        $expectedPersonGlobal = $this->getPersonCreateRequest()->getData()->getPersonGlobal();
        $expectedPersonGlobalAsJson = $this->serialize($expectedPersonGlobal);

        $response = new Response(200);

        $httpClient = $this->prophesize(HttpClientInterface::class);
        $httpClient->put("users/$totemId/global", $expectedPersonGlobalAsJson, [])
                   ->willReturn($response)
                   ->shouldBeCalled();


        /** @var HttpClientInterface $httpClient */
        $personService = new PersonService($this->getSerializer(), $httpClient->reveal());
        $personService->updatePersonGlobal($totemId, $expectedPersonGlobal);
    }

    /**
     * @test
     */
    public function updatePersonTipee_Success()
    {
        $namespace = 'tipee/testing';
        $totemId = '0102030405060708090';

        $expectedPersonTipee = $this->getPersonCreateRequest()->getData()->getPersonTipee();
        $expectedPersonTipeeAsJson = $this->serialize($expectedPersonTipee);

        $response = new Response(200);

        $httpClient = $this->prophesize(HttpClientInterface::class);
        $httpClient->put("users/$totemId/$namespace", $expectedPersonTipeeAsJson, [])
                   ->willReturn($response)
                   ->shouldBeCalled();

        /** @var HttpClientInterface $httpClient */
        $personService = new PersonService($this->getSerializer(), $httpClient->reveal());
        $personService->updatePersonTipee($totemId, $namespace, $expectedPersonTipee);
    }

    /**
     * @test
     */
    public function getTotemData_Success()
    {
        $namespace = 'tipee/testing';
        $totemId = '0102030405060708090';
        $expectedPersonTipee = new PersonTipee();

        $response = new Response(200, [], $this->serialize($expectedPersonTipee));

        $httpClient = $this->prophesize(HttpClientInterface::class);
        $httpClient->get("users/$totemId/$namespace", [])
                   ->shouldBeCalled()
                   ->willReturn($response);

        /** @var HttpClientInterface $httpClient */
        $personService = new PersonService($this->getSerializer(), $httpClient->reveal());
        $personTipee = $personService->getTotemData($totemId, $namespace);

        $this->assertEquals($expectedPersonTipee, $personTipee);
    }

    /**
     * @test
     */
    public function logout_Success()
    {
        $httpClient = $this->prophesize(HttpClientInterface::class);
        $httpClient->post('session/logout', [], [])
                   ->shouldBeCalled()
                   ->willReturn(new Response(401));

        $personService = new PersonService($this->getSerializer(), $httpClient->reveal());
        $personService->logout();

        $namespace = 'tipee/testing';
        $totemId = '0102030405060708090';

        $httpClient = $this->prophesize(HttpClientInterface::class);
        $httpClient->get("users/$totemId/$namespace", [])
            ->shouldBeCalled()
            ->willReturn(new Response(401));

        /** @var HttpClientInterface $httpClient */
        $personService = new PersonService($this->getSerializer(), $httpClient->reveal());
        $personTipee = $personService->getTotemData($totemId, $namespace);
        
        $this->assertEquals(false, $personTipee);
    }

    /**
     * @return Person
     */
    protected function getPersonCreateRequest()
    {
        $expectedPerson = new Person();

        $data = new PersonData();
        $data->setPersonGlobal(new PersonGlobal())
             ->setPersonTipee(new PersonTipee());


        $expectedPerson->addNamespace('namespace')
                       ->setData($data);

        return $expectedPerson;
    }

}