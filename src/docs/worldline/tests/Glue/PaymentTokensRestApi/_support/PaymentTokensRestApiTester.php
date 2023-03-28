<?php

declare(strict_types=1);

namespace PyzTest\Glue\PaymentTokensRestApi;

use Generated\Shared\Transfer\CustomerTransfer;

/**
 * Inherited Methods
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 * @SuppressWarnings(PHPMD)
*/
class PaymentTokensRestApiTester extends \Codeception\Actor
{
    use _generated\PaymentTokensRestApiTesterActions;

    /**
     * Define custom actions here
     */

    /**
     * @param string $name
     * @param string $password
     *
     * @return \Generated\Shared\Transfer\CustomerTransfer
     */
    public function createCustomerTransfer(string $name, string $password): CustomerTransfer
    {
        $customerTransfer = $this->haveCustomer([
            CustomerTransfer::USERNAME => $name,
            CustomerTransfer::PASSWORD => $password,
            CustomerTransfer::NEW_PASSWORD => $password,
        ]);

        return $this->confirmCustomer($customerTransfer);
    }
}
