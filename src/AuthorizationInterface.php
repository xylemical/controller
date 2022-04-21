<?php

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;

/**
 * Provides authorization for access to the request.
 */
interface AuthorizationInterface {

  /**
   * Authorize a user to use the controller.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @throws \Xylemical\Controller\Exception\AccessException
   */
  public function authorize(RequestInterface $request, ContextInterface $context): void;

}
