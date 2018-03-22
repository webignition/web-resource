<?php

namespace webignition\Tests\WebResource\Sitemap;

use webignition\InternetMediaType\Parser\ParseException as InternetMediaTypeParseException;
use webignition\Tests\WebResource\Factory\ResponseFactory;
use webignition\WebResource\Sitemap\Factory;

class GetUrlsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getUrlsDataProvider
     *
     * @param string $fixtureName
     * @param string $contentType
     * @param string[] $expectedUrls
     *
     * @throws InternetMediaTypeParseException
     */
    public function testGetUrls($fixtureName, $contentType, $expectedUrls)
    {
        $factory = new Factory();

        $response = ResponseFactory::createFromFixture($fixtureName, $contentType);

        $sitemap = $factory->create($response);

        $this->assertEquals($expectedUrls, $sitemap->getUrls());
    }

    /**
     * @return array
     */
    public function getUrlsDataProvider()
    {
        return [
            'atom' => [
                'fixtureName' => 'Sitemap/atom.xml',
                'contentType' => ResponseFactory::CONTENT_TYPE_ATOM,
                'expectedUrls' => [
                    'http://example.com/from-atom',
                ],
            ],
            'rss' => [
                'fixtureName' => 'Sitemap/rss.xml',
                'contentType' => ResponseFactory::CONTENT_TYPE_RSS,
                'expectedUrls' => [
                    'http://example.com/from-rss',
                ],
            ],
            'sitemaps org xml' => [
                'fixtureName' => 'Sitemap/sitemap.xml',
                'contentType' => ResponseFactory::CONTENT_TYPE_XML,
                'expectedUrls' => [
                    'http://example.com/xml/1',
                    'http://example.com/xml/2',
                    'http://example.com/xml/3',
                ],
            ],
            'sitemaps org xml v0.84' => [
                'fixtureName' => 'Sitemap/sitemap.0.84.xml',
                'contentType' => ResponseFactory::CONTENT_TYPE_XML,
                'expectedUrls' => [
                    'http://example.com/xml/1',
                    'http://example.com/xml/2',
                    'http://example.com/xml/3',
                ],
            ],
            'sitemaps org txt' => [
                'fixtureName' => 'Sitemap/sitemap.txt',
                'contentType' => ResponseFactory::CONTENT_TYPE_TXT,
                'expectedUrls' => [
                    'http://example.com/txt/1',
                    'http://example.com/txt/2',
                    'http://example.com/txt/3',
                ],
            ],
            'sitemaps org xml index' => [
                'fixtureName' => 'Sitemap/sitemap.index.xml',
                'contentType' => ResponseFactory::CONTENT_TYPE_XML,
                'expectedUrls' => [
                    'http://www.example.com/sitemap1.xml',
                    'http://www.example.com/sitemap2.xml',
                    'http://www.example.com/sitemap3.xml',
                ],
            ],
            'sitemaps org xml index google.com v0.84' => [
                'fixtureName' => 'Sitemap/sitemap.index.google.com.0.84.xml',
                'contentType' => ResponseFactory::CONTENT_TYPE_XML,
                'expectedUrls' => [
                    'http://www.example.com/sitemap1.xml',
                    'http://www.example.com/sitemap2.xml',
                    'http://www.example.com/sitemap3.xml',
                ],
            ],
            'parent location urls only' => [
                'fixtureName' => 'Sitemap/sitemap.with-images.xml',
                'contentType' => ResponseFactory::CONTENT_TYPE_XML,
                'expectedUrls' => [
                    'http://example.com/xml-with-images/1',
                    'http://example.com/xml-with-images/2',
                    'http://example.com/xml-with-images/3',
                ],
            ],
        ];
    }
}
