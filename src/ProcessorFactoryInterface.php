<?php

declare(strict_types=1);

namespace Xylemical\Controller;

/**
 * Provides a factory mechanism for processors.
 */
interface ProcessorFactoryInterface {

  /**
   * Get a processor based on a request.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   * @param mixed $contents
   *   The contents.
   *
   * @return \Xylemical\Controller\ProcessorInterface
   *   The processor.
   */
  public function getProcessor(RouteInterface $route, mixed $contents): ProcessorInterface;

}
