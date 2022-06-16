<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\ResponseInterface;

/**
 * Provides response generation behaviour.
 */
interface ResponderInterface {

  /**
   * Get the response for a request.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   * @param \Xylemical\Controller\ResultInterface $result
   *   The result.
   *
   * @return \Psr\Http\Message\ResponseInterface|null
   *   The response or NULL.
   *
   * @throws \Xylemical\Controller\Exception\UnhandledResponseException
   * @throws \Throwable
   */
  public function getResponse(RouteInterface $route, ResultInterface $result): ?ResponseInterface;

}
