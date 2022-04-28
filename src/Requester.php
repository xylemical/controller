<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Xylemical\Controller\Exception\InvalidBodyException;

/**
 * Provides a generic requester.
 */
class Requester implements RequesterInterface {

  /**
   * The requesters.
   *
   * @var \Xylemical\Controller\RequesterInterface[]
   */
  protected array $requesters = [];

  /**
   * Requester constructor.
   *
   * @param \Xylemical\Controller\RequesterInterface[] $requesters
   *   The initial requesters.
   */
  public function __construct(array $requesters = []) {
    foreach ($requesters as $requester) {
      $this->addRequester($requester);
    }
  }

  /**
   * Add a requester to the requesters.
   *
   * @param \Xylemical\Controller\RequesterInterface $requester
   *   The requester.
   *
   * @return $this
   */
  public function addRequester(RequesterInterface $requester): static {
    $this->requesters[] = $requester;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteInterface $route): bool {
    foreach ($this->requesters as $requester) {
      if ($requester->applies($route)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getBody(RouteInterface $route): mixed {
    foreach ($this->requesters as $requester) {
      if ($requester->applies($route)) {
        return $requester->getBody($route);
      }
    }
    throw new InvalidBodyException();
  }

}
