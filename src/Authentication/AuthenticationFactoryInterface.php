<?php

declare(strict_types=1);

namespace Xylemical\Controller\Authentication;

use Psr\Http\Message\RequestInterface;

/**
 * Provides an authentication factory mechanism.
 */
interface AuthenticationFactoryInterface {

  /**
   * Get the authentication service.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return \Xylemical\Controller\Authentication\AuthenticationInterface|null
   *   The authentication or NULL.
   */
  public function getAuthentication(RequestInterface $request): ?AuthenticationInterface;

}
