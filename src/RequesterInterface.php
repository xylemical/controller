<?php

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;

/**
 * Provides conversion of request into body.
 */
interface RequesterInterface {

  /**
   * Check the request can handle the request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return bool
   *   The result.
   */
  public function applies(RequestInterface $request): bool;

  /**
   * Get the body from the request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return mixed
   *   The body contents.
   *
   * @throws \Xylemical\Controller\Exception\InvalidBodyException
   */
  public function getBody(RequestInterface $request): mixed;

}
