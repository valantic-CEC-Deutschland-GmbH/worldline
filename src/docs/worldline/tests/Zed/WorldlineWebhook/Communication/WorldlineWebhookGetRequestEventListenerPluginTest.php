<?php

declare(strict_types = 1);

namespace ValanticSprykerTest\Zed\WorldlineWebhook\Communication;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;
use Generated\Shared\Transfer\WorldlineWebhookResponseTransfer;
use ValanticSpryker\Zed\WorldlineWebhook\Communication\Plugin\EventListener\WorldlineWebhookGetRequestEventListenerPlugin;
use ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookConfig;
use ValanticSprykerTest\Shared\Base\AbstractTest;
use ValanticSprykerTest\Zed\WorldlineWebhook\WorldlineWebhookCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group ValanticSprykerTest
 * @group Zed
 * @group WorldlineWebhook
 * @group Communication
 * @group WorldlineWebhookGetRequestEventListenerPluginTest
 * Add your own group annotations below this line
 * @group Worldline
 */
class WorldlineWebhookGetRequestEventListenerPluginTest extends AbstractTest
{
    protected WorldlineWebhookCommunicationTester $tester;

    public function testGetRequestIsAnsweredCorrectly()
    {
        // Arrange
        $apiRequestTransfer = new WorldlineWebhookRequestTransfer();
        $headers = [
            mb_strtolower(WorldlineWebhookConfig::HEADER_X_GCS_WEBHOOKS_ENDPOINT_VERIFICATION) => ['some thing to return'],
        ];
        $apiRequestTransfer->setHeaderData($headers);

        $apiRequestTransfer->setRequestData([]);
        $apiRequestTransfer->setRequestType('GET');

        $apiResponseTransfer = new WorldlineWebhookResponseTransfer();
        $this->tester->cleanupInMemoryQueue();

        // ACT
        $apiResponseTransfer = (new WorldlineWebhookGetRequestEventListenerPlugin())->handleEvent($apiRequestTransfer, $apiResponseTransfer);

        // Assert
        self::assertSame('some thing to return', $apiResponseTransfer->getData()[0]);
    }
}
