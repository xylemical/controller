<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;

/**
 * Provides a factory mechanism for processors.
 */
interface ProcessorFactoryInterface {

  /**
   * Get a processor based on a request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return \Xylemical\Controller\ProcessorInterface
   *   The processor.
   */
  public function getProcessor(RequestInterface $request): ProcessorInterface;

}
