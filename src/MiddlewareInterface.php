<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\ResponseInterface;

/**
 * Process middleware that surrounds the request/response process.
 */
interface MiddlewareInterface {

  /**
   * The priority of the middleware.
   *
   * @return int
   *   The priority.
   */
  public function priority(): int;

  /**
   * Process the request.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   *
   * @throws \Throwable
   */
  public function request(RouteInterface $route): void;

  /**
   * Process the response.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The response.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The updated response.
   *
   * @throws \Throwable
   */
  public function response(RouteInterface $route, ResponseInterface $response): ResponseInterface;

}
