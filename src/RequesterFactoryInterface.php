<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;

/**
 * Provides a factory mechanism for requesters.
 */
interface RequesterFactoryInterface {

  /**
   * Create a requester.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return \Xylemical\Controller\RequesterInterface
   *   The requester.
   */
  public function getRequester(RequestInterface $request): RequesterInterface;

}
