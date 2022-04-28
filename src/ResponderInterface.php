<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\ResponseInterface;

/**
 * Provides response generation behaviour.
 */
interface ResponderInterface {

  /**
   * Check the responder can handle the request.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   * @param \Xylemical\Controller\ResultInterface $result
   *   The result.
   *
   * @return bool
   *   The result.
   *
   * @throws \Throwable
   */
  public function applies(RouteInterface $route, ResultInterface $result): bool;

  /**
   * Get the response for a request.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   * @param \Xylemical\Controller\ResultInterface $result
   *   The result.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   *
   * @throws \Xylemical\Controller\Exception\UnhandledResponseException
   * @throws \Throwable
   */
  public function getResponse(RouteInterface $route, ResultInterface $result): ResponseInterface;

}
