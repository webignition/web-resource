<?php

namespace webignition\Tests\WebResource\Sitemap;

use webignition\InternetMediaType\Parser\ParseException as InternetMediaTypeParseException;
use webignition\Tests\WebResource\Factory\ResponseFactory;
use webignition\Tests\WebResource\Factory\UriFactory;
use webignition\WebResource\Sitemap\Factory;
use webignition\WebResource\Sitemap\Sitemap;
use webignition\WebResourceInterfaces\SitemapInterface;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new Factory();
    }

    /**
     * @dataProvider createUnknownTypeDataProvider
     *
     * @param string $fixtureName
     * @param string $contentType
     *
     * @throws InternetMediaTypeParseException
     */
    public function testCreateUnknownType($fixtureName, $contentType)
    {
        $response = ResponseFactory::createFromFixture($fixtureName, $contentType);

        $this->expectException(\RuntimeException::class);

        $this->factory->create($response, UriFactory::create('http://example.com'));
    }

    /**
     * @return array
     */
    public function createUnknownTypeDataProvider()
    {
        return [
            'html document' => [
                'fixtureName' => 'Html/empty-document.html',
                'contentType' => ResponseFactory::CONTENT_TYPE_HTML,
            ],
            'plain text' => [
                'fixtureName' => 'Sitemap/plain.txt',
                'contentType' => ResponseFactory::CONTENT_TYPE_TXT,
            ],
            'invalid xml' => [
                'fixtureName' => 'Sitemap/sitemap.invalid.xml',
                'contentType' => ResponseFactory::CONTENT_TYPE_XML,
            ],
            'empty xml' => [
                'fixtureName' => null,
                'contentType' => ResponseFactory::CONTENT_TYPE_XML,
            ],
            'xml no namespace' => [
                'fixtureName' => 'Sitemap/sitemap.no-namespace.xml',
                'contentType' => ResponseFactory::CONTENT_TYPE_XML,
            ],
        ];
    }

    /**
     * @dataProvider createDataProvider
     *
     * @param string $fixtureName
     * @param string $contentType
     * @param string $expectedType
     *
     * @throws InternetMediaTypeParseException
     */
    public function testCreate($fixtureName, $contentType, $expectedType)
    {
        $response = ResponseFactory::createFromFixture($fixtureName, $contentType);

        $sitemap = $this->factory->create($response, UriFactory::create('http://example.com'));

        $this->assertInstanceOf(Sitemap::class, $sitemap);
        $this->assertEquals($expectedType, $sitemap->getType());
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [
            'atom' => [
                'fixtureName' => 'Sitemap/atom.xml',
                'contentType' => ResponseFactory::CONTENT_TYPE_ATOM,
                'expectedType' => SitemapInterface::TYPE_ATOM,
            ],
            'rss' => [
                'fixtureName' => 'Sitemap/rss.xml',
                'contentType' => ResponseFactory::CONTENT_TYPE_RSS,
                'expectedType' => SitemapInterface::TYPE_RSS,
            ],
            'sitemaps org xml' => [
                'fixtureName' => 'Sitemap/sitemap.xml',
                'contentType' => ResponseFactory::CONTENT_TYPE_XML,
                'expectedType' => SitemapInterface::TYPE_SITEMAPS_ORG_XML,
                'expectedUrls' => [
                    'http://example.com/xml/1',
                    'http://example.com/xml/2',
                    'http://example.com/xml/3',
                ],
            ],
            'sitemaps org txt' => [
                'fixtureName' => 'Sitemap/sitemap.txt',
                'contentType' => ResponseFactory::CONTENT_TYPE_TXT,
                'expectedType' => SitemapInterface::TYPE_SITEMAPS_ORG_TXT,
                'expectedUrls' => [
                    'http://example.com/txt/1',
                    'http://example.com/txt/2',
                    'http://example.com/txt/3',
                ],
            ],
            'sitemaps org xml index' => [
                'fixtureName' => 'Sitemap/sitemap.index.xml',
                'contentType' => ResponseFactory::CONTENT_TYPE_XML,
                'expectedType' => SitemapInterface::TYPE_SITEMAPS_ORG_XML_INDEX,
                'expectedUrls' => [
                    'http://www.example.com/sitemap1.xml',
                    'http://www.example.com/sitemap2.xml',
                    'http://www.example.com/sitemap3.xml',
                ],
            ],
        ];
    }
}
