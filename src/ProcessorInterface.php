<?php

declare(strict_types=1);

namespace Xylemical\Controller;

/**
 * Processes a request into a result.
 */
interface ProcessorInterface {

  /**
   * Process the request into a result.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   * @param mixed $contents
   *   The processed body.
   *
   * @return \Xylemical\Controller\ResultInterface|null
   *   The result or NULL.
   *
   * @throws \Xylemical\Controller\Exception\AccessException
   * @throws \Xylemical\Controller\Exception\UnavailableException
   * @throws \Xylemical\Controller\Exception\DelayedException
   * @throws \Xylemical\Controller\Exception\ErrorException
   * @throws \Throwable
   */
  public function getResult(RouteInterface $route, mixed $contents): ?ResultInterface;

}
