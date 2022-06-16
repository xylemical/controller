<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

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
    $child->getResult($route, Argument::any())->willReturn($result);

    $child = $child->reveal();

    $processor = new Processor([$child]);

    $this->assertEquals($result, $processor->getResult($route, $body));

    $processor = new Processor();
    $processor->addProcessor($child);

    $this->assertEquals($result, $processor->getResult($route, $body));

    $processor = new Processor();

    $this->assertNull($processor->getResult($route, $body));
  }

}
