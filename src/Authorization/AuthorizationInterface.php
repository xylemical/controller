<?php

declare(strict_types=1);

namespace Xylemical\Controller\Authorization;

use Xylemical\Controller\RouteInterface;

/**
 * Provides the authorization behaviour.
 */
interface AuthorizationInterface {

  /**
   * Check the authorization service applies to the request.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   *
   * @return bool
   *   The result.
   */
  public function applies(RouteInterface $route): bool;

  /**
   * Authorize the route.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.s.
   *
   * @return bool
   *   The result.
   */
  public function authorize(RouteInterface $route): bool;

}
