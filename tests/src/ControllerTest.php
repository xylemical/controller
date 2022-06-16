<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Xylemical\Account\AccountInterface;
use Xylemical\Controller\Authentication\AuthenticationFactoryInterface;
use Xylemical\Controller\Authentication\AuthenticationInterface;
use Xylemical\Controller\Authorization\AuthorizationFactoryInterface;
use Xylemical\Controller\Authorization\AuthorizationInterface;
use Xylemical\Controller\Exception\AccessException;
use Xylemical\Controller\Exception\DelayedException;
use Xylemical\Controller\Exception\InvalidBodyException;
use Xylemical\Controller\Exception\UnavailableException;

/**
 * Tests \Xylemical\Controller\Controller.
 */
class ControllerTest extends TestCase {

  use ProphecyTrait;

  /**
   * Get a mock requester factory.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   * @param mixed $returns
   *   The returns.
   *
   * @return \Xylemical\Controller\RequesterFactoryInterface
   *   The factory.
   */
  protected function getMockRequesterFactory(RouteInterface $route, mixed $returns): RequesterFactoryInterface {
    $requester = $this->prophesize(RequesterInterface::class);
    if ($returns instanceof \Throwable) {
      $requester->getBody($route)->willThrow($returns);
    }
    else {
      $requester->getBody($route)->willReturn($returns);
    }
    $factory = $this->prophesize(RequesterFactoryInterface::class);
    $factory->getRequester($route)->willReturn($requester->reveal());
    return $factory->reveal();
  }

  /**
   * Get a mock processor factory.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   * @param \Xylemical\Controller\ResultInterface|null|\Throwable $returns
   *   The returns.
   *
   * @return \Xylemical\Controller\ProcessorFactoryInterface
   *   The factory.
   */
  protected function getMockProcessorFactory(RouteInterface $route, mixed $returns): ProcessorFactoryInterface {
    $processor = $this->prophesize(ProcessorInterface::class);
    if ($returns instanceof \Throwable) {
      $processor->getResult($route, Argument::any())->willThrow($returns);
    }
    else {
      $processor->getResult($route, Argument::any())->willReturn($returns);
    }
    $factory = $this->prophesize(ProcessorFactoryInterface::class);
    $factory->getProcessor($route, Argument::any())
      ->willReturn($processor->reveal());
    return $factory->reveal();
  }

  /**
   * Get a mock responder factory.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   * @param \Xylemical\Controller\ResultInterface|null $result
   *   The result.
   * @param \Psr\Http\Message\ResponseInterface|null|true $returns
   *   The returns.
   *
   * @return \Xylemical\Controller\ResponderFactoryInterface
   *   The factory.
   */
  protected function getMockResponderFactory(RouteInterface $route, mixed $result, mixed $returns): ResponderFactoryInterface {
    $responder = $this->prophesize(ResponderInterface::class);
    if ($returns === TRUE) {
      $responder->getResponse($route, Argument::any())->will(function ($args) {
        /** @var \Xylemical\Controller\ResultInterface $result */
        $result = $args[1];
        if ($result->getStatus() !== ResultInterface::STATUS_COMPLETE) {
          $response = (new Response())->withStatus($result->getStatus(), $result->getContents());
        }
        else {
          $response = new Response($result->getStatus(), $result->getContents());
        }
        return $response;
      });
    }
    elseif ($returns instanceof \Throwable) {
      $responder->getResponse($route, Argument::any())->willThrow($returns);
    }
    else {
      $responder->getResponse($route, $result)->willReturn($returns);
    }
    $factory = $this->prophesize(ResponderFactoryInterface::class);
    $factory->getResponder($route, Argument::any())
      ->willReturn($responder->reveal());
    return $factory->reveal();
  }

  /**
   * Get a mock middleware.
   *
   * @param string $name
   *   The name of the middleware.
   * @param int $priority
   *   The priority.
   * @param \Throwable|null $requestException
   *   The request exception.
   * @param \Throwable|null $responseException
   *   The response exception.
   * @param \Psr\Http\Message\ResponseInterface|null $response
   *   The response, or no change.
   * @param array $sequence
   *   The sequence of execution.
   *
   * @return \Xylemical\Controller\MiddlewareInterface
   *   The middleware.
   */
  protected function getMockMiddleware(string $name, int $priority, ?\Throwable $requestException, ?\Throwable $responseException, ?ResponseInterface $response, array &$sequence): MiddlewareInterface {
    $middleware = $this->prophesize(MiddlewareInterface::class);
    $middleware->priority()->willReturn($priority);
    if ($requestException) {
      $middleware->request(Argument::any())->willThrow($requestException);
    }
    else {
      $middleware->request(Argument::any())
        ->will(function ($args) use ($name, &$sequence) {
          $sequence['request'][] = $name;
        });
    }
    if ($responseException) {
      $middleware->response(Argument::any(), Argument::any())
        ->willThrow($responseException);
    }
    else {
      $middleware->response(Argument::any(), Argument::any())
        ->will(function ($args) use ($name, $response, &$sequence) {
          $sequence['response'][] = $name;
          return !is_null($response) ? $response : $args[1];
        });
    }
    return $middleware->reveal();
  }

  /**
   * Get a mock middleware factory.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   * @param \Xylemical\Controller\MiddlewareInterface[] $middlewares
   *   The middlewares.
   *
   * @return \Xylemical\Controller\MiddlewareFactoryInterface
   *   The factory.
   */
  protected function getMockMiddlewareFactory(RouteInterface $route, array $middlewares): MiddlewareFactoryInterface {
    $factory = $this->prophesize(MiddlewareFactoryInterface::class);
    $factory->getMiddleware($route)->willReturn($middlewares);
    return $factory->reveal();
  }

  /**
   * Get a mock authentication factory.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   * @param \Xylemical\Account\AccountInterface|bool|null|\Throwable $returns
   *   The returns.
   *
   * @return \Xylemical\Controller\Authentication\AuthenticationFactoryInterface|null
   *   The factory.
   */
  protected function getMockAuthenticationFactory(RouteInterface $route, mixed $returns): ?AuthenticationFactoryInterface {
    if (is_null($returns)) {
      return NULL;
    }

    $authentication = $this->prophesize(AuthenticationInterface::class);
    $authentication->authenticate($route)->willReturn($returns ?: NULL);
    $factory = $this->prophesize(AuthenticationFactoryInterface::class);
    $factory->getAuthentication($route)
      ->willReturn($authentication->reveal());
    return $factory->reveal();
  }

  /**
   * Get a mock authorization factory.
   *
   * @param \Xylemical\Controller\RouteInterface $route
   *   The route.
   * @param \Xylemical\Account\AccountInterface|null|\Throwable $returns
   *   The returns.
   *
   * @return \Xylemical\Controller\Authorization\AuthorizationFactoryInterface|null
   *   The factory.
   */
  protected function getMockAuthorizationFactory(RouteInterface $route, mixed $returns): ?AuthorizationFactoryInterface {
    if (is_null($returns)) {
      return NULL;
    }

    $authorization = $this->prophesize(AuthorizationInterface::class);
    $authorization->authorize($route)->willReturn($returns);
    $factory = $this->prophesize(AuthorizationFactoryInterface::class);
    $factory->getAuthorization($route)
      ->willReturn($authorization->reveal());
    return $factory->reveal();
  }

  /**
   * Get a mock route.
   *
   * @return \Xylemical\Controller\RouteInterface
   *   The route.
   */
  protected function getMockRoute(): RouteInterface {
    $route = $this->prophesize(RouteInterface::class);
    return $route->reveal();
  }

  /**
   * Test basic functionality.
   */
  public function testSanity(): void {
    $route = $this->getMockRoute();
    $returns = ['body'];
    $result = Result::complete($returns);
    $response = new Response(200, 'body');
    $sequence = [];

    $requesterFactory = $this->getMockRequesterFactory($route, $returns);
    $processorFactory = $this->getMockProcessorFactory($route, $result);
    $responderFactory = $this->getMockResponderFactory($route, $result, $response);

    $middlewares = [
      $this->getMockMiddleware('a', -1, NULL, NULL, NULL, $sequence),
      $this->getMockMiddleware('b', 0, NULL, NULL, NULL, $sequence),
      $this->getMockMiddleware('c', 0, NULL, NULL, NULL, $sequence),
      $this->getMockMiddleware('d', 1, NULL, NULL, NULL, $sequence),
    ];
    $middlewareFactory = $this->getMockMiddlewareFactory($route, $middlewares);

    $controller = new Controller($requesterFactory, $processorFactory, $responderFactory, $middlewareFactory);

    $outcome = $controller->handle($route);
    $this->assertSame($response, $outcome);
    $this->assertEquals(['d', 'b', 'c', 'a'], $sequence['request']);
    $this->assertEquals(['a', 'c', 'b', 'd'], $sequence['response']);
  }

  /**
   * Provides test data for exception handling.
   *
   * @return array
   *   The test data.
   */
  public function providerTestExceptions() {
    return [
      [
        NULL,
        NULL,
        TRUE,
        ResultInterface::STATUS_ERROR,
        'No available requester.',
      ],
      [
        ['body'],
        NULL,
        TRUE,
        ResultInterface::STATUS_ERROR,
        'No available processor.',
      ],
      [
        ['body'],
        Result::complete('body'),
        NULL,
        ResultInterface::STATUS_ERROR,
        'No available responder.',
      ],
      [
        new InvalidBodyException('Test Body'),
        NULL,
        TRUE,
        ResultInterface::STATUS_ERROR,
        'Test Body',
      ],
      [
        ['body'],
        new AccessException('Access denied'),
        TRUE,
        ResultInterface::STATUS_ACCESS,
        'Access denied',
      ],
      [
        ['body'],
        new DelayedException('Still processing'),
        TRUE,
        ResultInterface::STATUS_DELAYED,
        'Still processing',
      ],
      [
        ['body'],
        new UnavailableException('This resource does not exist.'),
        TRUE,
        ResultInterface::STATUS_UNAVAILABLE,
        'This resource does not exist.',
      ],
    ];
  }

  /**
   * Tests exception handling.
   *
   * @dataProvider providerTestExceptions
   */
  public function testExceptions(mixed $request, mixed $process, mixed $response, int $status, string $body): void {
    $route = $this->getMockRoute();

    $requesterFactory = $this->getMockRequesterFactory($route, $request);
    $processorFactory = $this->getMockProcessorFactory($route, $process);
    $responderFactory = $this->getMockResponderFactory($route, $process, $response);
    $middlewareFactory = $this->getMockMiddlewareFactory($route, []);

    $controller = new Controller($requesterFactory, $processorFactory, $responderFactory, $middlewareFactory);

    $outcome = $controller->handle($route);
    $this->assertEquals($status, $outcome->getStatusCode());
    $this->assertEquals($body, $outcome->getReasonPhrase());
  }

  /**
   * Provides the test data for testAuth().
   *
   * @return array
   *   The test data.
   */
  public function providerTestAuth(): array {
    return [
      [NULL, NULL, ResultInterface::STATUS_COMPLETE],
      [NULL, FALSE, ResultInterface::STATUS_ACCESS],
      [NULL, TRUE, ResultInterface::STATUS_COMPLETE],
      [TRUE, NULL, ResultInterface::STATUS_COMPLETE],
      [TRUE, FALSE, ResultInterface::STATUS_ACCESS],
      [TRUE, TRUE, ResultInterface::STATUS_COMPLETE],
      [FALSE, NULL, ResultInterface::STATUS_COMPLETE],
      [FALSE, FALSE, ResultInterface::STATUS_ACCESS],
      [FALSE, TRUE, ResultInterface::STATUS_COMPLETE],
    ];
  }

  /**
   * Test the authorization behaviour.
   *
   * @dataProvider providerTestAuth
   */
  public function testAuth(mixed $authentication, mixed $authorization): void {
    $account = $this->getMockBuilder(AccountInterface::class)->getMock();
    $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
    $context = $this->getMockBuilder(ContextInterface::class)->getMock();

    $route = new Route('test', [], $request, $context);
    $returns = ['body'];
    $result = Result::complete('body');

    $requesterFactory = $this->getMockRequesterFactory($route, $returns);
    $processorFactory = $this->getMockProcessorFactory($route, $result);
    $responderFactory = $this->getMockResponderFactory($route, $result, TRUE);
    $middlewareFactory = $this->getMockMiddlewareFactory($route, []);
    $authenticationFactory = $this->getMockAuthenticationFactory($route, match ($authorization) {
      default => NULL,
      TRUE => $account,
      FALSE => FALSE
    });
    $authorizationFactory = $this->getMockAuthorizationFactory($route, $authorization);

    $controller = new Controller($requesterFactory, $processorFactory, $responderFactory, $middlewareFactory, $authenticationFactory, $authorizationFactory);

    $outcome = $controller->handle($route);
    if ($authorization === FALSE) {
      $this->assertEquals(ResultInterface::STATUS_ACCESS, $outcome->getStatusCode());
      return;
    }
    if ($authorization === TRUE) {
      $this->assertSame($account, $route->getAccount());
    }
    else {
      $this->assertNull($route->getAccount());
    }

    $this->assertEquals(ResultInterface::STATUS_COMPLETE, $outcome->getStatusCode());
  }

}
