<?php

declare(strict_types = 1);

namespace ValanticSprykerTest\Zed\Worldline\Business\Mapper;

use Base\AbstractTest;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\AmountOfMoneyTransfer;
use Generated\Shared\Transfer\HostedCheckoutSpecificInputTransfer;
use Generated\Shared\Transfer\WorldlineAddressTransfer;
use Generated\Shared\Transfer\WorldlineBrowserDataTransfer;
use Generated\Shared\Transfer\WorldlineCardPaymentMethodSpecificOutputTransfer;
use Generated\Shared\Transfer\WorldlineCreatedPaymentOutputTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutResponseTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer;
use Generated\Shared\Transfer\WorldlineCustomerTransfer;
use Generated\Shared\Transfer\WorldlineDeviceTransfer;
use Generated\Shared\Transfer\WorldlineGetHostedCheckoutStatusResponseTransfer;
use Generated\Shared\Transfer\WorldlineHostedCheckoutSpecificOutputTransfer;
use Generated\Shared\Transfer\WorldlineOrderTransfer;
use Generated\Shared\Transfer\WorldlinePaymentOutputTransfer;
use Generated\Shared\Transfer\WorldlinePaymentStatusOutputTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTransfer;
use Ingenico\Connect\Sdk\Domain\Definitions\AmountOfMoney;
use Ingenico\Connect\Sdk\Domain\Definitions\CardEssentials;
use Ingenico\Connect\Sdk\Domain\Definitions\CardFraudResults;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutResponse;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\Definitions\CreatedPaymentOutput;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\GetHostedCheckoutResponse;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\CardPaymentMethodSpecificOutput;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\HostedCheckoutSpecificOutput;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\PaymentOutput;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\PaymentStatusOutput;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\ThreeDSecureResults;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use ValanticSpryker\Zed\Worldline\Business\Mapper\HostedCheckoutMapper;

class HostedCheckoutMapperTest extends Unit
{
    private const EXAMPLE_RETURN_URL = 'https://some.secure.url';
    private const EXAMPLE_TOKENS_STRING = 'uuid-00393-292929-883,uuid-2672-237823-233';
    private const EXAMPLE_HOSTED_CHECKOUT_ID = 'some Id';
    private const EXAMPLE_ORDER_REFERENCE = 'DE--666';
    private const EXAMPLE_PARTIAL_REDIRECT = 'some_redirect_url?id=someUUID';
    private const EXAMPLE_RETURN_MAC = 'SOME_RETURN_MAC';
    private const EXAMPLE_PAYMENT_ID = 'SOME_PAYMENT_ID';
    private const EXAMPLE_PAYMENT_STATUS_CODE = 700;
    private const EXAMPLE_PAYMENT_METHOD = 'EXAMPLE_PAYMENT_METHOD';
    private const EXAMPLE_TOKEN_STRING = 'MY_TOKEN_STRING';
    private const EXAMPLE_AUTHORISATION_CODE = 'EXAMPLE_AUTHORISATION_CODE';

    protected $tester;
    
    public function testMapCreateHostedCheckoutTransferToWorldlineCreateHostedCheckoutMapsSimpleWorldlineCreateHostedCheckoutTransferCorrectly()
    {
        // Arrange
        $createHostedCheckoutTransfer = $this->createWorldlineCreateHostedCheckoutTransfer();

        // Act
        $worldlineHostedCheckoutRequest = (new HostedCheckoutMapper())->mapCreateHostedCheckoutTransferToWorldlineCreateHostedCheckout($createHostedCheckoutTransfer);

        // Assert
        self::assertNotNull($worldlineHostedCheckoutRequest);
        $this->assertHostedCheckoutSpecificInput($worldlineHostedCheckoutRequest);

        $this->assertCustomerBillingAddress($worldlineHostedCheckoutRequest);

        $this->assertAmountOfMoney($worldlineHostedCheckoutRequest);

        $this->assertDevice($worldlineHostedCheckoutRequest);
    }

    public function testMapCreateHostedCheckoutResponseToCreateHostedCheckoutResponseTransfer()
    {
        // Arrange
        $createHostedCheckoutResponse = $this->createHostedCheckoutResponse();

        $createHostedCheckoutResponseTransfer = new WorldlineCreateHostedCheckoutResponseTransfer();

        // Act
        $createHostedCheckoutResponseTransfer = (new HostedCheckoutMapper())->mapCreateHostedCheckoutResponseToCreateHostedCheckoutResponseTransfer($createHostedCheckoutResponse, $createHostedCheckoutResponseTransfer);

        // Assert
        $this->assertCreateHostedCheckoutResponseTransfer($createHostedCheckoutResponseTransfer);
    }



    public function testMapGetHostedCheckoutStatusResponseToHostedCheckoutResponseTransfer()
    {
        // Arrange
        $getHostedCheckoutStatusResponse = $this->createGetHostedCheckoutStatusResponse();
        $worldlineGetHostedCheckoutResponseTransfer = new WorldlineGetHostedCheckoutStatusResponseTransfer();

        // Act
        $worldlineGetHostedCheckoutResponseTransfer = (new HostedCheckoutMapper())->mapGetHostedCheckoutStatusResponseToHostedCheckoutResponseTransfer($getHostedCheckoutStatusResponse, $worldlineGetHostedCheckoutResponseTransfer);

        // Assert
        $this->assertWorldlineGetHostedCheckoutStatusResponseTransfer($worldlineGetHostedCheckoutResponseTransfer);
    }

    /**
     * @return \Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer
     */
    protected function createWorldlineCreateHostedCheckoutTransfer(): WorldlineCreateHostedCheckoutTransfer
    {
        return (new WorldlineCreateHostedCheckoutTransfer())
            ->setHostedCheckoutSpecificInput(
                $this->createHostedCheckoutSpecificInput(),
            )
            ->setOrder(
                $this->createWorldlineOrderTransfer()
            );
    }

    /**
     * @return \Generated\Shared\Transfer\HostedCheckoutSpecificInputTransfer
     */
    protected function createHostedCheckoutSpecificInput(): HostedCheckoutSpecificInputTransfer
    {
        return (new HostedCheckoutSpecificInputTransfer())
            ->setReturnUrl(self::EXAMPLE_RETURN_URL)
            ->setTokens(self::EXAMPLE_TOKENS_STRING)
            ->setLocale('de_DE')
            ->setIsRecurring(false)
            ->setReturnCancelState(true)
            ->setValidateShoppingCart(false)
            ->setShowResultPage(false);
    }

    /**
     * @return \Generated\Shared\Transfer\WorldlineOrderTransfer
     */
    protected function createWorldlineOrderTransfer(): WorldlineOrderTransfer
    {
        return (new WorldlineOrderTransfer())
            ->setCustomer(
                (new WorldlineCustomerTransfer())
                    ->setLocale('de_DE')
                    ->setDevice(
                        $this->createWorldlineDeviceTransfer(),
                    )
                    ->setBillingAddress(
                        $this->createWorldlineAddressTransfer()
                    ),
            )
            ->setAmountOfMoney((new AmountOfMoneyTransfer())
                ->setAmount(1000)
                ->setCurrencyCode('EUR')
            );
    }

    /**
     * @return \Generated\Shared\Transfer\WorldlineAddressTransfer
     */
    protected function createWorldlineAddressTransfer(): WorldlineAddressTransfer
    {
        return (new WorldlineAddressTransfer())
            ->setCity('Langenfeld')
            ->setCountryCode('de')
            ->setZip('40764')
            ->setStreet('Felix-Wankel-Straße')
            ->setHouseNumber(16);
    }

    /**
     * @return \Generated\Shared\Transfer\WorldlineDeviceTransfer
     */
    protected function createWorldlineDeviceTransfer(): WorldlineDeviceTransfer
    {
        return (new WorldlineDeviceTransfer())
            ->setLocale('de_DE')
            ->setIpAddress('127.0.0.1')
            ->setUserAgent('Mozilla Gecko Something')
            ->setTimezoneOffsetUtcMinutes('-60')
            ->setBrowserData(
                (new WorldlineBrowserDataTransfer())
                    ->setColorDepth(32)
                    ->setInnerHeight(768)
                    ->setInnerWidth(1024)
                    ->setJavaEnabled(false)
                    ->setJavaScriptEnabled(true),
            );
    }

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest $worldlineHostedCheckoutRequest
     *
     * @return void
     */
    protected function assertHostedCheckoutSpecificInput(CreateHostedCheckoutRequest $worldlineHostedCheckoutRequest): void
    {
        self::assertSame(self::EXAMPLE_TOKENS_STRING, $worldlineHostedCheckoutRequest->hostedCheckoutSpecificInput->tokens);
        self::assertSame('de_DE', $worldlineHostedCheckoutRequest->hostedCheckoutSpecificInput->locale);
        self::assertFalse($worldlineHostedCheckoutRequest->hostedCheckoutSpecificInput->isRecurring);
        self::assertTrue($worldlineHostedCheckoutRequest->hostedCheckoutSpecificInput->returnCancelState);
        self::assertFalse( $worldlineHostedCheckoutRequest->hostedCheckoutSpecificInput->validateShoppingCart);
        self::assertFalse( $worldlineHostedCheckoutRequest->hostedCheckoutSpecificInput->showResultPage);
        self::assertSame(self::EXAMPLE_RETURN_URL, $worldlineHostedCheckoutRequest->hostedCheckoutSpecificInput->returnUrl);
    }

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest $worldlineHostedCheckoutRequest
     *
     * @return void
     */
    protected function assertCustomerBillingAddress(CreateHostedCheckoutRequest $worldlineHostedCheckoutRequest): void
    {
        self::assertSame('Langenfeld', $worldlineHostedCheckoutRequest->order->customer->billingAddress->city);
        self::assertSame('Felix-Wankel-Straße', $worldlineHostedCheckoutRequest->order->customer->billingAddress->street);
        self::assertSame(16, $worldlineHostedCheckoutRequest->order->customer->billingAddress->houseNumber);
        self::assertSame('40764', $worldlineHostedCheckoutRequest->order->customer->billingAddress->zip);
    }

    /**
     * @param \Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest $worldlineHostedCheckoutRequest
     *
     * @return void
     */
    protected function assertAmountOfMoney(CreateHostedCheckoutRequest $worldlineHostedCheckoutRequest): void
    {
        self::assertSame(1000, $worldlineHostedCheckoutRequest->order->amountOfMoney->amount);
        self::assertSame('EUR', $worldlineHostedCheckoutRequest->order->amountOfMoney->currencyCode);
    }

    private function assertDevice(CreateHostedCheckoutRequest $worldlineHostedCheckoutRequest)
    {
        self::assertSame('de_DE', $worldlineHostedCheckoutRequest->order->customer->device->locale);
        self::assertSame('127.0.0.1', $worldlineHostedCheckoutRequest->order->customer->device->ipAddress);
        self::assertSame('Mozilla Gecko Something', $worldlineHostedCheckoutRequest->order->customer->device->userAgent);
        self::assertSame('-60', $worldlineHostedCheckoutRequest->order->customer->device->timezoneOffsetUtcMinutes);
        self::assertSame(32, $worldlineHostedCheckoutRequest->order->customer->device->browserData->colorDepth);
        self::assertSame(768, $worldlineHostedCheckoutRequest->order->customer->device->browserData->innerHeight);
        self::assertSame(1024, $worldlineHostedCheckoutRequest->order->customer->device->browserData->innerWidth);
        self::assertFalse($worldlineHostedCheckoutRequest->order->customer->device->browserData->javaEnabled);
        self::assertTrue($worldlineHostedCheckoutRequest->order->customer->device->browserData->javaScriptEnabled);
    }

    /**
     * @return \Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutResponse
     */
    private function createHostedCheckoutResponse()
    {
        $createHostedCheckoutResponse = new CreateHostedCheckoutResponse();
        $createHostedCheckoutResponse->hostedCheckoutId = self::EXAMPLE_HOSTED_CHECKOUT_ID;
        $createHostedCheckoutResponse->invalidTokens = ['invalid_token_1', 'invalid_token_2'];
        $createHostedCheckoutResponse->merchantReference = self::EXAMPLE_ORDER_REFERENCE;
        $createHostedCheckoutResponse->partialRedirectUrl = self::EXAMPLE_PARTIAL_REDIRECT;
        $createHostedCheckoutResponse->RETURNMAC = self::EXAMPLE_RETURN_MAC;

        return $createHostedCheckoutResponse;
    }

    private function assertCreateHostedCheckoutResponseTransfer(WorldlineCreateHostedCheckoutResponseTransfer $createHostedCheckoutResponseTransfer)
    {
        self::assertSame(self::EXAMPLE_HOSTED_CHECKOUT_ID, $createHostedCheckoutResponseTransfer->getHostedCheckoutId());
        self::assertSame(['invalid_token_1', 'invalid_token_2'], $createHostedCheckoutResponseTransfer->getInvalidTokens());
        self::assertSame(self::EXAMPLE_ORDER_REFERENCE, $createHostedCheckoutResponseTransfer->getMerchantReference());
        self::assertSame(self::EXAMPLE_PARTIAL_REDIRECT, $createHostedCheckoutResponseTransfer->getPartialRedirectUrl());
        self::assertSame(self::EXAMPLE_RETURN_MAC, $createHostedCheckoutResponseTransfer->getRETURNMAC());
    }

    private function createGetHostedCheckoutStatusResponse()
    {
        $getHostedCheckoutStatusResponse = new GetHostedCheckoutResponse();

        $getHostedCheckoutStatusResponse->status = WorldlineConstants::STATUS_HOSTED_CHECKOUT_CREATED;
        $getHostedCheckoutStatusResponse->createdPaymentOutput = $this->createCreatedPaymentOutput();

        return $getHostedCheckoutStatusResponse;
    }

    private function assertWorldlineGetHostedCheckoutStatusResponseTransfer(WorldlineGetHostedCheckoutStatusResponseTransfer $worldlineGetHostedCheckoutStatusResponseTransfer)
    {
        self::assertSame(WorldlineConstants::STATUS_HOSTED_CHECKOUT_CREATED, $worldlineGetHostedCheckoutStatusResponseTransfer->getStatus());
        $this->assertWorldlineCreatedPaymentOutputTransfer($worldlineGetHostedCheckoutStatusResponseTransfer->getCreatedPaymentOutput());
    }

    private function createCreatedPaymentOutput()
    {
        $createdPaymentOutput = new CreatedPaymentOutput();
        $createdPaymentOutput->tokens = self::EXAMPLE_TOKENS_STRING;
        $createdPaymentOutput->payment = $this->createPayment();

        return $createdPaymentOutput;
    }

    private function assertWorldlineCreatedPaymentOutputTransfer(?WorldlineCreatedPaymentOutputTransfer $createdPaymentOutput)
    {
        self::assertNotNull($createdPaymentOutput);

        self::assertSame(self::EXAMPLE_TOKENS_STRING, $createdPaymentOutput->getTokens());
        $this->assertPayment($createdPaymentOutput->getPayment());
    }

    private function createPayment()
    {
        $payment = new Payment();
        $payment->status = WorldlineConstants::STATUS_CREATED;
        $payment->id = self::EXAMPLE_PAYMENT_ID;
        $payment->statusOutput = $this->createPaymentStatusOutput();
        $payment->hostedCheckoutSpecificOutput = $this->createHostedCheckoutSpecificOutput();
        $payment->paymentOutput = $this->createPaymentOutput();

        return $payment;
    }

    private function assertPayment(?WorldlinePaymentTransfer $paymentTransfer)
    {
        self::assertNotNull($paymentTransfer);

        self::assertSame(WorldlineConstants::STATUS_CREATED, $paymentTransfer->getStatus());
        self::assertSame(self::EXAMPLE_PAYMENT_ID, $paymentTransfer->getId());

        $this->assertPaymentStatusOutput($paymentTransfer->getStatusOutput());
        $this->assertPaymentOutput($paymentTransfer->getPaymentOutput());

        $this->assertHostedCheckoutSpecificOutput($paymentTransfer->getHostedCheckoutSpecificOutput());
    }

    private function createPaymentStatusOutput()
    {
        $paymentStatusOutput = new PaymentStatusOutput();

        $paymentStatusOutput->statusCode = self::EXAMPLE_PAYMENT_STATUS_CODE;
        $paymentStatusOutput->statusCategory = WorldlineConstants::STATUS_CATEGORY_PENDING_MERCHANT;
        $paymentStatusOutput->statusCodeChangeDateTime = '20230324085612';
        $paymentStatusOutput->isAuthorized = false;
        $paymentStatusOutput->isCancellable = true;
        $paymentStatusOutput->isRefundable = false;
        $paymentStatusOutput->isRetriable = false;
        $paymentStatusOutput->threeDSecureStatus = '3dStatus';

        return $paymentStatusOutput;
    }


    private function assertPaymentStatusOutput(?WorldlinePaymentStatusOutputTransfer $statusOutputTransfer)
    {
        self::assertNotNull($statusOutputTransfer);

        self::assertSame(self::EXAMPLE_PAYMENT_STATUS_CODE, $statusOutputTransfer->getStatusCode());
        self::assertSame(WorldlineConstants::STATUS_CATEGORY_PENDING_MERCHANT, $statusOutputTransfer->getStatusCategory());
        self::assertSame('20230324085612', $statusOutputTransfer->getStatusCodeChangeDateTime());

        self::assertFalse($statusOutputTransfer->getIsAuthorized());
        self::assertTrue($statusOutputTransfer->getIsCancellable());
        self::assertFalse($statusOutputTransfer->getIsRefundable());
        self::assertSame('3dStatus', $statusOutputTransfer->getThreeDSecureStatus());
    }

    private function createHostedCheckoutSpecificOutput()
    {
        $hostedCheckoutSepecificOutput = new HostedCheckoutSpecificOutput();

        $hostedCheckoutSepecificOutput->hostedCheckoutId = self::EXAMPLE_HOSTED_CHECKOUT_ID;
        $hostedCheckoutSepecificOutput->variant = '100';

        return $hostedCheckoutSepecificOutput;
    }

    private function assertHostedCheckoutSpecificOutput(?WorldlineHostedCheckoutSpecificOutputTransfer $hostedCheckoutSpecificOutputTransfer)
    {
        self::assertNotNull($hostedCheckoutSpecificOutputTransfer);

        self::assertSame(self::EXAMPLE_HOSTED_CHECKOUT_ID, $hostedCheckoutSpecificOutputTransfer->getHostedCheckoutId());
        self::assertSame('100', $hostedCheckoutSpecificOutputTransfer->getVariant());
    }

    private function createPaymentOutput(): PaymentOutput
    {
        $paymentOutput = new PaymentOutput();

        $paymentOutput->amountOfMoney = new AmountOfMoney();

        $paymentOutput->amountOfMoney->amount = 10500;
        $paymentOutput->amountOfMoney->currencyCode = 'EUR';

        $paymentOutput->amountPaid = 10500;
        $paymentOutput->paymentMethod = self::EXAMPLE_PAYMENT_METHOD;

        $paymentOutput->amountReversed = 0;
        $paymentOutput->cardPaymentMethodSpecificOutput = $this->createCardPaymentMethodSpecificOutput();

        return $paymentOutput;
    }

    private function assertPaymentOutput(?WorldlinePaymentOutputTransfer $paymentOutputTransfer)
    {
        self::assertNotNull($paymentOutputTransfer);

        self::assertSame(10500, $paymentOutputTransfer->getAmountOfMoney()->getAmount());
        self::assertSame('EUR', $paymentOutputTransfer->getAmountOfMoney()->getCurrencyCode());

        self::assertSame(10500, $paymentOutputTransfer->getAmountPaid());
        self::assertSame(self::EXAMPLE_PAYMENT_METHOD, $paymentOutputTransfer->getPaymentMethod());

        self::assertSame(0, $paymentOutputTransfer->getAmountReversed());

        $this->assertCardPaymentMethodSpecificOutput($paymentOutputTransfer->getCardPaymentMethodSpecificOutput());
    }

    private function createCardPaymentMethodSpecificOutput()
    {
        $cardPaymentMethodSpecificOutput = new CardPaymentMethodSpecificOutput();

        $cardPaymentMethodSpecificOutput->card = new CardEssentials();

        $cardPaymentMethodSpecificOutput->card->fromJson('{
            "cardNumber": "4 111 1111 1111 1111",
            "cardholderName": "Card Holder",
            "expiryDate": "25/03"
        }');

        $cardPaymentMethodSpecificOutput->authorisationCode = self::EXAMPLE_AUTHORISATION_CODE;

        $cardPaymentMethodSpecificOutput->fraudResults = new CardFraudResults();
        $cardPaymentMethodSpecificOutput->fraudResults->fromJson('{
            "avsResult": "avs_Result",
            "cvvResult": "cvv_Result",
            "fraudServiceResult": "fraud_Service_Result",
            "fraugster": {
                "fraudInvestigationPoints": "fraud_Investigation_Points",
                "fraudScore": "fraud_Score"
            },
            "inAuth": {
                "deviceCategory": "SMARTPHONE",
                "deviceId": "devide_Id",
                "riskScore": "risk_Score",
                "trueIpAddress": "true_Ip_Address"
            },
            "microsoftFraudProtection": {
                "clauseName": "clause_Name",
                "deviceCountryCode": "device_Country_Code",
                "deviceId": "device_Id",
                "fraudScore": 0,
                "policyApplied": "policy_Applied",
                "trueIpAddress": "true_Ip_Address",
                "userDeviceType": "user_Device_Type"
            },
            "retailDecisions": {
                "fraudCode": "fraud_Code",
                "fraudNeural": "fraud_Neural",
                "fraudRCF": "fraud_R_C_F"
            }
        }');
        $cardPaymentMethodSpecificOutput->initialSchemeTransactionId = 'initial_Scheme_Transaction_Id';
        $cardPaymentMethodSpecificOutput->paymentProductId = 1;
        $cardPaymentMethodSpecificOutput->schemeTransactionId = 'scheme_Transaction_Id';
        $cardPaymentMethodSpecificOutput->threeDSecureResults = new ThreeDSecureResults();
        $cardPaymentMethodSpecificOutput->threeDSecureResults->fromJson('{
            "acsTransactionId": "acs_Transaction_Id",
            "appliedExemption": "applied_Exemption",
            "authenticationAmount": {
                "amount": 10500,
                "currencyCode": "EUR"
            },
            "cavv": "cavv",
            "directoryServerTransactionId": "directory_Server_Transaction_Id",
            "eci": "eci",
            "exemptionOutput": {
                "exemptionRaised": "exemption_Raised",
                "exemptionRejectionReason": "exemption_Rejection_Reason",
                "exemptionRequest": "exemption_Request"
            },
            "schemeRiskScore": 0,
            "sdkData": {
                "sdkTransactionId": "sdk_Transaction_Id"
            },
            "threeDSecureData": {
                "acsTransactionId": "acs_Transaction_Id",
                "method": "frictionless",
                "utcTimestamp": "utc_Timestamp"
            },
            "threeDSecureVersion": "three_D_Secure_Version",
            "threeDServerTransactionId": "three_D_Server_Transaction_Id",
            "xid": "xid"
        }');
        $cardPaymentMethodSpecificOutput->token = self::EXAMPLE_TOKEN_STRING;

        return $cardPaymentMethodSpecificOutput;
    }

    private function assertCardPaymentMethodSpecificOutput(?WorldlineCardPaymentMethodSpecificOutputTransfer $methodSpecificOutputTransfer)
    {
        self::assertNotNull($methodSpecificOutputTransfer);

        self::assertSame('4 111 1111 1111 1111', $methodSpecificOutputTransfer->getCard()->getCardNumber());
        self::assertSame('Card Holder', $methodSpecificOutputTransfer->getCard()->getCardholderName());
        self::assertSame('25/03', $methodSpecificOutputTransfer->getCard()->getExpiryDate());

        self::assertSame(self::EXAMPLE_AUTHORISATION_CODE, $methodSpecificOutputTransfer->getAuthorisationCode());

        self::assertSame('SMARTPHONE', $methodSpecificOutputTransfer->getFraudResults()->getInAuth()->getDeviceCategory());
        self::assertSame('fraud_Investigation_Points',$methodSpecificOutputTransfer->getFraudResults()->getFraugster()->getFraudInvestigationPoints());

        self::assertSame('initial_Scheme_Transaction_Id', $methodSpecificOutputTransfer->getInitialSchemeTransactionId());
        self::assertSame(1, $methodSpecificOutputTransfer->getPaymentProductId());
        self::assertSame('scheme_Transaction_Id', $methodSpecificOutputTransfer->getSchemeTransactionId());

        self::assertSame('acs_Transaction_Id', $methodSpecificOutputTransfer->getThreeDSecureResults()->getThreeDSecureData()->getAcsTransactionId());
        self::assertSame('frictionless', $methodSpecificOutputTransfer->getThreeDSecureResults()->getThreeDSecureData()->getMethod());
        self::assertSame('utc_Timestamp', $methodSpecificOutputTransfer->getThreeDSecureResults()->getThreeDSecureData()->getUtcTimestamp());

        self::assertSame(self::EXAMPLE_TOKEN_STRING, $methodSpecificOutputTransfer->getToken());

    }
}
