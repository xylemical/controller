<?php

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;
use Xylemical\Controller\Exception\InvalidBodyException;
use Xylemical\Controller\Exception\UnavailableException;

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
  public function applies(RequestInterface $request): bool {
    foreach ($this->requesters as $requester) {
      if ($requester->applies($request)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getBody(RequestInterface $request): mixed {
    foreach ($this->requesters as $requester) {
      if ($requester->applies($request)) {
        return $requester->getBody($request);
      }
    }
    throw new InvalidBodyException();
  }

}
