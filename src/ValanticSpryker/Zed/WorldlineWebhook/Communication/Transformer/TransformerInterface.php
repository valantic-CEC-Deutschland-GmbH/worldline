<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Communication\Transformer;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;
use Generated\Shared\Transfer\WorldlineWebhookResponseTransfer;
use Symfony\Component\HttpFoundation\Response;

interface TransformerInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer $worldlineWebhookRequestTransfer
     * @param \Generated\Shared\Transfer\WorldlineWebhookResponseTransfer $worldlineWebhookResponseTransfer
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function transform(WorldlineWebhookRequestTransfer $worldlineWebhookRequestTransfer, WorldlineWebhookResponseTransfer $worldlineWebhookResponseTransfer, Response $response): Response;

    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookResponseTransfer $worldlineWebhookResponseTransfer
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param string $message
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function transformBadRequest(WorldlineWebhookResponseTransfer $worldlineWebhookResponseTransfer, Response $response, string $message): Response;
}
