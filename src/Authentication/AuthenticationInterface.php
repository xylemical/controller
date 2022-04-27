<?php

declare(strict_types=1);

namespace Xylemical\Controller\Authentication;

use Psr\Http\Message\RequestInterface;
use Xylemical\Account\Authentication\AuthenticationInterface as BaseAuthenticationInterface;

/**
 * Provides authentication for requests.
 */
interface AuthenticationInterface extends BaseAuthenticationInterface {

  /**
   * Check the authentication service applies to the request.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return bool
   *   The result.
   */
  public function applies(RequestInterface $request): bool;

  /**
   * Get the request interface.
   *
   * @return \Psr\Http\Message\RequestInterface|null
   *   The request interface.
   */
  public function getRequest(): ?RequestInterface;

  /**
   * Set the request for authentication.
   *
   * @param \Psr\Http\Message\RequestInterface|null $request
   *   The request.
   *
   * @return $this
   */
  public function setRequest(?RequestInterface $request): static;

}
