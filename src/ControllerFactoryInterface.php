<?php

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;

/**
 * Provides for generating controllers.
 */
interface ControllerFactoryInterface {

  /**
   * Create the controller.
   *
   * @param \Xylemical\Controller\ContextFactoryInterface $contextFactory
   *   The context factory.
   * @param \Xylemical\Controller\RequesterFactoryInterface $requesterFactory
   *   The requester factory.
   * @param \Xylemical\Controller\ProcessorFactoryInterface $processorFactory
   *   The processor factory.
   * @param \Xylemical\Controller\ResponderFactoryInterface $responderFactory
   *   The responder factory.
   * @param \Xylemical\Controller\MiddlewareFactoryInterface $middlewareFactory
   *   The middleware factory.
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return \Xylemical\Controller\Controller
   *   The controller.
   */
  public function getController(ContextFactoryInterface $contextFactory,
                                RequesterFactoryInterface $requesterFactory,
                                ProcessorFactoryInterface $processorFactory,
                                ResponderFactoryInterface $responderFactory,
                                MiddlewareFactoryInterface $middlewareFactory,
                                RequestInterface $request): Controller;

}
