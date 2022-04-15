<?php

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;

/**
 * Processes a request into a result.
 */
interface ProcessorInterface {

  /**
   * Check the processor applies to the request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The result.
   * @param mixed $contents
   *   The processed body results.
   *
   * @return bool
   *   The result.
   */
  public function applies(RequestInterface $request, mixed $contents): bool;

  /**
   * Process the request into a result.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param mixed $contents
   *   The processed body.
   *
   * @return \Xylemical\Controller\ResultInterface
   *   The result.
   *
   * @throws \Xylemical\Controller\Exception\AccessException
   * @throws \Xylemical\Controller\Exception\UnavailableException
   * @throws \Xylemical\Controller\Exception\DelayedException
   * @throws \Xylemical\Controller\Exception\ErrorException
   */
  public function getResult(RequestInterface $request, mixed $contents): ResultInterface;

}
