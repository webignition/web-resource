<?php

namespace webignition\Tests\WebResource\Factory;

use Psr\Http\Message\UriInterface;
use webignition\InternetMediaType\Parser\ParseException as InternetMediaTypeParseException;
use webignition\WebResource\Sitemap\Factory;
use webignition\WebResourceInterfaces\SitemapInterface;

class SitemapHelper
{
    /**
     * @param UriInterface $uri
     *
     * @return SitemapInterface
     *
     * @throws InternetMediaTypeParseException
     */
    public static function createXmlIndexSitemap(UriInterface $uri = null)
    {
        $response = ResponseFactory::createFromFixture('Sitemap/sitemap.index.xml', 'text/xml');

        $factory = new Factory();

        return $factory->create($response, $uri);
    }

    /**
     * @param string $fixtureName
     * @param UriInterface $uri
     *
     * @return SitemapInterface
     *
     * @throws InternetMediaTypeParseException
     */
    public static function createXmlSitemap($fixtureName, UriInterface $uri = null)
    {
        $response = ResponseFactory::createFromFixture($fixtureName, 'text/xml');

        $factory = new Factory();

        return $factory->create($response, $uri);
    }
}
