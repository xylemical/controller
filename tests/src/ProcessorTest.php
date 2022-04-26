<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\RequestInterface;
use Xylemical\Controller\Exception\UnavailableException;

/**
 * Test the \Xylemical\Controller\Processor class.
 */
class ProcessorTest extends TestCase {

  use ProphecyTrait;

  /**
   * Test the processor.
   */
  public function testProcessor(): void {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $result = $this->getMockBuilder(ResultInterface::class)->getMock();
    $context = $this->getMockBuilder(ContextInterface::class)->getMock();

    $body = [];

    $child = $this->prophesize(ProcessorInterface::class);
    $child->applies($request, $body, Argument::any())->willReturn(TRUE);
    $child->getResult($request, $body, Argument::any())->willReturn($result);

    $child = $child->reveal();

    $processor = new Processor([$child]);

    $this->assertTrue($processor->applies($request, $body, $context));
    $this->assertEquals($result, $processor->getResult($request, $body, $context));

    $processor = new Processor();
    $processor->addProcessor($child);

    $this->assertTrue($processor->applies($request, $body, $context));
    $this->assertEquals($result, $processor->getResult($request, $body, $context));

    $processor = new Processor();

    $this->assertFalse($processor->applies($request, $body, $context));
    $this->expectException(UnavailableException::class);
    $processor->getResult($request, $body, $context);
  }

}
