<?php

namespace webignition\Tests\WebResource\Implementation;

use webignition\WebResource\SpecificContentTypeWebResource;

class AcceptAllSpecificContentTypeWebResource extends SpecificContentTypeWebResource
{
    /**
     * {@inheritdoc}
     */
    protected static function getAllowedContentTypeStrings()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected static function getAllowedContentTypePatterns()
    {
        return [];
    }
}
