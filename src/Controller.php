<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\ResponseInterface;
use Xylemical\Controller\Authentication\AuthenticationFactoryInterface;
use Xylemical\Controller\Authorization\AuthorizationFactoryInterface;
use Xylemical\Controller\Exception\AccessException;
use Xylemical\Controller\Exception\DelayedException;
use Xylemical\Controller\Exception\UnavailableException;

/**
 * Provides the controller processing behaviour.
 */
final class Controller {

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
   * The middleware.
   *
   * @var \Xylemical\Controller\MiddlewareInterface[][]
   */
  protected array $middleware = [];

  /**
   * The authentication layer.
   *
   * @var \Xylemical\Controller\Authentication\AuthenticationFactoryInterface|null
   */
  protected ?AuthenticationFactoryInterface $authenticationFactory;

  /**
   * The authorization layer.
   *
   * @var \Xylemical\Controller\Authorization\AuthorizationFactoryInterface|null
   */
  protected ?AuthorizationFactoryInterface $authorizationFactory;

  /**
   * Controller constructor.
   *
   * @param \Xylemical\Controller\RequesterFactoryInterface $requesterFactory
   *   The requester factory.
   * @param \Xylemical\Controller\ProcessorFactoryInterface $processorFactory
   *   The processor factory.
   * @param \Xylemical\Controller\ResponderFactoryInterface $responderFactory
   *   The responder factory.
   * @param \Xylemical\Controller\MiddlewareFactoryInterface $middlewareFactory
   *   The middleware factory.
   * @param \Xylemical\Controller\Authentication\AuthenticationFactoryInterface|null $authenticationFactory
   *   The authentication factory.
   * @param \Xylemical\Controller\Authorization\AuthorizationFactoryInterface|null $authorizationFactory
   *   The authorization factory.
   */
  public function __construct(RequesterFactoryInterface $requesterFactory, ProcessorFactoryInterface $processorFactory, ResponderFactoryInterface $responderFactory, MiddlewareFactoryInterface $middlewareFactory, ?AuthenticationFactoryInterface $authenticationFactory = NULL, ?AuthorizationFactoryInterface $authorizationFactory = NULL) {
    $this->requesterFactory = $requesterFactory;
    $this->responderFactory = $responderFactory;
    $this->processorFactory = $processorFactory;
    $this->middlewareFactory = $middlewareFactory;
    $this->authenticationFactory = $authenticationFactory;
    $this->authorizationFactory = $authorizationFactory;
  }

  /**
   * Handles the request and response.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   */
  public function handle(RouteInterface $route): ResponseInterface {
    try {
      $this->doAuthentication($route);
      $this->doRequest($route);
      $this->doAuthorization($route);
      if (is_null($body = $this->doBody($route))) {
        throw new \Exception("No available requester.");
      }
      if (is_null($result = $this->doProcess($route, $body))) {
        throw new \Exception("No available processor.");
      }
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

    return $this->doResponse($route, $result);
  }

  /**
   * Perform the authentication for the route.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   */
  protected function doAuthentication(RouteInterface $route): void {
    $authentication = $this->authenticationFactory?->getAuthentication($route);
    if ($authentication) {
      $route->setAccount($authentication->authenticate($route));
    }
  }

  /**
   * Perform the middleware request.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   *
   * @throws \Throwable
   */
  protected function doRequest(RouteInterface $route): void {
    $this->middleware = [];
    foreach ($this->middlewareFactory->getMiddleware($route) as $middleware) {
      $this->middleware[$middleware->priority()][] = $middleware;
    }

    krsort($this->middleware);
    foreach ($this->middleware as $middlewares) {
      foreach ($middlewares as $middleware) {
        $middleware->request($route);
      }
    }
  }

  /**
   * Perform the authorization after the middleware.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   *
   * @throws \Xylemical\Controller\Exception\AccessException
   */
  protected function doAuthorization(RouteInterface $route): void {
    $authorization = $this->authorizationFactory?->getAuthorization($route);
    if ($authorization && !$authorization->authorize($route)) {
      throw new AccessException();
    }
  }

  /**
   * Perform the conversion of the body.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   *
   * @return mixed|null
   *   The body.
   *
   * @throws \Throwable
   * @throws \Xylemical\Controller\Exception\InvalidBodyException
   */
  protected function doBody(RouteInterface $route): mixed {
    $requester = $this->requesterFactory->getRequester($route);
    return $requester->getBody($route);
  }

  /**
   * Perform the processing of the request body.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   * @param mixed $body
   *   The body.
   *
   * @return \Xylemical\Controller\ResultInterface|null
   *   The result or NULL.
   *
   * @throws \Throwable
   * @throws \Xylemical\Controller\Exception\AccessException
   * @throws \Xylemical\Controller\Exception\DelayedException
   * @throws \Xylemical\Controller\Exception\ErrorException
   * @throws \Xylemical\Controller\Exception\UnavailableException
   */
  protected function doProcess(RouteInterface $route, mixed $body): ?ResultInterface {
    $processor = $this->processorFactory->getProcessor($route, $body);
    return $processor->getResult($route, $body);
  }

  /**
   * Perform response.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   * @param \Xylemical\Controller\ResultInterface $result
   *   The result.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   */
  protected function doResponse(RouteInterface $route, ResultInterface $result): ResponseInterface {
    try {
      $responder = $this->responderFactory->getResponder($route, $result);

      $response = $responder->getResponse($route, $result);
      if (!$response) {
        throw new \Exception("No available responder.");
      }

      ksort($this->middleware);
      foreach ($this->middleware as $middlewares) {
        foreach (array_reverse($middlewares) as $middleware) {
          $response = $middleware->response($route, $response);
        }
      }
    }
    catch (\Throwable $e) {
      return (new Response())->withStatus(
        $e->getCode() ?: 500,
        $e->getMessage()
      );
    }

    return $response;
  }

}
