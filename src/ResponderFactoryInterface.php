<?php

declare(strict_types=1);

namespace Xylemical\Controller;

/**
 * Provides a factory mechanism for responders.
 */
interface ResponderFactoryInterface {

  /**
   * Create a responder for a request.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   * @param \Xylemical\Controller\ResultInterface $result
   *   The result.
   *
   * @return \Xylemical\Controller\ResponderInterface|null
   *   The responder.
   */
  public function getResponder(RouteInterface $route, ResultInterface $result): ?ResponderInterface;

}
