<?php

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;

/**
 * Provides an authentication service.
 */
interface AuthenticationInterface {

  /**
   * Authenticate the request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return void
   */
  public function authenticate(RequestInterface $request, ContextInterface $context): void;

}
