<?php

namespace webignition\Tests\WebResource\Implementation;

use webignition\InternetMediaType\InternetMediaType;

class AcceptStringOnlySpecificContentTypeWebResourceTest extends AbstractSpecificContentTypeWebResourceTest
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

        $this->assertEquals($expectedModels, AcceptStringOnlyContentTypeWebResource::models($contentType));
    }

    /**
     * @return array
     */
    public function modelsDataProvider()
    {
        return array_merge_recursive(parent::modelsDataProvider(), [
            'text/plain' => [
                'expectedModels' => true,
            ],
            'text/html' => [
                'expectedModels' => true,
            ],
            'application/xml' => [
                'expectedModels' => false,
            ],
            'application/json' => [
                'expectedModels' => false,
            ],
            'image/png' => [
                'expectedModels' => false,
            ],
        ]);
    }
}
