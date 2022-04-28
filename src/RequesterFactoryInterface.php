<?php

declare(strict_types=1);

namespace Xylemical\Controller;

/**
 * Provides a factory mechanism for requesters.
 */
interface RequesterFactoryInterface {

  /**
   * Create a requester.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   *
   * @return \Xylemical\Controller\RequesterInterface
   *   The requester.
   */
  public function getRequester(RouteInterface $route): RequesterInterface;

}
