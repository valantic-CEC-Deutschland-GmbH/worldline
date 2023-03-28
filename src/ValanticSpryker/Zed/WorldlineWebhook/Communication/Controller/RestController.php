<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Communication\Controller;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;
use Generated\Shared\Transfer\WorldlineWebhookResponseTransfer;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Zed\Api\Communication\Controller\AbstractApiController;

/**
 * @method \ValanticSpryker\Zed\WorldlineWebhook\Business\WorldlineWebhookFacadeInterface getFacade()
 * @method \ValanticSpryker\Zed\WorldlineWebhook\Communication\WorldlineWebhookCommunicationFactory getFactory()
 */
class RestController extends AbstractApiController
{
    use LoggerTrait;

    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer $apiRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineWebhookResponseTransfer
     */
    public function indexAction(WorldlineWebhookRequestTransfer $apiRequestTransfer): WorldlineWebhookResponseTransfer
    {
        $this->getLogger()->debug('Received webhook event');

        return $this->getFacade()->dispatchWebhookEvent($apiRequestTransfer);
    }

    /**
     * @return void
     */
    public function deniedAction(): void
    {
    }
}
