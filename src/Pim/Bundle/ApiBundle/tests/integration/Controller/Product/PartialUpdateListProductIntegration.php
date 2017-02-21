<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\CatalogBundle\Version;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateListProductIntegration extends AbstractProductTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->createProduct('product_family', [
            'family' => 'familyA2',
        ]);
    }

    public function testPartialUpdateListWithTooLongLines()
    {
        $line = [
            'invalid_json_1'  => str_repeat('a', $this->getBufferSize() - 1),
            'invalid_json_2'  => str_repeat('a', $this->getBufferSize()),
            'invalid_json_3'  => '',
            'line_too_long_1' => '{"identifier":"foo"}' . str_repeat('a', $this->getBufferSize()),
            'line_too_long_2' => '{"identifier":"foo"}' . str_repeat(' ', $this->getBufferSize()),
            'line_too_long_3' => str_repeat('a', $this->getBufferSize() + 1),
            'line_too_long_4' => str_repeat('a', $this->getBufferSize() + 2),
            'line_too_long_5' => str_repeat('a', $this->getBufferSize() * 2),
            'line_too_long_6' => str_repeat('a', $this->getBufferSize() * 5),
            'invalid_json_4'  => str_repeat('a', $this->getBufferSize()),
        ];

        $data =
<<<JSON
${line['invalid_json_1']}
${line['invalid_json_2']}
${line['invalid_json_3']}
${line['line_too_long_1']}
${line['line_too_long_2']}
${line['line_too_long_3']}
${line['line_too_long_4']}
${line['line_too_long_5']}
${line['line_too_long_6']}
${line['invalid_json_4']}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":400,"message":"Invalid json message received"}
{"line":2,"code":400,"message":"Invalid json message received"}
{"line":3,"code":400,"message":"Invalid json message received"}
{"line":4,"code":400,"message":"Line is too long."}
{"line":5,"code":400,"message":"Line is too long."}
{"line":6,"code":400,"message":"Line is too long."}
{"line":7,"code":400,"message":"Line is too long."}
{"line":8,"code":400,"message":"Line is too long."}
{"line":9,"code":400,"message":"Line is too long."}
{"line":10,"code":400,"message":"Invalid json message received"}

JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);

        $this->assertSame(Response::HTTP_OK, $response['status']);
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testCreateAndUpdateSameProduct()
    {
        $data =
<<<JSON
    {"identifier": "my_identifier"}
    {"identifier": "my_identifier"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"identifier":"my_identifier","code":201}
{"line":2,"identifier":"my_identifier","code":204}

JSON;


        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);

        $this->assertSame(Response::HTTP_OK, $response['status']);
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testErrorWhenIdentifierIsMissing()
    {
        $data =
<<<JSON
    {"code": "my_identifier"}
    {"identifier": null}
    {"identifier": ""}
    {"identifier": " "}
    {}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":422,"message":"Identifier is missing."}
{"line":2,"code":422,"message":"Identifier is missing."}
{"line":3,"code":422,"message":"Identifier is missing."}
{"line":4,"code":422,"message":"Identifier is missing."}
{"line":5,"code":422,"message":"Identifier is missing."}

JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);

        $this->assertSame(Response::HTTP_OK, $response['status']);
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateWhenUpdaterFailed()
    {
        $data =
<<<JSON
    {"identifier": "foo", "variant_group":"bar"}
JSON;


        $version = substr(Version::VERSION, 0, 3);
        $expectedContent =
<<<JSON
{"line":1,"identifier":"foo","code":422,"message":"Property \"variant_group\" expects a valid variant group code. The variant group does not exist, \"bar\" given. Check the standard format documentation.","_links":{"documentation":{"href":"https:\/\/docs.akeneo.com\/${version}\/reference\/standard_format\/products.html"}}}

JSON;


        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);

        $this->assertSame(Response::HTTP_OK, $response['status']);
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateWhenValidationFailed()
    {
        $data =
<<<JSON
    {"identifier": "foo,"}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"identifier":"foo,","code":422,"message":"Validation failed.","errors":[{"field":"values[sku].varchar","message":"This field should not contain any comma or semicolon."}]}

JSON;


        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);

        $this->assertSame(Response::HTTP_OK, $response['status']);
        $this->assertSame($expectedContent, $response['content']);
    }

    protected function getBufferSize()
    {
        return $this->getParameter('api_buffer_size');
    }

    /**
     * Execute a request where the response is streamed by chunk.
     *
     * The whole content of the request and the whole content of the response
     * are loaded in memory.
     * Therefore, do not use this function on with an high input/output volumetry.
     *
     * @param string $method
     * @param string $uri
     * @param array  $parameters
     * @param array  $files
     * @param array  $server
     * @param null   $content
     * @param bool   $changeHistory
     *
     * @return array
     */
    public function executeStreamRequest($method, $uri, array $parameters = [], array $files = [], array $server = [], $content = null, $changeHistory = true)
    {
        $streamedContent = '';

        ob_start(function($buffer) use (&$streamedContent) {
            $streamedContent .= $buffer;

            return '';
        });

        $client = $this->createAuthenticatedClient();
        $client->setServerParameter('CONTENT_TYPE', 'application/json-stream');
        $client->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);

        ob_end_flush();

        $response = [
            'status'  => $client->getResponse()->getStatusCode(),
            'content' => $streamedContent,
        ];

        return $response;
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            true
        );
    }

}
