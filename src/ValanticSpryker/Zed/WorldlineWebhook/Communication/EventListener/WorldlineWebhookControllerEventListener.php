<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\WorldlineWebhook\Communication\EventListener;

use Generated\Shared\Transfer\WorldlineWebhookRequestTransfer;
use Generated\Shared\Transfer\WorldlineWebhookResponseTransfer;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Zed\Api\Communication\Controller\AbstractApiController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Throwable;
use ValanticSpryker\Shared\WorldlineWebhook\WorldlineWebhookConstants;
use ValanticSpryker\Zed\WorldlineWebhook\Business\WorldlineWebhookFacadeInterface;
use ValanticSpryker\Zed\WorldlineWebhook\Communication\Transformer\TransformerInterface;
use ValanticSpryker\Zed\WorldlineWebhook\WorldlineWebhookConfig;

class WorldlineWebhookControllerEventListener implements WorldlineWebhookControllerEventListenerInterface
{
    use LoggerTrait;

    public const REQUEST_URI = 'REQUEST_URI';

    /**
     * @param \ValanticSpryker\Zed\WorldlineWebhook\Communication\Transformer\TransformerInterface $transformer
     * @param \ValanticSpryker\Zed\WorldlineWebhook\Business\WorldlineWebhookFacadeInterface $webhookFacade
     * @param \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(private TransformerInterface $transformer, private WorldlineWebhookFacadeInterface $webhookFacade, private UtilEncodingServiceInterface $utilEncodingService)
    {
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $controllerEvent
     *
     * @return void
     */
    public function onKernelControllerEvent(ControllerEvent $controllerEvent): void
    {
        $request = $controllerEvent->getRequest();

        if (
            !$request->server->has(static::REQUEST_URI)
            || !str_starts_with($request->server->get(static::REQUEST_URI), WorldlineWebhookConfig::ROUTE_PREFIX_WORLDLINE_WEBHOOK_REST)
        ) {
            return;
        }

        /** @var array $currentController */
        $currentController = $controllerEvent->getController();
        [$controller, $action] = $currentController;

        if (!$controller instanceof AbstractApiController) {
            return;
        }

        $request = $controllerEvent->getRequest();
        $apiController = fn () => $this->executeControllerAction($request, $controller, $action);

        $controllerEvent->setController($apiController);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Spryker\Zed\Api\Communication\Controller\AbstractApiController $controller
     * @param mixed $action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function executeControllerAction(Request $request, AbstractApiController $controller, mixed $action): Response
    {
        $apiRequestTransfer = $this->getApiRequestTransfer($request);
        $this->logRequest($apiRequestTransfer);

        try {
            $responseTransfer = $controller->$action($apiRequestTransfer);
        } catch (Throwable $exception) {
            $responseTransfer = new WorldlineWebhookResponseTransfer();
            $responseTransfer->setCode($this->resolveStatusCode((int)$exception->getCode()));
        }
        $this->logResponse($responseTransfer);

        return $this->transformer->transform($apiRequestTransfer, $responseTransfer, new Response());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer
     */
    private function getApiRequestTransfer(Request $request): WorldlineWebhookRequestTransfer
    {
        $requestTransfer = new WorldlineWebhookRequestTransfer();

        $requestTransfer->setRequestType($request->getMethod());
        $requestTransfer->setQueryData($request->query->all());
        $requestTransfer->setHeaderData($request->headers->all());

        $serverData = $request->server->all();
        $requestTransfer->setServerData($serverData);
        $requestTransfer->setRequestUri($serverData[static::REQUEST_URI]);

        if (str_starts_with((string)$request->headers->get(WorldlineWebhookConstants::HEADER_CONTENT_TYPE), 'application/json')) {
            $content = $request->getContent();
            if (is_resource($content)) {
                $content = stream_get_contents($content);
                $content = $content ?: '{}';
            }

            $data = [$content];

            $request->request->replace($data);
        }

        return $requestTransfer->setRequestData($request->request->all());
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookRequestTransfer $apiRequestTransfer
     *
     * @return void
     */
    private function logRequest(WorldlineWebhookRequestTransfer $apiRequestTransfer): void
    {
        $filteredApiRequestTransfer = $this->webhookFacade->filterApiRequestTransfer($apiRequestTransfer);

        $this->getLogger()->info(sprintf(
            'Worldline webhook request [%s %s]: %s',
            $apiRequestTransfer->getRequestTypeOrFail(),
            $apiRequestTransfer->getRequestUriOrFail(),
            $this->utilEncodingService->encodeJson($filteredApiRequestTransfer->toArray()),
        ));
    }

    /**
     * @param \Generated\Shared\Transfer\WorldlineWebhookResponseTransfer $responseTransfer
     *
     * @return void
     */
    private function logResponse(WorldlineWebhookResponseTransfer $responseTransfer): void
    {
        $responseTransferData = $responseTransfer->toArray();
        unset($responseTransferData['request']);

        $this->getLogger()->info(sprintf(
            'Worldline webhook response [code %s]: %s',
            $responseTransfer->getCodeOrFail(),
            $this->utilEncodingService->encodeJson($responseTransferData),
        ));
    }

    /**
     * @param int $code
     *
     * @return int
     */
    private function resolveStatusCode(int $code): int
    {
        if ($code < WorldlineWebhookConfig::HTTP_CODE_SUCCESS || $code > WorldlineWebhookConfig::HTTP_CODE_INTERNAL_ERROR) {
            return WorldlineWebhookConfig::HTTP_CODE_INTERNAL_ERROR;
        }

        return $code;
    }
}
