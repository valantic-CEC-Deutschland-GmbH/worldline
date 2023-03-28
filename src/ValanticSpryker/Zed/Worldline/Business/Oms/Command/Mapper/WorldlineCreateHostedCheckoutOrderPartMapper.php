<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Oms\Command\Mapper;

use DateTime;
use DateTimeZone;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\AmountOfMoneyTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\PaymentWorldlineTransfer;
use Generated\Shared\Transfer\WorldlineAccountTransfer;
use Generated\Shared\Transfer\WorldlineAdditionalOrderInputTransfer;
use Generated\Shared\Transfer\WorldlineAddressPersonalTransfer;
use Generated\Shared\Transfer\WorldlineAddressTransfer;
use Generated\Shared\Transfer\WorldlineAuthenticationTransfer;
use Generated\Shared\Transfer\WorldlineBrowserDataTransfer;
use Generated\Shared\Transfer\WorldlineCreateHostedCheckoutTransfer;
use Generated\Shared\Transfer\WorldlineCustomerTransfer;
use Generated\Shared\Transfer\WorldlineDeviceTransfer;
use Generated\Shared\Transfer\WorldlineOrderReferencesTransfer;
use Generated\Shared\Transfer\WorldlineOrderTransfer;
use Generated\Shared\Transfer\WorldlineOrderTypeInformationTransfer;
use Generated\Shared\Transfer\WorldlinePersonalNameTransfer;
use Generated\Shared\Transfer\WorldlineShippingTransfer;
use Spryker\Shared\Kernel\Store;

class WorldlineCreateHostedCheckoutOrderPartMapper implements WorldlineCreateHostedCheckoutPartMapperInterface
{
    /**
     * @inheritDoc
     */
    public function map(WorldlineCreateHostedCheckoutTransfer $worldlineCreateHostedCheckoutTransfer, OrderTransfer $orderTransfer, PaymentWorldlineTransfer $paymentWorldlineTransfer): WorldlineCreateHostedCheckoutTransfer
    {
        $worldlineCreateHostedCheckoutTransfer->setOrder($this->getWorldlineOrderTransfer($orderTransfer, $paymentWorldlineTransfer));

        return $worldlineCreateHostedCheckoutTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\PaymentWorldlineTransfer $worldlinePaymentTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineOrderTransfer
     */
    private function getWorldlineOrderTransfer(OrderTransfer $orderTransfer, PaymentWorldlineTransfer $worldlinePaymentTransfer): WorldlineOrderTransfer
    {
        $worldlineOrderTransfer = new WorldlineOrderTransfer();

        $worldlineOrderTransfer->setAdditionalInput($this->getOrderAdditionalInformation($orderTransfer));
        $worldlineOrderTransfer->setAmountOfMoney($this->getAmountOfMoney($orderTransfer));
        $worldlineOrderTransfer->setReferences($this->getOrderReferences($worldlinePaymentTransfer));
        $worldlineOrderTransfer->setCustomer($this->getWorldlineCustomerTransfer($orderTransfer, $worldlinePaymentTransfer));
        $worldlineOrderTransfer->setShipping($this->getWorldlineShippingTransfer($orderTransfer));

        return $worldlineOrderTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineAdditionalOrderInputTransfer
     */
    private function getOrderAdditionalInformation(OrderTransfer $orderTransfer): WorldlineAdditionalOrderInputTransfer
    {
        $orderAdditionalInput = new WorldlineAdditionalOrderInputTransfer();

        $creationDateString = $orderTransfer->getCreatedAt();
        $creationDate = new DateTime($creationDateString);

        $orderAdditionalInput->setOrderDate($creationDate->format('YmdHis'));
        $orderAdditionalInput->setTypeInformation($this->getTypeInformation());

        return $orderAdditionalInput;
    }

    /**
     * @return \Generated\Shared\Transfer\WorldlineOrderTypeInformationTransfer
     */
    private function getTypeInformation(): WorldlineOrderTypeInformationTransfer
    {
        $typeInformation = new WorldlineOrderTypeInformationTransfer();

        $typeInformation->setPurchaseType('digital');
        $typeInformation->setTransactionType('purchase');
        $typeInformation->setUsageType('private');

        return $typeInformation;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\AmountOfMoneyTransfer
     */
    private function getAmountOfMoney(OrderTransfer $orderTransfer): AmountOfMoneyTransfer
    {
        $orderTransfer->requireCurrency()->requireTotals();

        $amountOfMoneyTransfer = new AmountOfMoneyTransfer();

        $amountOfMoneyTransfer->setAmount($orderTransfer->getTotals()->getGrandTotal());
        $amountOfMoneyTransfer->setCurrencyCode($orderTransfer->getCurrency()->getCode());

        return $amountOfMoneyTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\PaymentWorldlineTransfer $worldlinePaymentTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineOrderReferencesTransfer
     */
    private function getOrderReferences(PaymentWorldlineTransfer $worldlinePaymentTransfer): WorldlineOrderReferencesTransfer
    {
        $orderReferencesTransfer = new WorldlineOrderReferencesTransfer();

        $orderReferencesTransfer->setMerchantReference($worldlinePaymentTransfer->getMerchantReference());

        return $orderReferencesTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\PaymentWorldlineTransfer $worldlinePaymentTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineCustomerTransfer
     */
    private function getWorldlineCustomerTransfer(OrderTransfer $orderTransfer, PaymentWorldlineTransfer $worldlinePaymentTransfer): WorldlineCustomerTransfer
    {
        $worldlineCustomerTransfer = new WorldlineCustomerTransfer();

        $worldlineCustomerTransfer->setMerchantCustomerId($orderTransfer->getCustomerReference());
        $worldlineCustomerTransfer->setBillingAddress($this->getWorldlineBillingAddress($orderTransfer));
        $worldlineCustomerTransfer->setAccount($this->getCustomerAccount($orderTransfer));
        $worldlineCustomerTransfer->setLocale(Store::getInstance()->getCurrentLocale());
        $worldlineCustomerTransfer->setDevice($this->getDevice($worldlinePaymentTransfer));

        return $worldlineCustomerTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineAddressTransfer
     */
    private function getWorldlineBillingAddress(OrderTransfer $orderTransfer): WorldlineAddressTransfer
    {
        $orderTransfer->requireBillingAddress();
        $worldlineAddressTransfer = new WorldlineAddressTransfer();

        $worldlineAddressTransfer->setCountryCode($orderTransfer->getBillingAddress()->getIso2Code());
        $worldlineAddressTransfer->setCity($orderTransfer->getBillingAddress()->getCity());
        $worldlineAddressTransfer->setStreet($orderTransfer->getBillingAddress()->getAddress1());
        $worldlineAddressTransfer->setHouseNumber($orderTransfer->getBillingAddress()->getAddress2());
        $worldlineAddressTransfer->setZip($orderTransfer->getBillingAddress()->getZipCode());

        return $worldlineAddressTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineAccountTransfer
     */
    private function getCustomerAccount(OrderTransfer $orderTransfer): WorldlineAccountTransfer
    {
        $accountTransfer = new WorldlineAccountTransfer();

        $accountTransfer->setAuthentication(
            (new WorldlineAuthenticationTransfer())
                ->setMethod('merchant-credentials')
                ->setUtcTimestamp((new DateTime())->setTimezone(new DateTimeZone('UTC'))->format('YmdHi')),
        );
        $accountTransfer->setChangeDate(
            (new DateTime($orderTransfer->getCustomer()->getUpdatedAt()))->format('Ymd'),
        );
        $accountTransfer->setChangedDuringCheckout(false);
        $accountTransfer->setCreateDate(
            (new DateTime($orderTransfer->getCustomer()->getCreatedAt()))->format('Ymd'),
        );
        $accountTransfer->setHadSuspiciousActivity(false);
        $accountTransfer->setHasForgottenPassword(false);
        $accountTransfer->setHasPassword(true);

        return $accountTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\PaymentWorldlineTransfer $worldlinePaymentTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineDeviceTransfer
     */
    private function getDevice(PaymentWorldlineTransfer $worldlinePaymentTransfer): WorldlineDeviceTransfer
    {
        $worldlinePaymentTransfer->requirePaymentHostedCheckout();

        $deviceTransfer = new WorldlineDeviceTransfer();

        $paymentHostedCheckout = $worldlinePaymentTransfer->getPaymentHostedCheckout();

        $deviceTransfer->setBrowserData(
            (new WorldlineBrowserDataTransfer())
                ->setColorDepth($paymentHostedCheckout->getCustomerColorDepth())
                ->setScreenHeight($paymentHostedCheckout->getCustomerScreenHeight())
                ->setScreenWidth($paymentHostedCheckout->getCustomerScreenWidth())
                ->setJavaEnabled($paymentHostedCheckout->getCustomerJavaEnabled())
                ->setJavaScriptEnabled(true),
        );
        $deviceTransfer->setIpAddress($paymentHostedCheckout->getCustomerIpAddress());
        $deviceTransfer->setLocale($paymentHostedCheckout->getCustomerSelectedLocale() ?? $paymentHostedCheckout->getCustomerBrowserLocale());

        return $deviceTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\WorldlineShippingTransfer
     */
    private function getWorldlineShippingTransfer(OrderTransfer $orderTransfer): WorldlineShippingTransfer
    {
        $billingAddress = $orderTransfer->getBillingAddress();
        $itemShippingAddressTransfer = $this->pickFirstSetItemShippingAddress($orderTransfer);

        $worldlineShippingTransfer = new WorldlineShippingTransfer();
        if ($itemShippingAddressTransfer === null || $this->billingAndShippingAddressMatch($billingAddress, $itemShippingAddressTransfer)) {
            $worldlineShippingTransfer->setAddressIndicator('same-as-billing');
        } else {
            $worldlineShippingTransfer->setAddressIndicator('different-than-billing');
            $worldlineShippingTransfer->setAddress(
                (new WorldlineAddressPersonalTransfer())
                    ->setCountryCode($itemShippingAddressTransfer->getIso2Code())
                    ->setCity($itemShippingAddressTransfer->getCity())
                    ->setStreet($itemShippingAddressTransfer->getAddress1())
                    ->setHouseNumber($itemShippingAddressTransfer->getAddress2())
                    ->setZip($itemShippingAddressTransfer->getZipCode())
                    ->setName(
                        (new WorldlinePersonalNameTransfer())
                            ->setFirstName($itemShippingAddressTransfer->getFirstName())
                            ->setSurname($itemShippingAddressTransfer->getLastName()),
                    ),
            );
        }

        return $worldlineShippingTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\AddressTransfer|null
     */
    private function pickFirstSetItemShippingAddress(OrderTransfer $orderTransfer): ?AddressTransfer
    {
        foreach ($orderTransfer->getItems() as $orderItem) {
            $shippingAddress = $orderItem->getShipment()?->getShippingAddress();
            if ($shippingAddress !== null) {
                return $shippingAddress;
            }
        }

        return null;
    }

    /**
     * @param \Generated\Shared\Transfer\AddressTransfer $billingAddress
     * @param \Generated\Shared\Transfer\AddressTransfer $itemShippingAddressTransfer
     *
     * @return bool
     */
    private function billingAndShippingAddressMatch(AddressTransfer $billingAddress, AddressTransfer $itemShippingAddressTransfer): bool
    {
        return ($billingAddress->getIdSalesOrderAddress() === $itemShippingAddressTransfer->getIdSalesOrderAddress());
    }
}
