<?php

namespace webignition\Tests\WebResource;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use webignition\InternetMediaType\Parser\ParseException as InternetMediaTypeParseException;
use webignition\WebResource\Exception\InvalidContentTypeException;
use webignition\WebResource\Exception\Exception as WebResourceException;
use webignition\WebResource\JsonDocument;
use webignition\WebResource\Retriever;
use webignition\WebResource\WebPage\WebPage;
use webignition\WebResource\WebResource;
use webignition\WebResourceInterfaces\InvalidContentTypeExceptionInterface;
use webignition\WebResourceInterfaces\RetrieverExceptionInterface;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $retriever = new Retriever();
    }

    /**
     * @dataProvider throwsWebResourceExceptionDataProvider
     *
     * @param array $allowedContentTypes
     * @param bool $allowUnknownResourceTypes
     * @param array $httpFixtures
     * @param $expectedExceptionMessage
     * @param $expectedExceptionCode
     *
     * @throws InternetMediaTypeParseException
     * @throws InvalidContentTypeExceptionInterface
     */
    public function testThrowsWebResourceException(
        array $allowedContentTypes,
        $allowUnknownResourceTypes,
        array $httpFixtures,
        $expectedExceptionMessage,
        $expectedExceptionCode
    ) {
        $mockHandler = new MockHandler($httpFixtures);
        $httpClient = new HttpClient([
            'handler' => HandlerStack::create($mockHandler),
        ]);

        $request = new Request('GET', 'http://example.com');

        $retriever = new Retriever($httpClient, $allowedContentTypes, $allowUnknownResourceTypes);

        try {
            $retriever->retrieve($request);
            $this->fail(WebResourceException::class . ' not thrown');
        } catch (RetrieverExceptionInterface $webResourceException) {
            $this->assertEquals($expectedExceptionMessage, $webResourceException->getMessage());
            $this->assertEquals($expectedExceptionCode, $webResourceException->getCode());
        }

        $this->assertEquals(0, $mockHandler->count());
    }

    /**
     * @return array
     */
    public function throwsWebResourceExceptionDataProvider()
    {
        return [
            'http 404' => [
                'allowedContentTypes' => [],
                'allowUnknownResourceTypes' => true,
                'httpFixtures' => [
                    new Response(404),
                ],
                'expectedExceptionMessage' => 'Not Found',
                'expectedExceptionCode' => 404,
            ],
            'http 404 with content-type pre-verification' => [
                'allowedContentTypes' => [],
                'allowUnknownResourceTypes' => false,
                'httpFixtures' => [
                    new Response(404),
                    new Response(404),
                ],
                'expectedExceptionMessage' => 'Not Found',
                'expectedExceptionCode' => 404,
            ],
            'http 500' => [
                'allowedContentTypes' => [],
                'allowUnknownResourceTypes' => true,
                'httpFixtures' => [
                    new Response(500),
                ],
                'expectedExceptionMessage' => 'Internal Server Error',
                'expectedExceptionCode' => 500,
            ],
            'http 100' => [
                'allowedContentTypes' => [],
                'allowUnknownResourceTypes' => true,
                'httpFixtures' => [
                    new Response(100)
                ],
                'expectedExceptionMessage' => 'Continue',
                'expectedExceptionCode' => 100,
            ],
            'http 301' => [
                'allowedContentTypes' => [],
                'allowUnknownResourceTypes' => true,
                'httpFixtures' => [
                    new Response(301),
                ],
                'expectedExceptionMessage' => 'Moved Permanently',
                'expectedExceptionCode' => 301,
            ],
        ];
    }

    /**
     * @dataProvider getInvalidContentTypeDataProvider
     *
     * @param array $allowedContentTypes
     * @param bool $allowUnknownResourceTypes
     * @param array $httpFixtures
     * @param string $expectedExceptionMessage
     * @param string $expectedExceptionResponseContentType
     *
     * @throws InternetMediaTypeParseException
     * @throws InvalidContentTypeExceptionInterface
     * @throws RetrieverExceptionInterface
     */
    public function testGetInvalidContentType(
        array $allowedContentTypes,
        $allowUnknownResourceTypes,
        array $httpFixtures,
        $expectedExceptionMessage,
        $expectedExceptionResponseContentType
    ) {
        $mockHandler = new MockHandler($httpFixtures);
        $httpClient = new HttpClient([
            'handler' => HandlerStack::create($mockHandler),
        ]);

        $request = new Request('GET', 'http://example.com');

        $retriever = new Retriever($httpClient, $allowedContentTypes, $allowUnknownResourceTypes);

        try {
            $retriever->retrieve($request);
            $this->fail(InvalidContentTypeException::class . ' not thrown');
        } catch (InvalidContentTypeExceptionInterface $invalidContentTypeException) {
            $this->assertEquals($expectedExceptionMessage, $invalidContentTypeException->getMessage());
            $this->assertEquals(InvalidContentTypeException::CODE, $invalidContentTypeException->getCode());

            $this->assertEquals(
                $expectedExceptionResponseContentType,
                (string)$invalidContentTypeException->getContentType()
            );
        }

        $this->assertEquals(0, $mockHandler->count());
    }

    /**
     * @return array
     */
    public function getInvalidContentTypeDataProvider()
    {
        return [
            'no allowed content types; fails pre-verification' => [
                'allowedContentTypes' => [],
                'allowUnknownResourceTypes' => false,
                'httpFixtures' => [
                    new Response(),
                ],
                'expectedExceptionMessage' => 'Invalid content type ""',
                'expectedExceptionResponseContentType' => '',
            ],
            'disallowed content type; fails pre-verification' => [
                'allowedContentTypes' => [
                    'text/html',
                ],
                'allowUnknownResourceTypes' => false,
                'httpFixtures' => [
                    new Response(200, [
                        'Content-Type' => 'text/plain',
                    ]),
                ],
                'expectedExceptionMessage' => 'Invalid content type "text/plain"',
                'expectedExceptionResponseContentType' => 'text/plain',
            ],
            'disallowed content type; 500 on pre-verification, fails post-verification' => [
                'allowedContentTypes' => [
                    'text/html',
                ],
                'allowUnknownResourceTypes' => false,
                'httpFixtures' => [
                    new Response(500),
                    new Response(200, ['Content-Type' => 'text/plain']),
                ],
                'expectedExceptionMessage' => 'Invalid content type "text/plain"',
                'expectedExceptionResponseContentType' => 'text/plain',
            ],
            'no defined allowed content types; fails post-verification' => [
                'allowedContentTypes' => [],
                'allowUnknownResourceTypes' => false,
                'httpFixtures' => [
                    new Response(404),
                    new Response(200),
                ],
                'expectedExceptionMessage' => 'Invalid content type ""',
                'expectedExceptionResponseContentType' => '',
            ],
        ];
    }

    /**
     * @dataProvider getSuccessDataProvider
     *
     * @param array $allowedContentTypes
     * @param bool $allowUnknownResourceTypes
     * @param array $httpFixtures
     * @param string $expectedResourceClassName
     * @param string $expectedResourceContent
     *
     * @throws InternetMediaTypeParseException
     * @throws InvalidContentTypeExceptionInterface
     * @throws RetrieverExceptionInterface
     */
    public function testGetSuccess(
        array $allowedContentTypes,
        $allowUnknownResourceTypes,
        array $httpFixtures,
        $expectedResourceClassName,
        $expectedResourceContent
    ) {
        $mockHandler = new MockHandler($httpFixtures);
        $httpClient = new HttpClient([
            'handler' => HandlerStack::create($mockHandler),
        ]);

        $request = new Request('GET', 'http://example.com');

        $retriever = new Retriever($httpClient, $allowedContentTypes, $allowUnknownResourceTypes);
        $resource = $retriever->retrieve($request);

        $this->assertInstanceOf($expectedResourceClassName, $resource);
        $this->assertEquals($expectedResourceContent, $resource->getContent());

        $this->assertEquals(0, $mockHandler->count());
    }

    /**
     * @return array
     */
    public function getSuccessDataProvider()
    {
        return [
            'text/plain no mapped resource type' => [
                'allowedContentTypes' => [],
                'allowUnknownResourceTypes' => true,
                'httpFixtures' => [
                    new Response(200, ['Content-Type' => 'text/plain'], 'Foo'),
                ],
                'expectedResourceClassName' => WebResource::class,
                'expectedResourceContent' => 'Foo',
            ],
            'text/html' => [
                'allowedContentTypes' => [],
                'allowUnknownResourceTypes' => true,
                'httpFixtures' => [
                    new Response(200, ['Content-Type' => 'text/html'], '<!doctype><html>'),
                ],
                'expectedResourceClassName' => WebPage::class,
                'expectedResourceContent' => '<!doctype><html>',
            ],
            'application/json' => [
                'allowedContentTypes' => [],
                'allowUnknownResourceTypes' => true,
                'httpFixtures' => [
                    new Response(200, ['Content-Type' => 'application/json'], '[]'),
                ],
                'expectedResourceClassName' => JsonDocument::class,
                'expectedResourceContent' => '[]',
            ],
            'text/html with content-type pre-verification' => [
                'allowedContentTypes' => [
                    'text/html',
                ],
                'allowUnknownResourceTypes' => false,
                'httpFixtures' => [
                    new Response(200, ['Content-Type' => 'text/html']),
                    new Response(200, ['Content-Type' => 'text/html'], '<!doctype><html>'),
                ],
                'expectedResourceClassName' => WebPage::class,
                'expectedResourceContent' => '<!doctype><html>',
            ],
        ];
    }
}
