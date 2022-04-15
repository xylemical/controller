<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
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
  public function testProcessor() {
    $request = $this->getMockBuilder(RequestInterface::class)->getMock();
    $result = $this->getMockBuilder(ResultInterface::class)->getMock();

    $body = [];

    $child = $this->prophesize(ProcessorInterface::class);
    $child->applies($request, $body)->willReturn(TRUE);
    $child->getResult($request, $body)->willReturn($result);

    $child = $child->reveal();

    $processor = new Processor([$child]);

    $this->assertTrue($processor->applies($request, $body));
    $this->assertEquals($result, $processor->getResult($request, $body));

    $processor = new Processor();
    $processor->addProcessor($child);

    $this->assertTrue($processor->applies($request, $body));
    $this->assertEquals($result, $processor->getResult($request, $body));

    $processor = new Processor();

    $this->assertFalse($processor->applies($request, $body));
    $this->expectException(UnavailableException::class);
    $processor->getResult($request, $body);
  }

}
