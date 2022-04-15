<?php

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;

/**
 * Test \Xylemical\Controller\Response matches Psr-7 requirements.
 */
class ResponseTest extends TestCase {

  /**
   * Tests a response.
   */
  public function testResponse() {
    $response = new Response();
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('', $response->getBody()->getContents());
    $this->assertEquals('1.0', $response->getProtocolVersion());

    $header = 'X-Test';
    $value = 'Value';
    $result = $response->withHeader($header, $value);
    $this->assertNotEquals($response, $result);
    $this->assertEquals($value, $result->getHeaderLine($header));
    $this->assertEquals([$value], $result->getHeader($header));
    $this->assertEquals([$header => [$value]], $result->getHeaders());
    $this->assertTrue($result->hasHeader($header));

    $response = $result->withAddedHeader($header, $value);
    $this->assertNotEquals($response, $result);
    $this->assertEquals(
      "{$value}, {$value}",
      $response->getHeaderLine($header)
    );
    $this->assertEquals([$value, $value], $response->getHeader($header));
    $this->assertEquals([$header => [$value, $value]], $response->getHeaders());

    $result = $result->withoutHeader($header);
    $this->assertNotEquals($result, $response);
    $this->assertEquals('', $result->getHeaderLine($header));
    $this->assertEquals([], $result->getHeader($header));
    $this->assertEquals([], $result->getHeaders());
    $this->assertFalse($result->hasHeader($header));

    $reason = 'Random failure';
    $response = $result->withStatus(500, $reason);
    $this->assertNotEquals($result, $response);
    $this->assertEquals(500, $response->getStatusCode());
    $this->assertEquals($reason, $response->getReasonPhrase());

    $result = $response->withProtocolVersion('1.1');
    $this->assertNotEquals($result, $response);
    $this->assertEquals('1.1', $result->getProtocolVersion());

    $body = new Stream('Alternative Text');
    $response = $result->withBody($body);
    $this->assertNotEquals($result, $response);
    $this->assertNotEquals($result->getBody(), $response->getBody());
  }

}
