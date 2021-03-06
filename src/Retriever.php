<?php

namespace webignition\WebResource;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use webignition\InternetMediaType\InternetMediaType;
use webignition\InternetMediaType\Parser\ParseException as InternetMediaTypeParseException;
use webignition\InternetMediaType\Parser\Parser as InternetMediaTypeParser;
use webignition\InternetMediaTypeInterface\InternetMediaTypeInterface;
use webignition\WebResource\Exception\Exception as WebResourceException;
use webignition\WebResource\Exception\InvalidContentTypeException;
use webignition\WebResource\WebPage\WebPage;
use webignition\WebResourceInterfaces\InvalidContentTypeExceptionInterface;
use webignition\WebResourceInterfaces\RetrieverExceptionInterface;
use webignition\WebResourceInterfaces\RetrieverInterface;
use webignition\WebResourceInterfaces\WebResourceInterface;

class Retriever implements RetrieverInterface
{
    const DEFAULT_WEB_RESOURCE_MODEL = WebResource::class;
    const DEFAULT_ALLOWED_CONTENT_TYPES = [];
    const DEFAULT_ALLOW_UNKNOWN_RESOURCE_TYPES = true;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var string[]
     */
    private $allowedContentTypes = self::DEFAULT_ALLOWED_CONTENT_TYPES;

    /**
     * @var bool
     */
    private $allowUnknownResourceTypes = self::DEFAULT_ALLOW_UNKNOWN_RESOURCE_TYPES;

    /**
     * @param HttpClient|null $httpClient
     * @param array $allowedContentTypes
     * @param bool $allowUnknownResourceTypes
     */
    public function __construct(
        HttpClient $httpClient = null,
        $allowedContentTypes = self::DEFAULT_ALLOWED_CONTENT_TYPES,
        $allowUnknownResourceTypes = self::DEFAULT_ALLOW_UNKNOWN_RESOURCE_TYPES
    ) {
        if (empty($httpClient)) {
            $httpClient = new HttpClient();
        }

        $this->setHttpClient($httpClient);
        $this->setAllowedContentTypes($allowedContentTypes);
        $this->setAllowUnknownResourceTypes($allowUnknownResourceTypes);
    }

    /**
     * @param HttpClient $httpClient
     */
    public function setHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowedContentTypes($allowedContentTypes = [])
    {
        $this->allowedContentTypes = $allowedContentTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowUnknownResourceTypes($allowUnknownResourceTypes = true)
    {
        $this->allowUnknownResourceTypes = $allowUnknownResourceTypes;
    }

    /**
     * @param RequestInterface $request
     *
     * @return WebResourceInterface
     *
     * A RetrieverExceptionInterface instance MUST be thrown when a resource
     * is retrieved with a status code other than 200.
     *
     * An InvalidContentTypeExceptionInterface instance MUST  be thrown when the content type of a resource
     * does not match one of those provided by setAllowedContentTypes() and when unknown resource types are not allowed.
     *
     * @throws RetrieverExceptionInterface for cases where a resource could not be retrieved
     * @throws InvalidContentTypeExceptionInterface for cases where the retrieved resource content type is not allowed
     * @throws InternetMediaTypeParseException
     */
    public function retrieve(RequestInterface $request)
    {
        if (!$this->allowUnknownResourceTypes) {
            $headRequest = clone $request;
            $headRequest->withMethod('HEAD');

            $this->preVerifyContentType($headRequest);
        }

        try {
            $response = $this->httpClient->send($request);
        } catch (BadResponseException $badResponseException) {
            $response = $badResponseException->getResponse();
        }

        if ($this->isBadResponse($response)) {
            throw new WebResourceException($response, $request);
        }

        if ($this->isInformationalResponse($response)) {
            throw new WebResourceException($response, $request);
        }

        if ($this->isRedirectResponse($response)) {
            // Shouldn't happen, HTTP client should have the redirect handler
            // enabled, redirects should be followed
            throw new WebResourceException($response, $request);
        }

        $modelClassName = $this->getModelClassNameFromContentTypeWithContentTypeVerification($request, $response);

        return new $modelClassName($response, $request->getUri());
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     *
     * @return string
     *
     * @throws InternetMediaTypeParseException
     * @throws InvalidContentTypeException
     */
    private function getModelClassNameFromContentTypeWithContentTypeVerification(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $contentType = $this->getContentTypeFromResponse($response);
        $modelClassName = $this->getModelClassNameForContentType($contentType);
        $hasContentTypeSpecificModel = $modelClassName !== WebResource::class;

        if (!$hasContentTypeSpecificModel && !$this->allowUnknownResourceTypes) {
            throw new InvalidContentTypeException($contentType, $response, $request);
        }

        return $modelClassName;
    }

    /**
     * @param InternetMediaTypeInterface $contentType
     *
     * @return string
     */
    private function getModelClassNameForContentType(InternetMediaTypeInterface $contentType)
    {
        if (WebPage::models($contentType)) {
            return WebPage::class;
        }

        if (JsonDocument::models($contentType)) {
            return JsonDocument::class;
        }

        return WebResource::class;
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool
     *
     * @throws InvalidContentTypeException
     * @throws InternetMediaTypeParseException
     */
    private function preVerifyContentType(RequestInterface $request)
    {
        try {
            $response = $this->httpClient->send($request);
        } catch (BadResponseException $badResponseException) {
            $response = $badResponseException->getResponse();
        }

        if (!$this->isSuccessResponse($response)) {
            return null;
        }

        $this->getModelClassNameFromContentTypeWithContentTypeVerification($request, $response);

        return true;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return InternetMediaType
     *
     * @throws InternetMediaTypeParseException
     */
    private function getContentTypeFromResponse(ResponseInterface $response)
    {
        $mediaTypeParser = new InternetMediaTypeParser();
        $mediaTypeParser->setAttemptToRecoverFromInvalidInternalCharacter(true);
        $mediaTypeParser->setIgnoreInvalidAttributes(true);

        $contentTypeHeader = $response->getHeader('content-type');
        $contentTypeString = empty($contentTypeHeader)
            ? ''
            : $contentTypeHeader[0];

        return $mediaTypeParser->parse($contentTypeString);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function isInformationalResponse(ResponseInterface $response)
    {
        return $response->getStatusCode() < 200;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function isRedirectResponse(ResponseInterface $response)
    {
        return $response->getStatusCode() >= 300 && $response->getStatusCode() < 400;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function isBadResponse(ResponseInterface $response)
    {
        return $response->getStatusCode() >= 400;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function isSuccessResponse(ResponseInterface $response)
    {
        return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
    }
}
