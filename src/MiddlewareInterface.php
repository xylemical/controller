<?php

declare(strict_types=1);
namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;
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
   * @param \Xylemical\Controller\Controller $controller
   *   The controller.
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return \Psr\Http\Message\RequestInterface
   *   The request.
   *
   * @throws \Throwable
   */
  public function request(Controller $controller, RequestInterface $request, ContextInterface $context): RequestInterface;

  /**
   * Process the response.
   *
   * @param \Xylemical\Controller\Controller $controller
   *   The controller.
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The response.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The updated response.
   *
   * @throws \Throwable
   */
  public function response(Controller $controller, ResponseInterface $response, ContextInterface $context): ResponseInterface;

}
