<?php

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;

/**
 * Provides a factory mechanism for responders.
 */
interface ResponderFactoryInterface {

  /**
   * Create a responder for a request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return \Xylemical\Controller\ResponderInterface
   *   The responder.
   */
  public function getResponder(RequestInterface $request): ResponderInterface;

}
