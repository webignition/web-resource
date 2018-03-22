<?php

namespace webignition\Tests\WebResource\Sitemap;

use webignition\InternetMediaType\Parser\ParseException as InternetMediaTypeParseException;
use webignition\Tests\WebResource\Factory\SitemapHelper;
use webignition\Tests\WebResource\Factory\UriFactory;

class GetUrlsFromChildrenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws InternetMediaTypeParseException
     */
    public function testGettingUrlsFromChildren()
    {
        $sitemap = SitemapHelper::createXmlIndexSitemap(UriFactory::create('http://example.com/sitemap_index.xml'));

        $childSitemap1 = SitemapHelper::createXmlSitemap(
            'Sitemap/example.com.sitemap.01.xml',
            UriFactory::create('http://example.com/sitemap1.xml')
        );

        $childSitemap2 = SitemapHelper::createXmlSitemap(
            'Sitemap/example.com.sitemap.02.xml',
            UriFactory::create('http://example.com/sitemap2.xml')
        );

        $childSitemap3 = SitemapHelper::createXmlSitemap(
            'Sitemap/example.com.sitemap.03.xml',
            UriFactory::create('http://example.com/sitemap3.xml')
        );

        $sitemap->addChild($childSitemap1);
        $sitemap->addChild($childSitemap2);
        $sitemap->addChild($childSitemap3);

        $urls = [];
        foreach ($sitemap->getChildren() as $childSitemap) {
            $urls = array_merge($urls, $childSitemap->getUrls());
        }

        $this->assertCount(9, $urls);
    }
}
