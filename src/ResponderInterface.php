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
   *
   * @return bool
   *   The result.
   */
  public function applies(RequestInterface $request, ResultInterface $result): bool;

  /**
   * Get the response for a request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ResultInterface $result
   *   The result.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   *
   * @throws \Xylemical\Controller\Exception\UnhandledResponseException
   */
  public function getResponse(RequestInterface $request, ResultInterface $result): ResponseInterface;

}
