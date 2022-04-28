<?php

declare(strict_types=1);

namespace Xylemical\Controller\Authentication;

use Xylemical\Account\AccountInterface;
use Xylemical\Controller\RouteInterface;

/**
 * Provides authentication for requests.
 */
interface AuthenticationInterface {

  /**
   * Check the authentication service applies to the request.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   *
   * @return bool
   *   The result.
   */
  public function applies(RouteInterface $route): bool;

  /**
   * Authenticate the route.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   *
   * @return \Xylemical\Account\AccountInterface|null
   *   The account.
   */
  public function authenticate(RouteInterface $route): ?AccountInterface;

}
