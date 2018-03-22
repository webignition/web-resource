<?php

namespace webignition\Tests\WebResource\Implementation;

abstract class AbstractSpecificContentTypeWebResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function modelsDataProvider()
    {
        return [
            'text/plain' => [
                'contentTypeType' => 'text',
                'contentTypeSubtype' => 'plain',
            ],
            'text/html' => [
                'contentTypeType' => 'text',
                'contentTypeSubtype' => 'html',
            ],
            'application/xml' => [
                'contentTypeType' => 'application',
                'contentTypeSubtype' => 'xml',
            ],
            'application/json' => [
                'contentTypeType' => 'application',
                'contentTypeSubtype' => 'json',
            ],
            'image/png' => [
                'contentTypeType' => 'image',
                'contentTypeSubtype' => 'png',
            ],
        ];
    }
}
