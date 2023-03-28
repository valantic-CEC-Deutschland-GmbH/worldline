<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Token;

use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer;
use Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer;
use Generated\Shared\Transfer\WorldlineErrorItemTransfer;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface;
use ValanticSpryker\Zed\Worldline\Business\Writer\WorldlineWriterInterface;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

class WorldlinePaymentTokenDeleter implements WorldlinePaymentTokenDeleterInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\Business\ApiCallHandler\ApiCallHandlerInterface $apiCallHandler
     * @param \ValanticSpryker\Zed\Worldline\Business\Writer\WorldlineWriterInterface $worldlineWriter
     * @param \ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface $worldlineQueryContainer
     * @param \Spryker\Zed\Customer\Business\CustomerFacadeInterface $customerFacade
     */
    public function __construct(private ApiCallHandlerInterface $apiCallHandler, private WorldlineWriterInterface $worldlineWriter, private WorldlineQueryContainerInterface $worldlineQueryContainer, private CustomerFacadeInterface $customerFacade)
    {
    }

    /**
     * @inheritDoc
     */
    public function deletePaymentTokenById(WorldlineDeleteTokenRequestTransfer $tokenTransfer): WorldlineDeleteTokenResponseTransfer
    {
        $customerTransfer = null;
        if ($tokenTransfer->getIdCustomer()) {
            $customerTransfer = (new CustomerTransfer())->setIdCustomer($tokenTransfer->getIdCustomer());
            $customerTransfer = $this->customerFacade->getCustomer($customerTransfer);
        }
        if (($customerTransfer === null) && $tokenTransfer->getCustomerReference()) {
            $customerTransfer = $this->customerFacade->findByReference($tokenTransfer->getCustomerReference());
        }
        if ($customerTransfer === null) {
            return $this->createCustomerNotFoundErrorResponse();
        }

        if ($tokenTransfer->getIdToken()) {
            $tokenEntity = $this->worldlineQueryContainer->queryTokens()->findOneByIdToken($tokenTransfer->getIdToken());
            if ($tokenEntity) {
                $tokenTransfer->setToken($tokenEntity->getToken());
            }
        }

        if (
            !$this->worldlineQueryContainer->findTokenByExternalTokenIdAndFkCustomer(
                $tokenTransfer->getToken(),
                $customerTransfer->getIdCustomer(),
            )->count()
        ) {
            return $this->createTokenNotFoundErrorResponse();
        }

        /** @var \Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer $responseTransfer */
        $responseTransfer = $this->apiCallHandler->handleApiCall($tokenTransfer);

        $creditCardTokenTransfer = new WorldlineCreditCardTokenTransfer();
        $creditCardTokenTransfer->setToken($tokenTransfer->getToken());

        if ($responseTransfer->getIsSuccess()) {
            $this->markTokenAsDeleted($creditCardTokenTransfer);
        }

        return $responseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer $tokenTransfer
     *
     * @return void
     */
    public function markTokenAsDeleted(WorldlineCreditCardTokenTransfer $tokenTransfer): void
    {
        $this->worldlineWriter->markTokenDeletedById($tokenTransfer);
    }

    /**
     * @return \Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer
     */
    protected function createTokenNotFoundErrorResponse(): WorldlineDeleteTokenResponseTransfer
    {
        $responseTransfer = new WorldlineDeleteTokenResponseTransfer();

        $responseTransfer->setIsSuccess(false);
        $responseTransfer->setHttpStatusCode(WorldlineConfig::ERROR_CODE_TOKEN_NOT_FOUND);
        $responseTransfer->addError(
            (new WorldlineErrorItemTransfer())
                ->setMessage(WorldlineConfig::ERROR_MESSAGE_TOKEN_NOT_FOUND)
                ->setCode((string)WorldlineConfig::ERROR_CODE_TOKEN_NOT_FOUND)
                ->setHttpStatusCode(WorldlineConfig::ERROR_CODE_TOKEN_NOT_FOUND),
        );

        return $responseTransfer;
    }

    /**
     * @return \Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer
     */
    protected function createCustomerNotFoundErrorResponse(): WorldlineDeleteTokenResponseTransfer
    {
        $responseTransfer = new WorldlineDeleteTokenResponseTransfer();

        $responseTransfer->setIsSuccess(false);
        $responseTransfer->setHttpStatusCode(WorldlineConfig::ERROR_CODE_CUSTOMER_NOT_FOUND);
        $responseTransfer->addError(
            (new WorldlineErrorItemTransfer())
                ->setMessage(WorldlineConfig::ERROR_MESSAGE_CUSTOMER_NOT_FOUND)
                ->setCode((string)WorldlineConfig::ERROR_CODE_CUSTOMER_NOT_FOUND)
                ->setHttpStatusCode(WorldlineConfig::ERROR_CODE_CUSTOMER_NOT_FOUND),
        );

        return $responseTransfer;
    }
}
