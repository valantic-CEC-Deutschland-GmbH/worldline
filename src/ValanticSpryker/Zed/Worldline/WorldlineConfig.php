<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline;

use Generated\Shared\Transfer\PaymentTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrder;
use ValanticSpryker\Shared\Worldline\WorldlineConstants;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class WorldlineConfig extends AbstractBundleConfig
{
    /**
     * @var string
     */
    public const PROVIDER_NAME = 'Worldline';

    /**
     * @var string
     */
    public const PAYMENT_METHOD_INVOICE = 'worldlineInvoice';

    /**
     * @var string
     */
    public const PAYMENT_METHOD_CREDIT_CARD_VISA = 'worldlineCreditCardVisa';

    /**
     * @var string
     */
    public const PAYMENT_METHOD_CREDIT_CARD_MASTER_CARD = 'worldlineCreditCardMasterCard';

    /**
     * @var string
     */
    public const PAYMENT_METHOD_CREDIT_CARD_AMERICAN_EXPRESS = 'worldlineCreditCardAmericanExpress';

    /**
     * @var string
     */
    public const PAYMENT_METHOD_PAYPAL = 'worldlinePaypal';

    /**
     * @var int
     */
    public const WORLDLINE_PAYPAL_PAYMENT_PRODUCT_ID = 840;

    /**
     * @var string
     */
    private const AUTHORIZATION_MODE = 'PRE_AUTHORIZATION';

    /**
     * @var string
     */
    private const TRANSACTION_CHANNEL = 'ECOMMERCE';

    /**
     * @var string
     */
    public const ERROR_MESSAGE_TOKEN_NOT_FOUND = 'Token not found.';

    /**
     * @var string
     */
    public const ERROR_MESSAGE_CUSTOMER_NOT_FOUND = 'Customer not found.';

    /**
     * @var int
     */
    public const ERROR_CODE_TOKEN_NOT_FOUND = 404;

    /**
     * @var int
     */
    public const ERROR_CODE_CUSTOMER_NOT_FOUND = 403;

    /**
     * @var array
     */
    private array $paymentProductIdToPaymentMethodKeyMap = [
        1 => 'worldlineCreditCardVisa',
        2 => 'worldlineCreditCardAmericanExpress',
        3 => 'worldlineCreditCardMasterCard',
        840 => 'worldlinePaypal',
    ];

    /**
     * @var string
     */
    private const TIME_ZONE = 'CEST';

    /**
     * @var array
     */
    private array $paymentProductMap = [
        'Visa' => 1,
        'Master Card' => 3,
        'American Express' => 2,
        'PayPal' => 840,
    ];

    /**
     * @var int
     */
    private const HOSTED_CHECKOUT_ALLOWED_MAX_DURATION_IN_SECONDS = 7200; // 7200s = 2h because after 2h Worldline invalidates the hosted checkout anyway (as of Dec. 2022)

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->get(WorldlineConstants::WORLDLINE_API_KEY_ID);
    }

    /**
     * @return string
     */
    public function getApiSecret(): string
    {
        return $this->get(WorldlineConstants::WORLDLINE_API_SECRET);
    }

    /**
     * @return string
     */
    public function getApiEndpoint(): string
    {
        return $this->get(WorldlineConstants::WORLDLINE_API_ENDPOINT);
    }

    /**
     * @return string
     */
    public function getIntegrator(): string
    {
        return $this->get(WorldlineConstants::WORLDLINE_API_INTEGRATOR);
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->get(WorldlineConstants::WORLDLINE_API_MERCHANT_ID);
    }

    /**
     * @param \Generated\Shared\Transfer\PaymentTransfer $paymentTransfer
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $orderEntity
     *
     * @return string
     */
    public function generateWorldlineReference(PaymentTransfer $paymentTransfer, SpySalesOrder $orderEntity): string
    {
        return $orderEntity->getOrderReference();
    }

    /**
     * @return string
     */
    public function getCountryIso2Code(): string
    {
        return $this->get(WorldlineConstants::WORLDLINE_API_COUNTRY_ISO2CODE, 'DE');
    }

    /**
     * @return string
     */
    public function getAuthorizationMode(): string
    {
        return self::AUTHORIZATION_MODE;
    }

    /**
     * @return bool
     */
    public function getIsTokenizationEnabled(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getTransactionChannel(): string
    {
        return self::TRANSACTION_CHANNEL;
    }

    /**
     * @return bool
     */
    public function getReturnCancelState(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getTimeZone(): string
    {
        return self::TIME_ZONE;
    }

    /**
     * @return int
     */
    public function getHostedCheckoutAllowedMaxDurationInSeconds(): int
    {
        return self::HOSTED_CHECKOUT_ALLOWED_MAX_DURATION_IN_SECONDS;
    }

    /**
     * @param string|null $paymentSelection
     *
     * @return int
     */
    public function mapPaymentMethodToPaymentProductId(?string $paymentSelection): int
    {
        if (array_key_exists($paymentSelection, $this->paymentProductMap)) {
            return $this->paymentProductMap[$paymentSelection];
        }

        return 0;// not found
    }

    /**
     * @return int
     */
    public function getConnectTimeout(): int
    {
        return $this->get(WorldlineConstants::CONNECT_TIMEOUT, -1);
    }

    /**
     * @return int
     */
    public function getReadTimeout(): int
    {
        return $this->get(WorldlineConstants::READ_TIMEOUT, -1);
    }

    /**
     * @return array
     */
    public function getSecretKeyStore(): array
    {
        return $this->get(WorldlineConstants::WORLDLINE_SECRETS_KEY_STORE, []);
    }

    /**
     * @return bool
     */
    public function getIsDevelopmentWithoutWorldlineBackend(): bool
    {
        return $this->get(WorldlineConstants::WORLDLINE_IS_DEVELOPMENT_WITHOUT_WORLDLINE_BACKEND, false);
    }

    /**
     * @return string
     */
    public function getWorldlineTimeStampTimeZone(): string
    {
        return $this->get(WorldlineConstants::WORLDLINE_TIMEZONE, 'Europe/Paris');
    }

    /**
     * @param int $paymentProductId
     *
     * @return string
     */
    public function getPaymentMethodKeyByPaymentProductId(int $paymentProductId): string
    {
        if (array_key_exists($paymentProductId, $this->paymentProductIdToPaymentMethodKeyMap)) {
            return $this->paymentProductIdToPaymentMethodKeyMap[$paymentProductId];
        }

        return 'unknownPaymentMethod';
    }

    /**
     * @return int
     */
    public function getMaxHoursToWaitBeforeCaptureTimesOut(): int
    {
        return $this->get(WorldlineConstants::WORLDLINE_MAX_HOURS_TO_WAIT_BEFORE_CAPTURE_TIMES_OUT, 24);
    }

    /**
     * @return int
     */
    public function getLimitOfDeletedTokensToRemove(): int
    {
        return $this->get(WorldlineConstants::WORLDLINE_LIMIT_OF_DELETED_TOKENS_TO_REMOVE_AT_A_TIME, 5);
    }
}
