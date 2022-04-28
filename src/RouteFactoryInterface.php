<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;

/**
 * Provides a route factory mechanism.
 */
interface RouteFactoryInterface {

  /**
   * Get the route.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return \Xylemical\Controller\RouteInterface
   *   The route.
   */
  public function getRoute(RequestInterface $request): RouteInterface;

}
