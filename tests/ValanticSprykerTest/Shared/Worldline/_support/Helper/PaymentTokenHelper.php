<?php

declare(strict_types = 1);

namespace ValanticSprykerTest\Shared\Worldline\Helper;

use Codeception\Module;
use Generated\Shared\DataBuilder\WorldlineCreditCardTokenBuilder;
use Generated\Shared\DataBuilder\WorldlineThreeDSecureDataBuilder;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer;
use Orm\Zed\Worldline\Persistence\VsyWorldlineThreeDSecureResult;
use Orm\Zed\Worldline\Persistence\VsyWorldlineThreeDSecureResultQuery;
use Orm\Zed\Worldline\Persistence\VsyWorldlineToken;
use Orm\Zed\Worldline\Persistence\VsyWorldlineTokenQuery;
use SprykerTest\Shared\Testify\Helper\DataCleanupHelperTrait;
use SprykerTest\Shared\Testify\Helper\DependencyHelperTrait;
use SprykerTest\Shared\Testify\Helper\LocatorHelperTrait;

class PaymentTokenHelper extends Module
{
    use DependencyHelperTrait;
    use LocatorHelperTrait;
    use DataCleanupHelperTrait;

    public function havePaymentTokenWithThreeDSecureDataForCustomer(CustomerTransfer $customerTransfer, array $seed = []): WorldlineCreditCardTokenTransfer
    {
        $threeDSecureData = (new WorldlineThreeDSecureDataBuilder())->build();

        $vsyWorldlineThreeDSecureEntity = new VsyWorldlineThreeDSecureResult();
        $vsyWorldlineThreeDSecureEntity->fromArray($threeDSecureData->toArray());

        $vsyWorldlineThreeDSecureEntity->save();

        $tokenTransfer = (new WorldlineCreditCardTokenBuilder())->seed($seed)->build();

        $tokenTransfer->setFkCustomer($customerTransfer->getIdCustomer());
        $tokenTransfer->setFkInitialThreeDSecureResult($vsyWorldlineThreeDSecureEntity->getIdThreeDSecureResult());

        $vsyWorldlineTokenEntity = new VsyWorldlineToken();
        $vsyWorldlineTokenEntity->fromArray($tokenTransfer->toArray());
        $vsyWorldlineTokenEntity->save();

        $tokenTransfer->setIdToken($vsyWorldlineTokenEntity->getIdToken());

        $this->getDataCleanupHelper()->_addCleanup(function () use ($tokenTransfer): void {
            $this->debug(sprintf('Deleting worldline token data: %s', $tokenTransfer->getToken()));

            VsyWorldlineThreeDSecureResultQuery::create()->filterByIdThreeDSecureResult($tokenTransfer->getFkInitialThreeDSecureResult())->find()->delete();
            VsyWorldlineTokenQuery::create()->filterByIdToken($tokenTransfer->getIdToken())->find()->delete();
        });

        return $tokenTransfer;
    }
}
