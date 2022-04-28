<?php

declare(strict_types=1);

namespace Xylemical\Controller\Authentication;

use Xylemical\Controller\RouteInterface;

/**
 * Provides an authentication factory mechanism.
 */
interface AuthenticationFactoryInterface {

  /**
   * Get the authentication service.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   *
   * @return \Xylemical\Controller\Authentication\AuthenticationInterface|null
   *   The authentication or NULL.
   */
  public function getAuthentication(RouteInterface $route): ?AuthenticationInterface;

}
