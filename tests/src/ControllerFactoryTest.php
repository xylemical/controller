<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\RequestInterface;

/**
 * Tests \Xylemical\Controller\ControllerFactory.
 */
class ControllerFactoryTest extends TestCase {

  use ProphecyTrait;

  /**
   * Test the sanity of the controller factory.
   */
  public function testSanity(): void {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();

    $contextFactory = $this->getMockBuilder(ContextFactoryInterface::class)
      ->getMock();

    $responder = $this->getMockBuilder(ResponderInterface::class)->getMock();
    $responderFactory = $this->prophesize(ResponderFactoryInterface::class);
    $responderFactory->getResponder($request)->willReturn($responder);

    $processor = $this->getMockBuilder(ProcessorInterface::class)->getMock();
    $processorFactory = $this->prophesize(ProcessorFactoryInterface::class);
    $processorFactory->getProcessor($request)->willReturn($processor);

    $requester = $this->getMockBuilder(RequesterInterface::class)->getMock();
    $requesterFactory = $this->prophesize(RequesterFactoryInterface::class);
    $requesterFactory->getRequester($request)->willReturn($requester);

    $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
    $middlewareFactory = $this->prophesize(MiddlewareFactoryInterface::class);
    $middlewareFactory->getMiddleware($request)->willReturn([$middleware]);

    $factory = new ControllerFactory();
    $controller = $factory->getController(
      $contextFactory,
      $requesterFactory->reveal(),
      $processorFactory->reveal(),
      $responderFactory->reveal(),
      $middlewareFactory->reveal(),
      $request
    );
    $this->assertSame($responder, $controller->getResponder());
    $this->assertSame($processor, $controller->getProcessor());
    $this->assertSame($requester, $controller->getRequester());
    $this->assertEquals([$middleware], $controller->getMIddleware());
  }

}
