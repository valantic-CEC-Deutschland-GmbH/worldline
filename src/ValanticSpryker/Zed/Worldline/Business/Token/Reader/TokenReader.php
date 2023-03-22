<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Token\Reader;

use Generated\Shared\Transfer\PaymentMethodConditionsTransfer;
use Generated\Shared\Transfer\PaymentMethodCriteriaTransfer;
use Generated\Shared\Transfer\PaymentMethodTransfer;
use Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokenErrorItemTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use Spryker\Zed\Payment\Business\PaymentFacadeInterface;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineRepositoryInterface;

class TokenReader implements TokenReaderInterface
{
 /**
  * @var \Spryker\Zed\Payment\Business\PaymentFacadeInterface
  */
    private PaymentFacadeInterface $paymentFacade;

    /**
     * @var \ValanticSpryker\Zed\Worldline\Persistence\WorldlineRepositoryInterface
     */
    private WorldlineRepositoryInterface $worldlineRepository;

    /**
     * @var array $paymentMethodCache {
     * @type string $paymentMethodKey paymentMethodKey from the database entry
     * @type string $paymentMethodName paymentMethodName from the database entry
     *     }
     */
    private array $paymentMethodCache = [];

    /**
     * @var \Spryker\Zed\Customer\Business\CustomerFacadeInterface
     */
    private CustomerFacadeInterface $customerFacade;

    /**
     * @param \Spryker\Zed\Payment\Business\PaymentFacadeInterface $paymentFacade
     * @param \Spryker\Zed\Customer\Business\CustomerFacadeInterface $customerFacade
     * @param \ValanticSpryker\Zed\Worldline\Persistence\WorldlineRepositoryInterface $worldlineRepository
     */
    public function __construct(PaymentFacadeInterface $paymentFacade, CustomerFacadeInterface $customerFacade, WorldlineRepositoryInterface $worldlineRepository)
    {
        $this->paymentFacade = $paymentFacade;
        $this->customerFacade = $customerFacade;
        $this->worldlineRepository = $worldlineRepository;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentTokensByCustomerId(WorldlinePaymentTokenRequestTransfer $worldlinePaymentTokenRequestTransfer): WorldlinePaymentTokensResponseTransfer
    {
        $customerReference = $worldlinePaymentTokenRequestTransfer->getCustomerReference();
        $worldlinePaymentTokensResponseTransfer = (new WorldlinePaymentTokensResponseTransfer());

        $customerResponseTransfer = $this->customerFacade->findCustomerByReference($customerReference);
        if ($customerResponseTransfer->getIsSuccess() === false || !$customerResponseTransfer->getHasCustomer()) {
            $worldlinePaymentTokensResponseTransfer->setIsSuccessful(false);
            $worldlinePaymentTokensResponseTransfer->addError(
                (new WorldlinePaymentTokenErrorItemTransfer())->setMessage("Customer {$customerReference} does not exist."),
            );
        } else {
            $worldlinePaymentTokensResponseTransfer = $this->worldlineRepository->getPaymentTokensByCustomerId(
                $customerResponseTransfer->getCustomerTransfer()->getIdCustomer(),
            );
            /** @var \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer $worldlineCreditCardTokenTransfer */
            foreach ($worldlinePaymentTokensResponseTransfer->getTokens() as $worldlineCreditCardTokenTransfer) {
                $this->hydratePaymentMethodName($worldlineCreditCardTokenTransfer);
            }
        }

        $worldlinePaymentTokensResponseTransfer->setCustomerReference($customerReference);

        return $worldlinePaymentTokensResponseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer $worldlineCreditCardTokenTransfer
     *
     * @return void
     */
    private function hydratePaymentMethodName(WorldlineCreditCardTokenTransfer $worldlineCreditCardTokenTransfer): void
    {
        $paymentMethodKey = $worldlineCreditCardTokenTransfer->getPaymentMethodKey();

        if (!array_key_exists($paymentMethodKey, $this->paymentMethodCache)) {
            $paymentMethodTransfer = $this->findPaymentMethodByPaymentMethodKey($paymentMethodKey);
            $paymentMethodName = $paymentMethodTransfer?->getName();
            $this->paymentMethodCache[$paymentMethodKey] = $paymentMethodName;
        }

        $worldlineCreditCardTokenTransfer->setPaymentMethodName(
            $this->paymentMethodCache[$paymentMethodKey],
        );
    }

    /**
     * @param string|null $paymentMethodKey
     *
     * @return \Generated\Shared\Transfer\PaymentMethodTransfer|null
     */
    private function findPaymentMethodByPaymentMethodKey(?string $paymentMethodKey): ?PaymentMethodTransfer
    {
        $paymentMethodCollectionTransfer = $this->paymentFacade->getPaymentMethodCollection(
            (new PaymentMethodCriteriaTransfer())->setPaymentMethodConditions(
                (new PaymentMethodConditionsTransfer())->addPaymentMethodKey($paymentMethodKey),
            ),
        );
        $paymentMethodsFound = $paymentMethodCollectionTransfer->getPaymentMethods();
        if ($paymentMethodsFound->count() === 1) {
            return $paymentMethodsFound->getArrayCopy()[0];
        }

        return null;
    }
}
