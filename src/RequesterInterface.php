<?php

declare(strict_types=1);

namespace Xylemical\Controller;

/**
 * Provides conversion of request into body.
 */
interface RequesterInterface {

  /**
   * Get the body from the request.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   *
   * @return mixed|null
   *   The body contents or NULL.
   *
   * @throws \Xylemical\Controller\Exception\InvalidBodyException
   * @throws \Throwable
   */
  public function getBody(RouteInterface $route): mixed;

}
