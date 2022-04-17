<?php

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;

/**
 * Provides for creating a context from the request.
 */
interface ContextFactoryInterface {

  /**
   * Create a context from the request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return \Xylemical\Controller\ContextInterface
   *   The context.
   */
  public function getContext(RequestInterface $request): ContextInterface;

}
