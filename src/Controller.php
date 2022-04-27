<?php

declare(strict_types=1);
namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Xylemical\Account\AccountInterface;
use Xylemical\Controller\Authentication\AuthenticationInterface;
use Xylemical\Controller\Exception\AccessException;
use Xylemical\Controller\Exception\DelayedException;
use Xylemical\Controller\Exception\UnavailableException;

/**
 * Generic controller behaviour.
 */
class Controller {

  /**
   * The requester.
   *
   * @var \Xylemical\Controller\RequesterInterface
   */
  protected RequesterInterface $requester;

  /**
   * The processor.
   *
   * @var \Xylemical\Controller\ProcessorInterface
   */
  protected ProcessorInterface $processor;

  /**
   * The responder.
   *
   * @var \Xylemical\Controller\ResponderInterface
   */
  protected ResponderInterface $responder;

  /**
   * The context factory.
   *
   * @var \Xylemical\Controller\ContextFactoryInterface
   */
  protected ContextFactoryInterface $factory;

  /**
   * The authentication layer.
   *
   * @var \Xylemical\Controller\Authentication\AuthenticationInterface|null
   */
  protected ?AuthenticationInterface $authentication = NULL;

  /**
   * The middleware.
   *
   * @var \Xylemical\Controller\MiddlewareInterface[][]
   */
  protected array $middleware = [];

  /**
   * Controller constructor.
   *
   * @param \Xylemical\Controller\ContextFactoryInterface $factory
   *   The request.
   * @param \Xylemical\Controller\RequesterInterface $requester
   *   The responder.
   * @param \Xylemical\Controller\ProcessorInterface $processor
   *   The processor.
   * @param \Xylemical\Controller\ResponderInterface $responder
   *   The context factory.
   */
  public function __construct(ContextFactoryInterface $factory, RequesterInterface $requester, ProcessorInterface $processor, ResponderInterface $responder) {
    $this->requester = $requester;
    $this->responder = $responder;
    $this->processor = $processor;
    $this->factory = $factory;
  }

  /**
   * Get the requester.
   *
   * @return \Xylemical\Controller\RequesterInterface
   *   The requester.
   */
  public function getRequester(): RequesterInterface {
    return $this->requester;
  }

  /**
   * Get the processor.
   *
   * @return \Xylemical\Controller\ProcessorInterface
   *   The processor.
   */
  public function getProcessor(): ProcessorInterface {
    return $this->processor;
  }

  /**
   * Get the responder.
   *
   * @return \Xylemical\Controller\ResponderInterface
   *   The responder.
   */
  public function getResponder(): ResponderInterface {
    return $this->responder;
  }

  /**
   * Get the middleware.
   *
   * @return \Xylemical\Controller\MiddlewareInterface[]
   *   The middleware.
   */
  public function getMiddleware(): array {
    $middleware = [];
    foreach ($this->middleware as $middlewares) {
      $middleware = array_merge($middleware, $middlewares);
    }
    return $middleware;
  }

  /**
   * Set the middleware.
   *
   * @param \Xylemical\Controller\MiddlewareInterface[] $middleware
   *   The middleware.
   *
   * @return $this
   */
  public function setMiddleware(array $middleware): static {
    $this->middleware = [];
    foreach ($middleware as $item) {
      $this->addMiddleware($item);
    }
    return $this;
  }

  /**
   * Add a middleware.
   *
   * @param \Xylemical\Controller\MiddlewareInterface $middleware
   *   The middleware.
   *
   * @return $this
   */
  public function addMiddleware(MiddlewareInterface $middleware): static {
    $this->middleware[$middleware->priority()][] = $middleware;
    return $this;
  }

  /**
   * Set the authentication layer.
   *
   * @return \Xylemical\Controller\Authentication\AuthenticationInterface|null
   *   The authentication or NULL.
   */
  public function getAuthentication(): ?AuthenticationInterface {
    return $this->authentication;
  }

  /**
   * Set the authentication layer.
   *
   * @param \Xylemical\Controller\Authentication\AuthenticationInterface|null $authentication
   *   The authentication.
   *
   * @return $this
   */
  public function setAuthentication(?AuthenticationInterface $authentication): static {
    $this->authentication = $authentication;
    return $this;
  }

  /**
   * Handles the request and response.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   */
  public function handle(RequestInterface $request): ResponseInterface {
    try {
      $context = $this->factory->getContext($request);
      $request = $this->doAuthentication($request, $context);
      $request = $this->doRequest($request, $context);
      $body = $this->requester->getBody($request, $context);
    }
    catch (\Throwable $e) {
      $result = Result::exception($e->getCode(), $e->getMessage());
      $context = new Context();
    }

    if (!isset($result)) {
      try {
        if (!$this->processor->applies($request, $body ?? NULL, $context)) {
          throw new \Exception('No available processor.');
        }
        $result = $this->processor->getResult($request, $body ?? NULL, $context);
      }
      catch (AccessException $e) {
        $result = Result::access($e->getMessage());
      }
      catch (DelayedException $e) {
        $result = Result::delayed($e->getMessage());
      }
      catch (UnavailableException $e) {
        $result = Result::unavailable($e->getMessage());
      }
      catch (\Throwable $e) {
        $result = Result::exception($e->getCode(), $e->getMessage());
      }
    }

    try {
      if ($this->responder->applies($request, $result, $context)) {
        return $this->doResponse(
          $this->responder->getResponse($request, $result, $context),
          $context
        );
      }
      throw new \Exception('The responder is unable to respond.');
    }
    catch (\Throwable $e) {
      return (new Response())->withStatus(
        $e->getCode() ?: 500,
        $e->getMessage()
      );
    }
  }

  /**
   * Perform the authentication.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return \Psr\Http\Message\RequestInterface
   *   The request.
   */
  protected function doAuthentication(RequestInterface $request, ContextInterface $context): RequestInterface {
    if ($this->authentication) {
      $this->authentication->setRequest($request);
      $account = $this->authentication->authenticate();
      $context->set(AccountInterface::class, $account);
    }
    return $request;
  }

  /**
   * Perform middleware requests.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return \Psr\Http\Message\RequestInterface
   *   The request.
   *
   * @throws \Throwable
   */
  protected function doRequest(RequestInterface $request, ContextInterface $context): RequestInterface {
    krsort($this->middleware);
    foreach ($this->middleware as $middlewares) {
      foreach ($middlewares as $middleware) {
        $request = $middleware->request($this, $request, $context);
      }
    }
    return $request;
  }

  /**
   * Perform middle responses.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The response.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   *
   * @throws \Throwable
   */
  protected function doResponse(ResponseInterface $response, ContextInterface $context): ResponseInterface {
    ksort($this->middleware);
    foreach ($this->middleware as $middlewares) {
      foreach (array_reverse($middlewares) as $middleware) {
        $response = $middleware->response($this, $response, $context);
      }
    }
    return $response;
  }

}
