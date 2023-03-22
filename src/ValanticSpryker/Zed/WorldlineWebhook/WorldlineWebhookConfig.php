<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook;

use ValanticSpryker\Shared\WorldlineWebhook\WorldlineWebhookConstants;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class WorldlineWebhookConfig extends AbstractBundleConfig
{
    /**
     * @var string
     */
    public const ROUTE_PREFIX_WORLDLINE_WEBHOOK_REST = '/rest/worldline/webhooks';

    /**
     * @var int
     */
    public const HTTP_CODE_SUCCESS = 200;

    /**
     * @var int
     */
    public const HTTP_CODE_INTERNAL_ERROR = 500;

    /**
     * @var int
     */
    public const HTTP_CODE_BAD_REQUEST = 400;

    /**
     * @var int
     */
    public const HTTP_CODE_VALIDATION_ERRORS = 422;

    /**
     * @var int
     */
    public const HTTP_CODE_NO_CONTENT = 204;
    /**
     * @var string
     */
    public const HEADER_X_GCS_WEBHOOKS_ENDPOINT_VERIFICATION = 'X-GCS-Webhooks-Endpoint-Verification';

    /**
     * @var string
     */
    public const HEADER_X_GCS_SIGNATURE = 'X-GCS-Signature';

    /**
     * @var string
     */
    public const HEADER_X_GCS_KEYID = 'X-GCS-KeyId';

    /**
     * @var string
     */
    public const EVENT_ROUTING_KEY_ERROR = 'error';

    /**
     * @return int
     */
    public function getEventQueueChunkSize(): int
    {
        return WorldlineWebhookConstants::QUEUE_CHUNK_SIZE;
    }

    /**
     * @return bool
     */
    public function isWebhookEnabled(): bool
    {
        return $this->get(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_ENABLED, true);
    }

    /**
     * @return bool
     */
    public function isWebhookDebugEnabled(): bool
    {
        return $this->get(WorldlineWebhookConstants::WORLDLINE_WEBHOOK_DEBUG_ENABLED, false);
    }
}
