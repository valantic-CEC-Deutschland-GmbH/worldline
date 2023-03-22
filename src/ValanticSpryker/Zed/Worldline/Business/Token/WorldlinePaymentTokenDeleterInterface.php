<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Token;

use Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer;
use Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer;

interface WorldlinePaymentTokenDeleterInterface
{
    /**
     * @param \Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer $tokenTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineDeleteTokenResponseTransfer
     */
    public function deletePaymentTokenById(WorldlineDeleteTokenRequestTransfer $tokenTransfer): WorldlineDeleteTokenResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\WorldlineCreditCardTokenTransfer $tokenTransfer
     *
     * @return void
     */
    public function markTokenAsDeleted(WorldlineCreditCardTokenTransfer $tokenTransfer): void;
}
