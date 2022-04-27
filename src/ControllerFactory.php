<?php

declare(strict_types=1);
namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;
use Xylemical\Controller\Authentication\AuthenticationFactoryInterface;

/**
 * Provides a generic controller factory.
 */
class ControllerFactory implements ControllerFactoryInterface {

  /**
   * The context factory.
   *
   * @var \Xylemical\Controller\ContextFactoryInterface
   */
  protected ContextFactoryInterface $contextFactory;

  /**
   * The requester factory.
   *
   * @var \Xylemical\Controller\RequesterFactoryInterface
   */
  protected RequesterFactoryInterface $requesterFactory;

  /**
   * The processor factory.
   *
   * @var \Xylemical\Controller\ProcessorFactoryInterface
   */
  protected ProcessorFactoryInterface $processorFactory;

  /**
   * The responder factory.
   *
   * @var \Xylemical\Controller\ResponderFactoryInterface
   */
  protected ResponderFactoryInterface $responderFactory;

  /**
   * The middleware factory.
   *
   * @var \Xylemical\Controller\MiddlewareFactoryInterface
   */
  protected MiddlewareFactoryInterface $middlewareFactory;

  /**
   * The authentication factory.
   *
   * @var \Xylemical\Controller\Authentication\AuthenticationFactoryInterface|null
   */
  protected ?AuthenticationFactoryInterface $authenticationFactory = NULL;

  /**
   * ControllerFactory constructor.
   *
   * @param \Xylemical\Controller\ContextFactoryInterface $contextFactory
   *   The context factory.
   * @param \Xylemical\Controller\RequesterFactoryInterface $requesterFactory
   *   The requester factory.
   * @param \Xylemical\Controller\ProcessorFactoryInterface $processorFactory
   *   The processor factory.
   * @param \Xylemical\Controller\ResponderFactoryInterface $responderFactory
   *   The responder factory.
   * @param \Xylemical\Controller\MiddlewareFactoryInterface $middlewareFactory
   *   The middleware factory.
   */
  public function __construct(ContextFactoryInterface $contextFactory, RequesterFactoryInterface $requesterFactory, ProcessorFactoryInterface $processorFactory, ResponderFactoryInterface $responderFactory, MiddlewareFactoryInterface $middlewareFactory) {
    $this->contextFactory = $contextFactory;
    $this->requesterFactory = $requesterFactory;
    $this->processorFactory = $processorFactory;
    $this->responderFactory = $responderFactory;
    $this->middlewareFactory = $middlewareFactory;
  }

  /**
   * Get the authentication factory.
   *
   * @return \Xylemical\Controller\Authentication\AuthenticationFactoryInterface|null
   *   The authentication factory.
   */
  public function getAuthenticationFactory(): ?AuthenticationFactoryInterface {
    return $this->authenticationFactory;
  }

  /**
   * Set the authentication factory.
   *
   * @param \Xylemical\Controller\Authentication\AuthenticationFactoryInterface|null $authenticationFactory
   *   The authentication factory.
   *
   * @return $this
   */
  public function setAuthenticationFactory(?AuthenticationFactoryInterface $authenticationFactory): static {
    $this->authenticationFactory = $authenticationFactory;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getController(RequestInterface $request): Controller {
    $controller = new Controller(
      $this->contextFactory,
      $this->requesterFactory->getRequester($request),
      $this->processorFactory->getProcessor($request),
      $this->responderFactory->getResponder($request)
    );
    foreach ($this->middlewareFactory->getMiddleware($request) as $middleware) {
      $controller->addMiddleware($middleware);
    }
    if ($this->authenticationFactory) {
      $controller->setAuthentication($this->authenticationFactory->getAuthentication($request));
    }
    return $controller;
  }

}
