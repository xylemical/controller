<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Provides for creating a context from the request.
 */
interface ContextFactoryInterface {

  /**
   * Create a context from the request.
   *
   * @param \Psr\Http\Message\ServerRequestInterface $request
   *   The request.
   *
   * @return \Xylemical\Controller\ContextInterface|null
   *   The context or NULL.
   */
  public function getContext(ServerRequestInterface $request): ?ContextInterface;

}
