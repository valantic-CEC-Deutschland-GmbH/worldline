<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Communication\Controller;

use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \ValanticSpryker\Zed\Worldline\Business\WorldlineFacadeInterface getFacade()()
 * @method \ValanticSpryker\Zed\Worldline\Communication\WorldlineCommunicationFactory getFactory()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineQueryContainerInterface getQueryContainer()
 * @method \ValanticSpryker\Zed\Worldline\Persistence\WorldlineRepositoryInterface getRepository()
 */
class SalesController extends AbstractController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function listAction(Request $request): array
    {
        $orderTransfer = new OrderTransfer();
        $orderTransfer->unserialize((string)$request->request->get('serializedOrderTransfer'));

        $table = $this->getFactory()->createTransactionStatusLogTable($orderTransfer);

        return [
            'transactionStatusLogTable' => $table->render(),
        ];
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function listTableAction(Request $request): JsonResponse
    {
        /** @var string $orderReference */
        $orderReference = $request->get('orderReference');

        $orderTransfer = new OrderTransfer();
        $orderTransfer->setOrderReference($orderReference);

        $table = $this->getFactory()->createTransactionStatusLogTable($orderTransfer);

        return $this->jsonResponse(
            $table->fetchData(),
        );
    }
}
