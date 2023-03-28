<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Communication\Formatter;

use Spryker\Service\UtilEncoding\Model\Json;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;

class JsonFormatter implements FormatterInterface
{
    /**
     * @var \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface
     */
    protected $utilEncodingService;

    /**
     * @param \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(UtilEncodingServiceInterface $utilEncodingService)
    {
        $this->utilEncodingService = $utilEncodingService;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function format($value): string
    {
        $options = Json::DEFAULT_OPTIONS | JSON_PRETTY_PRINT;

        return $this->utilEncodingService->encodeJson($value, $options) ?? '';
    }
}
