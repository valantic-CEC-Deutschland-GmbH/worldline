<?php

declare(strict_types = 1);

namespace ValanticSpryker\Shared\Worldline;

use Spryker\Shared\Kernel\AbstractBundleConfig;

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
     * @var string
     */
    public const PAYMENT_METHOD_NAME_CREDIT_CARD_VISA = 'Visa';

    /**
     * @var string
     */
    public const PAYMENT_METHOD_NAME_CREDIT_CARD_MASTER_CARD = 'Master Card';

    /**
     * @var string
     */
    public const PAYMENT_METHOD_NAME_CREDIT_CARD_AMERICAN_EXPRESS = 'American Express';

    /**
     * @var string
     */
    public const PAYMENT_METHOD_NAME_PAYPAL = 'paypal';
}
