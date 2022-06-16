<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\ResponseInterface;

/**
 * Provides generic responder.
 */
class Responder implements ResponderInterface {

  /**
   * The responders.
   *
   * @var \Xylemical\Controller\ResponderInterface[]
   */
  protected array $responders = [];

  /**
   * Responder constructor.
   *
   * @param \Xylemical\Controller\ResponderInterface[] $responders
   *   The initial responders.
   */
  public function __construct(array $responders = []) {
    foreach ($responders as $responder) {
      $this->addResponder($responder);
    }
  }

  /**
   * Add a responder.
   *
   * @param \Xylemical\Controller\ResponderInterface $responder
   *   The responder.
   *
   * @return $this
   */
  public function addResponder(ResponderInterface $responder): static {
    $this->responders[] = $responder;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse(RouteInterface $route, ResultInterface $result): ?ResponseInterface {
    foreach ($this->responders as $responder) {
      if ($response = $responder->getResponse($route, $result)) {
        return $response;
      }
    }
    return NULL;
  }

}
