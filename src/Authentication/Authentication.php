<?php

declare(strict_types=1);

namespace Xylemical\Controller\Authentication;

use Xylemical\Account\AccountInterface;
use Xylemical\Controller\RouteInterface;

/**
 * Provide a base authentication class.
 */
class Authentication implements AuthenticationInterface {

  /**
   * {@inheritdoc}
   */
  public function authenticate(RouteInterface $route): ?AccountInterface {
    return NULL;
  }

}
