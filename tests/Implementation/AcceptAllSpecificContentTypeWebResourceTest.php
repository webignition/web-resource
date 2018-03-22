<?php

namespace webignition\Tests\WebResource\Implementation;

use webignition\InternetMediaType\InternetMediaType;

class AcceptAllSpecificContentTypeWebResourceTest extends AbstractSpecificContentTypeWebResourceTest
{
    /**
     * @dataProvider modelsDataProvider
     *
     * @param string $contentTypeType
     * @param string $contentTypeSubtype
     */
    public function testModels($contentTypeType, $contentTypeSubtype)
    {
        $contentType = new InternetMediaType();
        $contentType->setType($contentTypeType);
        $contentType->setSubtype($contentTypeSubtype);

        $this->assertTrue(AcceptAllSpecificContentTypeWebResource::models($contentType));
    }
}
