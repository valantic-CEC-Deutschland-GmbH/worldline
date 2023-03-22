<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\EventProcessor;

use Generated\Shared\Transfer\WebhookEventTransfer;
use ValanticSpryker\Zed\Worldline\Business\Writer\WorldlineWriterInterface;
use Spryker\Shared\Log\LoggerTrait;

class TokenEventProcessor implements EventProcessorInterface
{
    use LoggerTrait;

    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Writer\WorldlineWriterInterface $worldlineWriter
     */
    public function __construct(private WorldlineWriterInterface $worldlineWriter)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\WebhookEventTransfer $eventTransfer
     *
     * @return void
     */
    public function processEvent(WebhookEventTransfer $eventTransfer): void
    {
        if (!$eventTransfer->getToken()) {
            return;
        }

        switch ($eventTransfer->getType()) {
            case 'token.created':
                $this->getLogger()->debug('ignoring token created webhook event');

                return;
            case 'token.updated':
                $this->getLogger()->debug('updating token because of webhook event');
                $this->worldlineWriter->updateWorldlineTokenByEvent($eventTransfer);

                break;
            case 'token.expired':
                $this->getLogger()->debug('set token expired because of webhook event');
                $this->worldlineWriter->setTokenExpiredByEvent($eventTransfer);

                break;
            case 'token.deleted':
                $this->getLogger()->debug('mark token as deleted because of webhook event');
                $this->worldlineWriter->markTokenDeletedByEvent($eventTransfer);

                break;
            default:
                break;
        }
    }
}
