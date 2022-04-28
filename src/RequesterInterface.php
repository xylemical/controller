<?php

declare(strict_types=1);

namespace Xylemical\Controller;

/**
 * Provides conversion of request into body.
 */
interface RequesterInterface {

  /**
   * Check the request can handle the request.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   *
   * @return bool
   *   The result.
   *
   * @throws \Throwable
   */
  public function applies(RouteInterface $route): bool;

  /**
   * Get the body from the request.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   *
   * @return mixed
   *   The body contents.
   *
   * @throws \Xylemical\Controller\Exception\InvalidBodyException
   * @throws \Throwable
   */
  public function getBody(RouteInterface $route): mixed;

}
