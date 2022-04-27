<?php

declare(strict_types=1);

namespace Xylemical\Controller\Authentication;

use Psr\Http\Message\RequestInterface;
use Xylemical\Account\AccountInterface;

/**
 * Provide a base authentication class.
 */
class Authentication implements AuthenticationInterface {

  /**
   * The request.
   *
   * @var \Psr\Http\Message\RequestInterface|null
   */
  protected ?RequestInterface $request = NULL;

  /**
   * {@inheritdoc}
   */
  public function applies(RequestInterface $request): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getRequest(): ?RequestInterface {
    return $this->request;
  }

  /**
   * {@inheritdoc}
   */
  public function setRequest(?RequestInterface $request): static {
    $this->request = $request;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate(): ?AccountInterface {
    return NULL;
  }

}
