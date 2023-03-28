<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Communication\Controller;

use Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Shared\Customer\CustomerConstants;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \ValanticSpryker\Zed\Worldline\Business\WorldlineFacadeInterface getFacade()
 * @method \ValanticSpryker\Zed\Worldline\Communication\WorldlineCommunicationFactory getFactory()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface getQueryContainer()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineRepositoryInterface getRepository()
 */
class TokenController extends AbstractController
{
    public const PARAM_ID_TOKEN = 'id-token';

    /**
     * @var string
     */
    protected const URL_CUSTOMER_LIST = '/customer';

    /**
     * @var string
     */
    protected const URL_CUSTOMER_VIEW = '/customer/view';

    /**
     * @var string
     */
    public const URL_PAYMENT_TOKEN_DELETE_PAGE = '/worldline/token/delete';

    /**
     * @var string
     */
    public const MESSAGE_PAYMENT_TOKEN_DELETE_SUCCESS = 'Payment token was successfully deleted.';

    /**
     * @var string
     */
    public const ERROR_MESSAGE_TOKEN_DOES_NOT_EXIST = 'Payment token does not exist.';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function deleteAction(Request $request): RedirectResponse|array
    {
        $idCustomer = $request->query->getInt(CustomerConstants::PARAM_ID_CUSTOMER);
        $idToken = $this->castId($request->query->get(static::PARAM_ID_TOKEN));

        if (!$idCustomer) {
            return $this->redirectResponse(static::URL_CUSTOMER_LIST);
        }

        $tokenTransfer = $this->getFacade()->findPaymentTokenById($idToken);

        if ($tokenTransfer->getIdToken() === null) {
            $this->addErrorMessage(static::ERROR_MESSAGE_TOKEN_DOES_NOT_EXIST);

            return $this->redirectResponse(
                Url::generate(static::URL_CUSTOMER_VIEW, [
                    CustomerConstants::PARAM_ID_CUSTOMER => $idCustomer,
                ])->build(),
            );
        }

        $paymentTokenDeleteForm = $this->getFactory()->getPaymentTokenDeleteForm();
        $customerViewLink = $this->getFactory()
            ->getRouterFacade()
            ->getBackofficeChainRouter()
            ->generate(
                'customer:view',
                [
                    CustomerConstants::PARAM_ID_CUSTOMER => $idCustomer,
                ],
            );
        $deleteWorldlineTokenRequestTransfer = new WorldlineDeleteTokenRequestTransfer();
        $deleteWorldlineTokenRequestTransfer->setIdToken($idToken);
        $deleteWorldlineTokenRequestTransfer->setIdCustomer($idCustomer);

        return $this->viewResponse([
            'backUrl' => $customerViewLink,
            'paymentTokenDeleteForm' => $paymentTokenDeleteForm->setData($deleteWorldlineTokenRequestTransfer)
                ->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function confirmAction(Request $request): RedirectResponse|array
    {
        $paymentTokenDeleteForm = $this->getFactory()->getPaymentTokenDeleteForm();
        $paymentTokenDeleteForm->handleRequest($request);

        if (!$paymentTokenDeleteForm->isSubmitted()) {
            $this->addErrorMessage('Form was not submitted.');

            return $this->createRedirectResponseToPaymentTokenDeletePage($paymentTokenDeleteForm);
        }

        if (!$paymentTokenDeleteForm->isValid()) {
            foreach ($paymentTokenDeleteForm->getErrors(true) as $formError) {
                /** @var \Symfony\Component\Form\FormError $formError */
                $this->addErrorMessage($formError->getMessage(), $formError->getMessageParameters());
            }

            return $this->createRedirectResponseToPaymentTokenDeletePage($paymentTokenDeleteForm);
        }

        /** @var \Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer $deleteTokenRequestTransfer */
        $deleteTokenRequestTransfer = $paymentTokenDeleteForm->getData();

        $deleteTokenResponseTrasnfer = $this->getFacade()->deletePaymentTokenById($deleteTokenRequestTransfer);

        if (!$deleteTokenResponseTrasnfer->getIsSuccess()) {
            foreach ($deleteTokenResponseTrasnfer->getErrors() as $error) {
                $this->addErrorMessage($error->getCode() . ': ' . $error->getMessage());
            }

            return $this->redirectResponse(self::URL_CUSTOMER_LIST);
        }

        $this->addSuccessMessage(static::MESSAGE_PAYMENT_TOKEN_DELETE_SUCCESS);

        return $this->redirectResponse(
            Url::generate(static::URL_CUSTOMER_VIEW, [
                CustomerConstants::PARAM_ID_CUSTOMER => $deleteTokenRequestTransfer->getIdCustomer(),
            ])->build(),
        );
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $paymentTokenDeleteForm
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function createRedirectResponseToPaymentTokenDeletePage(FormInterface $paymentTokenDeleteForm): RedirectResponse
    {
        /** @var \Generated\Shared\Transfer\WorldlineDeleteTokenRequestTransfer $deleteTokenRequestTransfer */
        $deleteTokenRequestTransfer = $paymentTokenDeleteForm->getData();

        if ($deleteTokenRequestTransfer->getIdCustomer()) {
            return $this->redirectResponse(
                Url::generate(static::URL_PAYMENT_TOKEN_DELETE_PAGE, [
                    CustomerConstants::PARAM_ID_CUSTOMER => $deleteTokenRequestTransfer->getIdCustomer(),
                    static::PARAM_ID_TOKEN => $deleteTokenRequestTransfer->getIdToken(),
                ])->build(),
            );
        }

        return $this->redirectResponse(self::URL_CUSTOMER_LIST);
    }
}
