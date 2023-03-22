<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\EventProcessor;

use Generated\Shared\Transfer\WebhookEventTransfer;
use Spryker\Shared\Log\LoggerTrait;
use ValanticSpryker\Zed\Worldline\Business\Writer\WorldlineWriterInterface;

class PaymentEventProcessor implements EventProcessorInterface
{
    use LoggerTrait;

    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\Writer\WorldlineWriterInterface $worldlineWriter
     */
    public function __construct(private WorldlineWriterInterface $worldlineWriter)
    {
    }

    /**
     * @inheritDoc
     */
    public function processEvent(WebhookEventTransfer $eventTransfer): void
    {
        if (!$eventTransfer->getPayment()) {
            return;
        }

        $this->getLogger()->debug('saving payment webhook event');
        $this->worldlineWriter->savePaymentEvent($eventTransfer);
    }
}
