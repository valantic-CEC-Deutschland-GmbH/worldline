<?php

declare(strict_types = 1);

namespace ValanticSpryker\Glue\PaymentTokensRestApi;

use Spryker\Glue\Kernel\AbstractBundleConfig;

class PaymentTokensRestApiConfig extends AbstractBundleConfig
{
    /**
     * @var string
     */
    public const RESOURCE_PAYMENT_TOKENS = 'payment-tokens';

    /**
     * @var string
     */
    public const RESPONSE_CODE_CUSTOMER_REFERENCE_MISSING = '405'; // Note: Same as used in CustomersRestApiConfig

    /**
     * @var string
     */
    public const RESPONSE_DETAILS_CUSTOMER_REFERENCE_MISSING = 'Customer reference is missing.'; // Note: Same as used in CustomersRestApiConfig
}
