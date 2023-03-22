<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Communication\Transformer;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;
use Generated\Shared\Transfer\WorldlineWebhookResponseTransfer;
use ValanticSpryker\Shared\WorldlineWebhook\WorldlineWebhookConstants;
use ValanticSpryker\Zed\WorldlineWebhook\Communication\Formatter\FormatterInterface;
use ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookConfig;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class Transformer implements TransformerInterface
{
    /**
     * @param \ValanticSpryker\Zed\WorldlineWebhook\Communication\Formatter\FormatterInterface $formatter
     * @param \ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookConfig $apiConfig
     * @param \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(private FormatterInterface $formatter, private WorldlineWebhookConfig $apiConfig, private UtilEncodingServiceInterface $utilEncodingService)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer $apiRequestTransfer
     * @param \Generated\Shared\Transfer\WorldlineWebhookResponseTransfer $apiResponseTransfer
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function transform(WorldlineWebhookRequestTransfer $apiRequestTransfer, WorldlineWebhookResponseTransfer $apiResponseTransfer, Response $response): Response
    {
        $headers = $apiResponseTransfer->getHeaders() + $this->getDefaultResponseHeaders($apiRequestTransfer);
        $response->headers->add($headers);

        $response->setStatusCode($apiResponseTransfer->getCodeOrFail());

        return $this->addResponseContent($apiRequestTransfer, $apiResponseTransfer, $response);
    }

    /**
     * @inheritDoc
     */
    public function transformBadRequest(WorldlineWebhookResponseTransfer $apiResponseTransfer, Response $response, string $message): Response
    {
        $headers = $apiResponseTransfer->getHeaders() + $this->getDefaultResponseHeaders();
        $response->headers->add($headers);

        $response->setStatusCode(WorldlineWebhookConfig::HTTP_CODE_BAD_REQUEST);
        $response->setContent($this->utilEncodingService->encodeJson([
            'code' => WorldlineWebhookConfig::HTTP_CODE_BAD_REQUEST,
            'message' => $message,
        ]));

        return $response;
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer|null $apiRequestTransfer
     *
     * @return array<string, string>
     */
    protected function getDefaultResponseHeaders(?WorldlineWebhookRequestTransfer $apiRequestTransfer = null): array
    {
        return [
            WorldlineWebhookConstants::HEADER_CONTENT_TYPE => $this->createContentTypeHeader($apiRequestTransfer),
        ];
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer $apiRequestTransfer
     * @param \Generated\Shared\Transfer\WorldlineWebhookResponseTransfer $apiResponseTransfer
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function addResponseContent(WorldlineWebhookRequestTransfer $apiRequestTransfer, WorldlineWebhookResponseTransfer $apiResponseTransfer, Response $response): Response
    {
        if ($this->isContentless($apiResponseTransfer)) {
            return $response;
        }

        $content = [];
        $content['code'] = $apiResponseTransfer->getCode();

        $result = $apiResponseTransfer->getData();
        if ($result !== null) {
            $content['data'] = $result;
        }

        if ($this->apiConfig->isWebhookDebugEnabled()) {
            $content['_request'] = $apiRequestTransfer->toArray();
        } else {
            $content = $result;
        }

        if ($apiResponseTransfer->getIsPlainText()) {
            $content = $result[0];
        } else {
            $content = $this->formatter
                ->format($content);
        }

        $response->setContent($content);

        return $response;
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer|null $apiRequestTransfer
     *
     * @return string
     */
    private function createContentTypeHeader(?WorldlineWebhookRequestTransfer $apiRequestTransfer): string
    {
        $formatType = $apiRequestTransfer && $apiRequestTransfer->getFormatType() ? $apiRequestTransfer->getFormatType() : 'json';

        return sprintf('application/%s', $formatType);
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookResponseTransfer $apiResponseTransfer
     *
     * @return bool
     */
    private function isContentless(WorldlineWebhookResponseTransfer $apiResponseTransfer): bool
    {
        return (int)$apiResponseTransfer->getCode() === WorldlineWebhookConfig::HTTP_CODE_NO_CONTENT;
    }
}
