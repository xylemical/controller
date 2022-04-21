<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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
   * Get a mock context factory.
   *
   * @return \Xylemical\Controller\ContextFactoryInterface
   *   The factory.
   */
  protected function getMockContextFactory() {
    $context = $this->getMockBuilder(ContextInterface::class)->getMock();
    $factory = $this->prophesize(ContextFactoryInterface::class);
    $factory->getContext(Argument::any())->willReturn($context);
    return $factory->reveal();
  }

  /**
   * Get a generic responder.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return \Prophecy\Prophecy\ObjectProphecy
   *   The responder.
   */
  protected function getMockResponder($request) {
    $responder = $this->prophesize(ResponderInterface::class);
    $responder->applies($request, Argument::any(), Argument::any())
      ->willReturn(TRUE);
    $responder->getResponse($request, Argument::any(), Argument::any())
      ->will(function ($args) {
        /** @var \Xylemical\Controller\ResultInterface $result */
        $result = $args[1];
        return new Response($result->getStatus(), $result->getContents());
      });
    return $responder;
  }

  /**
   * Get a mock middleware.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The response.
   * @param int $priority
   *   The priority.
   * @param array $sequence
   *   The processing sequence.
   *
   * @return \Xylemical\Controller\MiddlewareInterface
   *   The mock middleware.
   */
  protected function getMockMiddleware(RequestInterface $request, ResponseInterface $response, int $priority, &$sequence): MiddlewareInterface {
    $middleware = $this->prophesize(MiddlewareInterface::class);
    $middleware->priority()->willReturn($priority);
    $middleware->request(Argument::any(), Argument::any(), Argument::any())
      ->will(function ($args) use ($request, &$sequence) {
        $sequence['request'][] = $args[1];
        return $request;
      });
    $middleware->response(Argument::any(), Argument::any(), Argument::any())
      ->will(function ($args) use ($response, &$sequence) {
        $sequence['response'][] = $args[1];
        return $response;
      });
    return $middleware->reveal();
  }


  /**
   * Test basic functionality.
   */
  public function testSanity() {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
    $middleware = $this->getMockMiddleware($request, $response, 0, $sequence);

    $requester = $this->getMockBuilder(RequesterInterface::class)->getMock();
    $responder = $this->getMockBuilder(ResponderInterface::class)->getMock();
    $processor = $this->getMockBuilder(ProcessorInterface::class)->getMock();
    $factory = $this->getMockContextFactory();

    $controller = new Controller($factory, $requester, $processor, $responder);
    $this->assertEquals([], $controller->getMiddleware());
    $controller->addMiddleware($middleware);
    $this->assertEquals([$middleware], $controller->getMiddleware());
    $controller->setMiddleware([]);
    $this->assertEquals([], $controller->getMiddleware());
    $controller->setMiddleware([$middleware]);
    $this->assertEquals([$middleware], $controller->getMiddleware());

    $this->assertEquals($requester, $controller->getRequester());
    $this->assertEquals($responder, $controller->getResponder());
    $this->assertEquals($processor, $controller->getProcessor());
  }

  /**
   * Test an invalid body exception.
   */
  public function testInvalidBodyException() {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();

    $requester = $this->prophesize(RequesterInterface::class);
    $processor = $this->prophesize(ProcessorInterface::class);
    $responder = $this->getMockResponder($request);

    $exception = new InvalidBodyException('Test Message');
    $requester->applies($request, Argument::any())->willReturn(TRUE);
    $requester->getBody($request, Argument::any())->willThrow($exception);

    $controller = new Controller(
      $this->getMockContextFactory(),
      $requester->reveal(),
      $processor->reveal(),
      $responder->reveal()
    );

    $response = $controller->handle($request);
    $this->assertEquals(ResultInterface::STATUS_ERROR, $response->getStatusCode());
    $this->assertEquals($exception->getMessage(), $response->getBody()
      ->getContents());
  }

  /**
   * Test when missing a processor that applies.
   */
  public function testMissingProcessor() {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $body = ['body'];

    $requester = $this->prophesize(RequesterInterface::class);
    $processor = $this->prophesize(ProcessorInterface::class);
    $responder = $this->getMockResponder($request);

    $requester->applies($request, Argument::any())->willReturn(TRUE);
    $requester->getBody($request, Argument::any())->willReturn($body);

    $processor->applies($request, $body, Argument::any())->willReturn(FALSE);

    $controller = new Controller(
      $this->getMockContextFactory(),
      $requester->reveal(),
      $processor->reveal(),
      $responder->reveal()
    );

    $response = $controller->handle($request);
    $this->assertEquals(ResultInterface::STATUS_ERROR, $response->getStatusCode());
    $this->assertEquals('No available processor.', $response->getBody()
      ->getContents());
  }

  /**
   * Provides data for testProcessorExceptions().
   *
   * @return array[]
   *   The data.
   */
  public function providerTestProcessorExceptions() {
    return [
      [AccessException::class, ResultInterface::STATUS_ACCESS],
      [DelayedException::class, ResultInterface::STATUS_DELAYED],
      [UnavailableException::class, ResultInterface::STATUS_UNAVAILABLE],
      [\Exception::class, ResultInterface::STATUS_ERROR],
    ];
  }

  /**
   * Test the processor exception behaviour.
   *
   * @dataProvider providerTestProcessorExceptions
   */
  public function testProcessorExceptions($exception, $status) {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $body = ['body'];

    $requester = $this->prophesize(RequesterInterface::class);
    $processor = $this->prophesize(ProcessorInterface::class);
    $responder = $this->getMockResponder($request);

    $requester->applies($request, Argument::any())->willReturn(TRUE);
    $requester->getBody($request, Argument::any())->willReturn($body);

    $exception = new $exception('Test Message');
    $processor->applies($request, $body, Argument::any())->willReturn(TRUE);
    $processor->getResult($request, $body, Argument::any())
      ->willThrow($exception);

    $controller = new Controller(
      $this->getMockContextFactory(),
      $requester->reveal(),
      $processor->reveal(),
      $responder->reveal()
    );

    $response = $controller->handle($request);
    $this->assertEquals($status, $response->getStatusCode());
    $this->assertEquals($exception->getMessage(), $response->getBody()
      ->getContents());
  }

  /**
   * Test the processor success.
   */
  public function testProcessor() {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $body = ['body'];

    $requester = $this->prophesize(RequesterInterface::class);
    $processor = $this->prophesize(ProcessorInterface::class);
    $responder = $this->getMockResponder($request);

    $requester->applies($request, Argument::any())->willReturn(TRUE);
    $requester->getBody($request, Argument::any())->willReturn($body);

    $processor->applies($request, $body, Argument::any())->willReturn(TRUE);
    $processor->getResult($request, $body, Argument::any())
      ->willReturn(Result::complete('Test Message'));

    $controller = new Controller(
      $this->getMockContextFactory(),
      $requester->reveal(),
      $processor->reveal(),
      $responder->reveal()
    );

    $response = $controller->handle($request);
    $this->assertEquals(ResultInterface::STATUS_COMPLETE, $response->getStatusCode());
    $this->assertEquals('Test Message', $response->getBody()->getContents());
  }

  /**
   * Test response exception.
   */
  public function testResponseException() {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $body = ['body'];

    $exception = new \Exception('Exception Message', 214);
    $requester = $this->prophesize(RequesterInterface::class);
    $processor = $this->prophesize(ProcessorInterface::class);
    $responder = $this->prophesize(ResponderInterface::class);
    $responder->applies($request, Argument::any(), Argument::any())
      ->willReturn(TRUE);
    $responder->getResponse($request, Argument::any(), Argument::any())
      ->willThrow($exception);

    $requester->applies($request, Argument::any())->willReturn(TRUE);
    $requester->getBody($request, Argument::any())->willReturn($body);

    $processor->applies($request, $body, Argument::any())->willReturn(TRUE);
    $processor->getResult($request, $body, Argument::any())
      ->willReturn(Result::complete('Test Message'));

    $controller = new Controller(
      $this->getMockContextFactory(),
      $requester->reveal(),
      $processor->reveal(),
      $responder->reveal()
    );

    $response = $controller->handle($request);
    $this->assertEquals(214, $response->getStatusCode());
    $this->assertEquals($exception->getMessage(), $response->getReasonPhrase());
  }

  /**
   * Test response exception.
   */
  public function testResponderFailure() {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $body = ['body'];

    $requester = $this->prophesize(RequesterInterface::class);
    $processor = $this->prophesize(ProcessorInterface::class);
    $responder = $this->prophesize(ResponderInterface::class);
    $responder->applies($request, Argument::any(), Argument::any())
      ->willReturn(FALSE);

    $requester->applies($request, Argument::any())->willReturn(TRUE);
    $requester->getBody($request, Argument::any())->willReturn($body);

    $processor->applies($request, $body, Argument::any())->willReturn(TRUE);
    $processor->getResult($request, $body, Argument::any())
      ->willReturn(Result::complete('Test Message'));

    $controller = new Controller(
      $this->getMockContextFactory(),
      $requester->reveal(),
      $processor->reveal(),
      $responder->reveal()
    );

    $response = $controller->handle($request);
    $this->assertEquals(500, $response->getStatusCode());
    $this->assertEquals('The responder is unable to respond.', $response->getReasonPhrase());
  }


  /**
   * Test middleware.
   */
  public function testMiddleware() {
    $middlewares = [
      'a' => [
        $this->getMockBuilder(RequestInterface::class)->getMock(),
        $this->getMockBuilder(ResponseInterface::class)->getMock(),
        1,
      ],
      'b' => [
        $this->getMockBuilder(RequestInterface::class)->getMock(),
        $this->getMockBuilder(ResponseInterface::class)->getMock(),
        0,
      ],
      'c' => [
        $this->getMockBuilder(RequestInterface::class)->getMock(),
        $this->getMockBuilder(ResponseInterface::class)->getMock(),
        0,
      ],
      'd' => [
        $this->getMockBuilder(RequestInterface::class)->getMock(),
        $this->getMockBuilder(ResponseInterface::class)->getMock(),
        -1,
      ],
    ];

    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $response = $this->getMockBuilder(ResponseInterface::class)->getMock();

    $requester = $this->prophesize(RequesterInterface::class);
    $requester->applies(Argument::any(), Argument::any())->willReturn(TRUE);
    $requester->getBody(Argument::any(), Argument::any())
      ->willReturn([]);

    $processor = $this->prophesize(ProcessorInterface::class);
    $processor->applies($middlewares['a'][0], Argument::any(), Argument::any())
      ->willReturn(new Result(0, NULL));

    $responder = $this->prophesize(ResponderInterface::class);
    $responder->applies(Argument::any(), Argument::any(), Argument::any())
      ->willReturn(TRUE);
    $responder->getResponse(Argument::any(), Argument::any(), Argument::any())
      ->willReturn($response);

    $controller = new Controller(
      $this->getMockContextFactory(),
      $requester->reveal(),
      $processor->reveal(),
      $responder->reveal()
    );

    $sequence = [];
    foreach ($middlewares as $middleware) {
      $controller->addMiddleware(
        $this->getMockMiddleware(
          $middleware[0],
          $middleware[1],
          $middleware[2],
          $sequence
        )
      );
    }

    $response = $controller->handle($request);
    $this->assertEquals($response, $middlewares['d'][1]);
    $this->assertEquals([
      $request,
      $middlewares['d'][0],
      $middlewares['b'][0],
      $middlewares['c'][0],
    ], $sequence['request']);
    $this->assertEquals([
      $response,
      $middlewares['a'][1],
      $middlewares['c'][1],
      $middlewares['b'][1],
    ], $sequence['response']);
  }

}
