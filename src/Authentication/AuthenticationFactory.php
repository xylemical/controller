<?php

declare(strict_types=1);

namespace Xylemical\Controller\Authentication;

use Psr\Http\Message\RequestInterface;

/**
 * Provide an authentication factory for multiple authentications.
 */
class AuthenticationFactory implements AuthenticationFactoryInterface {

  /**
   * The authentications.
   *
   * @var \Xylemical\Controller\Authentication\AuthenticationInterface[]
   */
  protected array $authentications = [];

  /**
   * Get the authentications.
   *
   * @return \Xylemical\Controller\Authentication\AuthenticationInterface[]
   *   The authentications.
   */
  public function getAuthentications(): array {
    return $this->authentications;
  }

  /**
   * Set the authentications.
   *
   * @param \Xylemical\Controller\Authentication\AuthenticationInterface[] $authentications
   *   The authentications.
   *
   * @return $this
   */
  public function setAuthentications(array $authentications): static {
    $this->authentications = [];
    $this->addAuthentications($authentications);
    return $this;
  }

  /**
   * Add multiple authentications.
   *
   * @param \Xylemical\Controller\Authentication\AuthenticationInterface[] $authentications
   *   The authentications.
   *
   * @return $this
   */
  public function addAuthentications(array $authentications): static {
    foreach ($authentications as $authentication) {
      $this->addAuthentication($authentication);
    }
    return $this;
  }

  /**
   * Add an authentication.
   *
   * @param \Xylemical\Controller\Authentication\AuthenticationInterface $authentication
   *   The authentication.
   *
   * @return $this
   */
  public function addAuthentication(AuthenticationInterface $authentication): static {
    $this->authentications[] = $authentication;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthentication(RequestInterface $request): ?AuthenticationInterface {
    foreach ($this->authentications as $authentication) {
      if ($authentication->applies($request)) {
        return $authentication;
      }
    }

    return NULL;
  }

}
