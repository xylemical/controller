<?php

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;

/**
 * Provides a generic controller factory.
 */
class ControllerFactory implements ControllerFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function getController(ContextFactoryInterface $contextFactory, RequesterFactoryInterface $requesterFactory, ProcessorFactoryInterface $processorFactory, ResponderFactoryInterface $responderFactory, MiddlewareFactoryInterface $middlewareFactory, RequestInterface $request): Controller {
    $controller = new Controller(
      $contextFactory,
      $requesterFactory->getRequester($request),
      $processorFactory->getProcessor($request),
      $responderFactory->getResponder($request)
    );
    foreach ($middlewareFactory->getMiddleware($request) as $middleware) {
      $controller->addMiddleware($middleware);
    }
    return $controller;
  }

}
