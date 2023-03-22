<?php

declare(strict_types = 1);

namespace ValanticSpryker\Shared\WorldlineWebhook;

interface WorldlineWebhookConstants
{
    /**
     * @var int
     */
    public const QUEUE_CHUNK_SIZE = 127;

    /**
     * @var string
     */
    public const WORLDLINE_WEBHOOK_EVENT_QUEUE_NAME = 'worldline_webhook.events';

    /**
     * @var string
     */
    public const WORLDLINE_WEBHOOK_EVENT_QUEUE_ERROR = 'worldline_webhook.events.error';

    /**
     * @var string
     */
    public const WORLDLINE_WEBHOOK_ENABLED = 'WORLDLINE_WEBHOOK_ENABLED';

    /**
     * @var string
     */
    public const WORLDLINE_WEBHOOK_DEBUG_ENABLED = 'WORLDLINE_WEBHOOK_DEBUG_ENABLED';
    /**
     * @var string
     */
    public const HEADER_CONTENT_TYPE = 'Content-Type';
}
