<?php

declare(strict_types = 1);

namespace ValanticSprykerTest\Shared\Worldline\Helper;

use Codeception\Module;
use Generated\Shared\DataBuilder\PaymentWorldlineBuilder;
use Generated\Shared\DataBuilder\PaymentWorldlineTransactionStatusBuilder;
use Generated\Shared\DataBuilder\WorldlinePaymentHostedCheckoutBuilder;
use Generated\Shared\Transfer\PaymentWorldlineTransactionStatusTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\WorldlinePaymentHostedCheckoutTransfer;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineHostedCheckoutQuery;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineQuery;
use Orm\Zed\Worldline\Persistence\VsyPaymentWorldlineTransactionStatusLogQuery;
use Orm\Zed\Worldline\Persistence\VsyWorldlineApiLog;
use Orm\Zed\Worldline\Persistence\VsyWorldlineApiLogQuery;
use ValanticSpryker\Zed\Worldline\Business\WorldlineFacade;
use ValanticSpryker\Zed\Worldline\Business\WorldlineFacadeInterface;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineEntityManager;
use SprykerTest\Shared\Testify\Helper\DataCleanupHelperTrait;
use SprykerTest\Shared\Testify\Helper\DependencyHelperTrait;
use SprykerTest\Shared\Testify\Helper\LocatorHelperTrait;

class HostedCheckoutHelper extends Module
{
    use DependencyHelperTrait;
    use LocatorHelperTrait;
    use DataCleanupHelperTrait;

    /**
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param array $override
     *
     * @return \Generated\Shared\Transfer\PaymentWorldlineTransfer
     */
    public function haveHostedCheckout(
        SaveOrderTransfer $saveOrderTransfer,
        QuoteTransfer $quoteTransfer,
        array $paymentWorldlineOverride = [],
        array $paymentWorldlineHostedCheckoutOverride = [
            PaymentWorldlineTransactionStatusTransfer::FK_WORLDLINE_API_LOG => null,
            PaymentWorldlineTransactionStatusTransfer::FK_WORLDLINE_REST_LOG => null,
        ],
        array $paymentWorldlineTransactionStatusLogOverride = []
    ): PaymentWorldlineTransfer {
        $paymentWorldlineTransfer = (new PaymentWorldlineBuilder())->seed($paymentWorldlineOverride)->build();

        $paymentWorldlineHostedCheckoutTransfer = (new WorldlinePaymentHostedCheckoutBuilder())->seed($paymentWorldlineHostedCheckoutOverride)->build();

        $paymentWorldlineTransfer->setMerchantReference($saveOrderTransfer->getOrderReference());
        $paymentWorldlineTransfer->setPaymentHostedCheckout($paymentWorldlineHostedCheckoutTransfer);
        $quoteTransfer->getPayments()->offsetGet(0)->setPaymentWorldline($paymentWorldlineTransfer);

        $this->createWorldlineFacade()->saveOrderPayment($quoteTransfer, $saveOrderTransfer);

        $entity = VsyPaymentWorldlineQuery::create()->findOneByFkSalesOrder($saveOrderTransfer->getIdSalesOrder());

        $hostedCheckoutEntity = $entity->getVsyPaymentWorldlineHostedCheckout();

        $paymentWorldlineTransfer
            ->fromArray($entity->toArray(), true);

        $paymentWorldlineHostedCheckoutTransfer->fromArray($hostedCheckoutEntity->toArray(), true);

        if (empty($paymentWorldlineTransactionStatusLogOverride)) {
            $paymentWorldlineTransactionStatusLogOverride = [
                PaymentWorldlineTransactionStatusTransfer::FK_WORLDLINE_API_LOG => null,
                PaymentWorldlineTransactionStatusTransfer::FK_WORLDLINE_REST_LOG => null,
            ];
        }
        $paymentWorldlineTransactionStatusLogTransfer = (new PaymentWorldlineTransactionStatusBuilder())->seed($paymentWorldlineTransactionStatusLogOverride)->build();
        $paymentWorldlineTransactionStatusLogTransfer->setFkPaymentWorldline($entity->getIdPaymentWorldline());

        $paymentWorldlineTransactionStatusLogTransfer = (new WorldlineEntityManager())->savePaymentWorldlineTransactionStatus($paymentWorldlineTransactionStatusLogTransfer);


        $this->getDataCleanupHelper()->_addCleanup(function () use ($paymentWorldlineTransfer): void {
            $this->debug(sprintf('Deleting hosted checkout data: %s', $paymentWorldlineTransfer->getFkSalesOrder()));

            VsyPaymentWorldlineHostedCheckoutQuery::create()->filterByFkPaymentWorldline($paymentWorldlineTransfer->getIdPaymentWorldline())->find()->delete();
            VsyWorldlineApiLogQuery::create()->filterByFkPaymentWorldline($paymentWorldlineTransfer->getIdPaymentWorldline())->find()->delete();
            VsyPaymentWorldlineQuery::create()->filterByFkSalesOrder($paymentWorldlineTransfer->getFkSalesOrder())->find()->delete();

            VsyPaymentWorldlineTransactionStatusLogQuery::create()
                ->filterByFkPaymentWorldline($paymentWorldlineTransfer->getIdPaymentWorldline())
                ->find()
                ->delete();
        });

        return $paymentWorldlineTransfer;
    }

    private function createWorldlineFacade(): WorldlineFacadeInterface
    {
        return new WorldlineFacade();
    }
}
