<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Provides a route factory mechanism.
 */
interface RouteFactoryInterface {

  /**
   * Get the route.
   *
   * @param \Psr\Http\Message\ServerRequestInterface $request
   *   The request.
   *
   * @return \Xylemical\Controller\RouteInterface|null
   *   The route or NULL.
   */
  public function getRoute(ServerRequestInterface $request): ?RouteInterface;

}
