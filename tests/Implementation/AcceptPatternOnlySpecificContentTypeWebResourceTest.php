<?php

namespace webignition\Tests\WebResource\Implementation;

use webignition\InternetMediaType\InternetMediaType;

class AcceptPatternOnlySpecificContentTypeWebResourceTest extends AbstractSpecificContentTypeWebResourceTest
{
    /**
     * @dataProvider modelsDataProvider
     *
     * @param string $contentTypeType
     * @param string $contentTypeSubtype
     * @param bool $expectedModels
     */
    public function testModels($contentTypeType, $contentTypeSubtype, $expectedModels)
    {
        $contentType = new InternetMediaType();
        $contentType->setType($contentTypeType);
        $contentType->setSubtype($contentTypeSubtype);

        $this->assertEquals($expectedModels, AcceptPatternOnlyContentTypeWebResource::models($contentType));
    }

    /**
     * @return array
     */
    public function modelsDataProvider()
    {
        return array_merge_recursive(parent::modelsDataProvider(), [
            'text/plain' => [
                'expectedModels' => false,
            ],
            'text/html' => [
                'expectedModels' => false,
            ],
            'application/xml' => [
                'expectedModels' => true,
            ],
            'application/json' => [
                'expectedModels' => true,
            ],
            'image/png' => [
                'expectedModels' => false,
            ],
        ]);
    }
}
