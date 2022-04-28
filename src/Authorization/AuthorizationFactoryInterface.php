<?php

declare(strict_types=1);

namespace Xylemical\Controller\Authorization;

use Xylemical\Controller\RouteInterface;

/**
 * Provides an authorization factory mechanism.
 */
interface AuthorizationFactoryInterface {

  /**
   * Get the authorization service.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   *
   * @return \Xylemical\Controller\Authorization\AuthorizationInterface|null
   *   The authorization or NULL.
   */
  public function getAuthorization(RouteInterface $route): ?AuthorizationInterface;

}
