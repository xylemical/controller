<?php

declare(strict_types=1);

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
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return bool
   *   The result.
   *
   * @throws \Throwable
   */
  public function applies(RequestInterface $request, ContextInterface $context): bool;

  /**
   * Get the body from the request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return mixed
   *   The body contents.
   *
   * @throws \Xylemical\Controller\Exception\InvalidBodyException
   * @throws \Throwable
   */
  public function getBody(RequestInterface $request, ContextInterface $context): mixed;

}
