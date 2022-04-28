<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
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
    $route = $this->getMockBuilder(RouteInterface::class)->getMock();
    $result = $this->getMockBuilder(ResultInterface::class)->getMock();

    $body = [];

    $child = $this->prophesize(ProcessorInterface::class);
    $child->applies($route, Argument::any())->willReturn(TRUE);
    $child->getResult($route, Argument::any())->willReturn($result);

    $child = $child->reveal();

    $processor = new Processor([$child]);

    $this->assertTrue($processor->applies($route, $body));
    $this->assertEquals($result, $processor->getResult($route, $body));

    $processor = new Processor();
    $processor->addProcessor($child);

    $this->assertTrue($processor->applies($route, $body));
    $this->assertEquals($result, $processor->getResult($route, $body));

    $processor = new Processor();

    $this->assertFalse($processor->applies($route, $body));
    $this->expectException(UnavailableException::class);
    $processor->getResult($route, $body);
  }

}
