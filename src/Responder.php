<?php

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Xylemical\Controller\Exception\UnhandledResponseException;

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
   * @param \Xylemical\Controller\ResultInterface $result
   */
  public function applies(RequestInterface $request, ResultInterface $result): bool {
    foreach ($this->responders as $responder) {
      if ($responder->applies($request, $result)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse(RequestInterface $request, ResultInterface $result): ResponseInterface {
    foreach ($this->responders as $responder) {
      if ($responder->applies($request, $result)) {
        return $responder->getResponse($request, $result);
      }
    }
    throw new UnhandledResponseException();
  }

}
