<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;

/**
 * Provides a factory mechanism for middleware.
 */
interface MiddlewareFactoryInterface {

  /**
   * Get the middlewares to be applied to the controller.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return \Xylemical\Controller\MiddlewareInterface[]
   *   The middleware.
   */
  public function getMiddleware(RequestInterface $request): array;

}
