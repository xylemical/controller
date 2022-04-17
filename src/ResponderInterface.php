<?php

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Provides response generation behaviour.
 */
interface ResponderInterface {

  /**
   * Check the responder can handle the request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ResultInterface $result
   *   The result.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return bool
   *   The result.
   *
   * @throws \Throwable
   */
  public function applies(RequestInterface $request, ResultInterface $result, ContextInterface $context): bool;

  /**
   * Get the response for a request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ResultInterface $result
   *   The result.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   *
   * @throws \Xylemical\Controller\Exception\UnhandledResponseException
   */
  public function getResponse(RequestInterface $request, ResultInterface $result, ContextInterface $context): ResponseInterface;

}
