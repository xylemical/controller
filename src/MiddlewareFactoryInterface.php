<?php

declare(strict_types=1);

namespace Xylemical\Controller;

/**
 * Provides a factory mechanism for middleware.
 */
interface MiddlewareFactoryInterface {

  /**
   * Get the middlewares to be applied to the controller.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   *
   * @return \Xylemical\Controller\MiddlewareInterface[]
   *   The middleware.
   */
  public function getMiddleware(RouteInterface $route): array;

}
