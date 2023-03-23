<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Customer\Communication\Table;

use Generated\Shared\Transfer\PaymentMethodCollectionTransfer;
use Generated\Shared\Transfer\PaymentMethodCriteriaTransfer;
use Orm\Zed\Worldline\Persistence\Map\VsyWorldlineTokenTableMap;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Shared\Customer\CustomerConstants;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;
use Spryker\Zed\Payment\Business\PaymentFacadeInterface;
use Spryker\Zed\PropelOrm\Business\Runtime\ActiveQuery\Criteria;
use ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface;

class TokenTable extends AbstractTable
{
    public const PARAM_ID_TOKEN = 'id-token';

    /**
     * @var string
     */
    public const TOKEN_TABLE_URL = '/token-table?%s=%s';

    /**
     * @var string
     */
    public const ACTIONS = 'Actions';

    public const PAYMENT_METHOD = 'Payment Method';

    public const COL_PAYMENT_METHOD = 'payment_method';

    public const URL_REMOVE_TOKEN = '/worldline/token/delete';

    private const TABLE_IDENTIFIER = 'token_table';

    /**
     * @var \Generated\Shared\Transfer\PaymentMethodCollectionTransfer
     */
    private PaymentMethodCollectionTransfer $paymentMethodCollection;

    /**
     * @param \Spryker\Zed\Payment\Business\PaymentFacadeInterface $paymentFacade
     * @param int $idCustomer
     * @param \ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface $worldlineQueryContainer
     */
    public function __construct(
        private PaymentFacadeInterface $paymentFacade,
        private int $idCustomer,
        private WorldlineQueryContainerInterface $worldlineQueryContainer
    ) {
        $this->paymentMethodCollection = $this->paymentFacade->getPaymentMethodCollection(new PaymentMethodCriteriaTransfer());
    }

    /**
     * @inheritDoc
     */
    protected function configure(TableConfiguration $config)
    {
        $config->setHeader($this->getHeaderFields());

        $config->addRawColumn(self::COL_PAYMENT_METHOD);
        $config->addRawColumn(static::ACTIONS);

        $config->setSortable(
            [
                VsyWorldlineTokenTableMap::COL_ID_TOKEN,
                VsyWorldlineTokenTableMap::COL_HOLDER_NAME,
                VsyWorldlineTokenTableMap::COL_OBFUSCATED_CARD_NUMBER,
                static::COL_PAYMENT_METHOD,
            ],
        );

        $config->setSearchable(
            [
                VsyWorldlineTokenTableMap::COL_HOLDER_NAME,
                VsyWorldlineTokenTableMap::COL_OBFUSCATED_CARD_NUMBER,
                static::COL_PAYMENT_METHOD,
            ],
        );
        $this->setTableIdentifier(self::TABLE_IDENTIFIER);
        $config->setUrl(sprintf(static::TOKEN_TABLE_URL, CustomerConstants::PARAM_ID_CUSTOMER, $this->idCustomer));

        return $config;
    }

    /**
     * @return array<string>
     */
    protected function getHeaderFields(): array
    {
        return [
            VsyWorldlineTokenTableMap::COL_ID_TOKEN => '#',
            VsyWorldlineTokenTableMap::COL_EXPIRY_MONTH => 'Expiry Month',
            VsyWorldlineTokenTableMap::COL_HOLDER_NAME => 'Holder Name',
            VsyWorldlineTokenTableMap::COL_OBFUSCATED_CARD_NUMBER => 'Card Number',
            VsyWorldlineTokenTableMap::COL_EXPIRED_AT => 'Expired At',
            static::COL_PAYMENT_METHOD => self::PAYMENT_METHOD,
            static::ACTIONS => 'Actions',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function prepareData(TableConfiguration $config)
    {
        $query = $this->worldlineQueryContainer->queryTokens()
            ->filterByFkCustomer($this->idCustomer)
            ->filterByDeletedAt(comparison: Criteria::ISNULL);

        $lines = $this->runQuery($query, $config);

        if ($lines) {
            foreach ($lines as $key => $value) {
                $lines[$key][self::COL_PAYMENT_METHOD] = $this->getPaymentMethodByPaymentMethodKey($lines[$key][VsyWorldlineTokenTableMap::COL_PAYMENT_METHOD_KEY]);

                $lines[$key][static::ACTIONS] = $this->buildLinks($value);
            }
        }

        return $lines;
    }

    /**
     * @param string $paymentMethodKey
     *
     * @return string
     */
    private function getPaymentMethodByPaymentMethodKey(string $paymentMethodKey): string
    {
        foreach ($this->paymentMethodCollection->getPaymentMethods() as $paymentMethod) {
            if ($paymentMethod->getPaymentMethodKey() === $paymentMethodKey) {
                return $paymentMethod->getName();
            }
        }

        return $paymentMethodKey;
    }

    /**
     * @param array $details
     *
     * @return string
     */
    private function buildLinks(array $details): string
    {
        $buttons = [];

        $idToken = !empty($details[VsyWorldlineTokenTableMap::COL_ID_TOKEN])
            ? $details[VsyWorldlineTokenTableMap::COL_ID_TOKEN]
            : null;

        if ($idToken !== null) {
            $buttons[] = $this->generateRemoveButton(
                (string)Url::generate(static::URL_REMOVE_TOKEN, [
                    static::PARAM_ID_TOKEN => $idToken,
                    CustomerConstants::PARAM_ID_CUSTOMER => $this->idCustomer,
                ]),
                'Delete',
            );
        }

        return implode(' ', $buttons);
    }
}
