<?php

namespace App\Tests\Shared;

use App\Tests\Shared\Dto\OpenApiTestDto;
use Nyholm\Psr7\Factory\Psr17Factory;
use Osteel\OpenApi\Testing\ValidatorBuilder;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\VarDumper\VarDumper;

class OpenApiTest extends ApiTest
{
    public const API_DOC = '/opt/docs/api.json';

    private function _testEndpoint(OpenApiTestDto $openApiTestDto): void
    {
        $validator = ValidatorBuilder::fromJsonFile(self::API_DOC)->getValidator();

        $request = $openApiTestDto->request;

        $this->client->request($request->method, $request->uri, $request->parameters, $request->files, $request->server, $request->content);
        $response = $this->client->getResponse();

        if ($response->getStatusCode() == 500) {
            VarDumper::dump($request);
            VarDumper::dump($response->getContent());
        }

        $this->assertEquals($openApiTestDto->expectedStatusCode, $response->getStatusCode());

        $psr17Factory = new Psr17Factory();
        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $psrResponse = $psrHttpFactory->createResponse($response);

        $result = $validator->validate($psrResponse, $request->uri, $request->method);
        $this->assertTrue($result);

        $json = json_decode($response->getContent(), true);

        foreach ($openApiTestDto->validators as $validator) {
            $this->$validator($json);
        }
    }

    public function testEndpoints()
    {
        /** @var OpenApiTestDto $openApiTestDto */
        foreach ($this->getOpenApiTests() as $openApiTestDto) {
            $this->_testEndpoint($openApiTestDto);
        }
    }

    public function getOpenApiTests(): array
    {
        return [];
    }
}
