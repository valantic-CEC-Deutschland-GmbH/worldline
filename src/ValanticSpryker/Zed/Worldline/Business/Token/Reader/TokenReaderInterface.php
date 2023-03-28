<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Token\Reader;

use Generated\Shared\Transfer\WorldlinePaymentTokenRequestTransfer;
use Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer;

interface TokenReaderInterface
{
 /**
  * @param \Generated\Shared\Transfer\WorldlinePaymentTokenRequestTransfer $worldlinePaymentTokenRequestTransfer
  *
  * @return \Generated\Shared\Transfer\WorldlinePaymentTokensResponseTransfer
  */
    public function getPaymentTokensByCustomerId(WorldlinePaymentTokenRequestTransfer $worldlinePaymentTokenRequestTransfer): WorldlinePaymentTokensResponseTransfer;
}
