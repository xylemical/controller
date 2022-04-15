<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\RequestInterface;
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
   * Get a generic responder.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return \Prophecy\Prophecy\ObjectProphecy
   */
  protected function getResponder($request) {
    $responder = $this->prophesize(ResponderInterface::class);
    $responder->getResponse($request, Argument::any())
      ->will(function($args) {
        /** @var \Xylemical\Controller\ResultInterface $result */
        $result = $args[1];
        return new Response($result->getStatus(), $result->getContents());
      });
    return $responder;
  }

  /**
   * Test an invalid body exception.
   */
  public function testInvalidBodyException() {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();

    $requester = $this->prophesize(RequesterInterface::class);
    $processor = $this->prophesize(ProcessorInterface::class);
    $responder = $this->getResponder($request);

    $exception = new InvalidBodyException('Test Message');
    $requester->applies($request)->willReturn(TRUE);
    $requester->getBody($request)->willThrow($exception);

    $controller = new Controller(
      $requester->reveal(),
      $responder->reveal(),
      $processor->reveal()
    );

    $response = $controller->handle($request);
    $this->assertEquals(ResultInterface::STATUS_ERROR, $response->getStatusCode());
    $this->assertEquals($exception->getMessage(), $response->getBody()->getContents());
  }

  /**
   * Test when missing a processor that applies.
   */
  public function testMissingProcessor() {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $body = ['body'];

    $requester = $this->prophesize(RequesterInterface::class);
    $processor = $this->prophesize(ProcessorInterface::class);
    $responder = $this->getResponder($request);

    $requester->applies($request)->willReturn(TRUE);
    $requester->getBody($request)->willReturn($body);

    $processor->applies($request, $body)->willReturn(FALSE);

    $controller = new Controller(
      $requester->reveal(),
      $responder->reveal(),
      $processor->reveal()
    );

    $response = $controller->handle($request);
    $this->assertEquals(ResultInterface::STATUS_ERROR, $response->getStatusCode());
    $this->assertEquals('No available processor.', $response->getBody()->getContents());
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
    $responder = $this->getResponder($request);

    $requester->applies($request)->willReturn(TRUE);
    $requester->getBody($request)->willReturn($body);

    $exception = new $exception('Test Message');
    $processor->applies($request, $body)->willReturn(TRUE);
    $processor->getResult($request, $body)->willThrow($exception);

    $controller = new Controller(
      $requester->reveal(),
      $responder->reveal(),
      $processor->reveal()
    );

    $response = $controller->handle($request);
    $this->assertEquals($status, $response->getStatusCode());
    $this->assertEquals($exception->getMessage(), $response->getBody()->getContents());
  }

  /**
   * Test the processor success.
   */
  public function testProcessor() {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $body = ['body'];

    $requester = $this->prophesize(RequesterInterface::class);
    $processor = $this->prophesize(ProcessorInterface::class);
    $responder = $this->getResponder($request);

    $requester->applies($request)->willReturn(TRUE);
    $requester->getBody($request)->willReturn($body);

    $processor->applies($request, $body)->willReturn(TRUE);
    $processor->getResult($request, $body)->willReturn(Result::complete('Test Message'));

    $controller = new Controller(
      $requester->reveal(),
      $responder->reveal(),
      $processor->reveal()
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

    $exception = new \Exception('Exception Message');
    $requester = $this->prophesize(RequesterInterface::class);
    $processor = $this->prophesize(ProcessorInterface::class);
    $responder = $this->prophesize(ResponderInterface::class);
    $responder->getResponse($request, Argument::any())->willThrow($exception);

    $requester->applies($request)->willReturn(TRUE);
    $requester->getBody($request)->willReturn($body);

    $processor->applies($request, $body)->willReturn(TRUE);
    $processor->getResult($request, $body)->willReturn(Result::complete('Test Message'));

    $controller = new Controller(
      $requester->reveal(),
      $responder->reveal(),
      $processor->reveal()
    );

    $response = $controller->handle($request);
    $this->assertEquals(500, $response->getStatusCode());
    $this->assertEquals($exception->getMessage(), $response->getReasonPhrase());
  }

}
