<?php

namespace webignition\Tests\WebResource\Factory;

use Mockery;
use Mockery\Mock;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use webignition\WebResource\WebResource;

class ResponseFactory
{
    const CONTENT_TYPE_ATOM = 'application/atom+xml';
    const CONTENT_TYPE_RSS = 'application/rss+xml';
    const CONTENT_TYPE_XML = 'text/xml';
    const CONTENT_TYPE_TXT = 'text/plain';
    const CONTENT_TYPE_HTML = 'text/html';

    /**
     * @param string $fixtureName
     * @param string $contentType
     *
     * @return Mock|ResponseInterface
     */
    public static function createFromFixture($fixtureName, $contentType = self::CONTENT_TYPE_HTML)
    {
        return self::create($contentType, FixtureLoader::load($fixtureName));
    }

    /**
     * @param string $contentType
     * @param string $content
     * @param StreamInterface|null $bodyStream
     *
     * @return Mock|ResponseInterface
     */
    public static function create($contentType = self::CONTENT_TYPE_HTML, $content = '', $bodyStream = null)
    {
        /* @var ResponseInterface|Mock $response */
        $response = Mockery::mock(ResponseInterface::class);

        $response
            ->shouldReceive('getHeader')
            ->with(WebResource::HEADER_CONTENT_TYPE)
            ->andReturn([
                $contentType,
            ]);

        if (empty($bodyStream)) {
            /* @var StreamInterface|Mock $bodyStream */
            $bodyStream = Mockery::mock(StreamInterface::class);
            $bodyStream
                ->shouldReceive('__toString')
                ->andReturn($content);
        }

        $response
            ->shouldReceive('getBody')
            ->andReturn($bodyStream);

        return $response;
    }
}
