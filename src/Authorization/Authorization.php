<?php

declare(strict_types=1);

namespace Xylemical\Controller\Authorization;

use Xylemical\Controller\RouteInterface;

/**
 * A base authorization.
 */
class Authorization implements AuthorizationInterface {

  /**
   * {@inheritdoc}
   */
  public function authorize(RouteInterface $route): bool {
    return FALSE;
  }

}
