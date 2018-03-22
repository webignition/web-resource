<?php

namespace webignition\Tests\WebResource\Sitemap;

use webignition\InternetMediaType\Parser\ParseException as InternetMediaTypeParseException;
use webignition\Tests\WebResource\Factory\SitemapHelper;
use webignition\Tests\WebResource\Factory\UriFactory;
use webignition\WebResource\Sitemap\Factory;

class AddChildTest extends \PHPUnit_Framework_TestCase
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
        $this->factory = new Factory();
    }

    /**
     * @throws InternetMediaTypeParseException
     */
    public function testAddChildToNonIndexSitemap()
    {
        $sitemap = SitemapHelper::createXmlSitemap('Sitemap/sitemap.xml');
        $childSitemap = SitemapHelper::createXmlSitemap('Sitemap/sitemap.xml');

        $this->assertFalse($sitemap->addChild($childSitemap));
    }

    /**
     * @throws InternetMediaTypeParseException
     */
    public function testAddChildToIndexSitemap()
    {
        $sitemap = SitemapHelper::createXmlIndexSitemap();
        $childSitemap = SitemapHelper::createXmlSitemap('Sitemap/sitemap.xml');

        $this->assertTrue($sitemap->addChild($childSitemap));
    }

    /**
     * @throws InternetMediaTypeParseException
     */
    public function testAddingChildIsIdempotent()
    {
        $sitemap = SitemapHelper::createXmlIndexSitemap();
        $childSitemap = SitemapHelper::createXmlSitemap('Sitemap/sitemap.index.xml');

        $this->assertTrue($sitemap->addChild($childSitemap));
        $this->assertCount(1, $sitemap->getChildren());

        $this->assertTrue($sitemap->addChild($childSitemap));
        $this->assertCount(1, $sitemap->getChildren());
    }

    /**
     * @throws InternetMediaTypeParseException
     */
    public function testAddingMultipleChildren()
    {
        $sitemap = SitemapHelper::createXmlIndexSitemap();

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

        $this->assertTrue($sitemap->addChild($childSitemap1));
        $this->assertTrue($sitemap->addChild($childSitemap2));
        $this->assertTrue($sitemap->addChild($childSitemap3));

        $this->assertCount(3, $sitemap->getChildren());
    }
}
