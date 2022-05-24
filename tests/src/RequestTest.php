<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use function implode;

/**
 * Tests \Xylemical\Controller\Request.
 */
class RequestTest extends TestCase {

  /**
   * The test data for testWithMethod().
   *
   * @return array
   *   The test data.
   */
  public function providerTestWithMethod(): array {
    return [
      ['GET', 'GET'],
      ['PuT', 'PUT'],
      ['post', 'POST'],
      ['pAtch', 'PATCH'],
      ['DELETE', 'DELETE'],
      ['CONNECT', 'CONNECT'],
      ['TRACE', 'TRACE'],
      ['OPTIONS', 'OPTIONS'],
      ['invalid', '', TRUE],
    ];
  }

  /**
   * Tests withMethod().
   *
   * @dataProvider providerTestWithMethod
   */
  public function testWithMethod(string $method, string $expected, bool $exception = FALSE): void {
    $request = new Request('GET', '', '');
    $thrown = FALSE;
    $updated = $request;
    try {
      $updated = $request->withMethod($method);
    }
    catch (\InvalidArgumentException $e) {
      $thrown = TRUE;
    }

    $this->assertEquals($thrown, $exception);
    if (!$exception) {
      $this->assertNotSame($request, $updated);
      $this->assertEquals($expected, $updated->getMethod());
    }
  }

  /**
   * The test data for testWithProtocol().
   *
   * @return array
   *   The test data.
   */
  public function providerTestWithProtocol(): array {
    return [
      ['', '1.0'],
      ['1.1', '1.1'],
      ['hTTp', '1.0', TRUE],
      ['1.', '', TRUE],
      ['.1', '', TRUE],
    ];
  }

  /**
   * Tests withProtocol().
   *
   * @dataProvider providerTestWithProtocol
   */
  public function testWithProtocol(string $protocol, string $expected, bool $exception = FALSE): void {
    $request = new Request('GET', '', '');
    $thrown = FALSE;
    $updated = $request;
    try {
      $updated = $request->withProtocolVersion($protocol);
    }
    catch (\InvalidArgumentException $e) {
      $thrown = TRUE;
    }

    $this->assertEquals($thrown, $exception);
    if (!$exception) {
      $this->assertNotSame($request, $updated);
      $this->assertEquals($expected, $updated->getProtocolVersion());
    }
  }

  /**
   * The test data for testWithHeaders().
   *
   * @return array
   *   The test data.
   */
  public function providerTestWithHeaders(): array {
    return [
      ['', '1.0', ['' => ['1.0']], TRUE],
      [
        'X-A',
        'Connection=1; Framework=0',
        [
          'X-A' => ['Connection=1; Framework=0'],
        ],
      ],
      ['a', ['a', 'b'], ['a' => ['a', 'b']]],
      ['a', ["a\r\n b"], ['a' => ['a b']]],
      ["X-A\nX-A", '1.0', [], TRUE],
      ['a', "a\n", [], TRUE],
    ];
  }

  /**
   * Tests withHeaders().
   *
   * @dataProvider providerTestWithHeaders
   */
  public function testWithHeaders(string $header, string|array $value, array $expected, bool $exception = FALSE): void {
    $request = new Request('GET', '', '');
    $this->assertFalse($request->hasHeader($header));
    $this->assertEquals('', $request->getHeaderLine($header));
    $this->assertEquals([], $request->getHeader($header));

    $thrown = FALSE;
    $updated = $request;
    try {
      $updated = $request->withHeader($header, $value);
    }
    catch (\InvalidArgumentException $e) {
      $thrown = TRUE;
    }

    $this->assertEquals($thrown, $exception);
    if (!$exception) {
      $this->assertNotSame($request, $updated);
      $this->assertEquals($expected, $updated->getHeaders());
      $this->assertTrue($updated->hasHeader($header));
      $this->assertEquals($expected[$header], $updated->getHeader($header));
      $this->assertEquals(
        implode(',', $expected[$header]),
        $updated->getHeaderLine($header)
      );
    }
  }

  /**
   * The test data for testWithAddedHeader().
   *
   * @return array
   *   The test data.
   */
  public function providerTestWithAddedHeader(): array {
    return [
      [
        'X-A',
        'Connection=1; Framework=0',
        ['a'],
        [
          'X-A' => ['a', 'Connection=1; Framework=0'],
        ],
      ],
      ['', '1.0', [], [], TRUE],
      ['a', ['a', 'b'], [], ['a' => ['a', 'b']]],
      ['a', ["a\r\n b"], [], ['a' => ['a b']]],
      ["X-A\nX-A", '1.0', [], [], TRUE],
    ];
  }

  /**
   * Tests withAddedHeader().
   *
   * @dataProvider providerTestWithAddedHeader
   */
  public function testWithAddedHeader(string $header, string|array $value, array $initial, array $expected, bool $exception = FALSE): void {
    $headers = $initial ? [$header => $initial] : [];
    $request = new Request('GET', '', '', $headers);
    $this->assertEquals((bool) $initial, $request->hasHeader($header));
    $this->assertEquals(implode(',', $initial), $request->getHeaderLine($header));
    $this->assertEquals($initial, $request->getHeader($header));

    $thrown = FALSE;
    $updated = $request;
    try {
      $updated = $request->withAddedHeader($header, $value);
    }
    catch (\InvalidArgumentException $e) {
      $thrown = TRUE;
    }

    $this->assertEquals($thrown, $exception);
    if (!$exception) {
      $this->assertNotSame($request, $updated);
      $this->assertEquals($expected, $updated->getHeaders());
      $this->assertTrue($updated->hasHeader($header));
      $this->assertEquals($expected[$header], $updated->getHeader($header));
      $this->assertEquals(
        implode(',', $expected[$header]),
        $updated->getHeaderLine($header)
      );
    }
  }

  /**
   * The test data for testWithoutHeader().
   *
   * @return array
   *   The test data.
   */
  public function providerTestWithoutHeader(): array {
    return [
      [
        'X-A',
        ['a'],
      ],
    ];
  }

  /**
   * Tests withoutHeader().
   *
   * @dataProvider providerTestWithoutHeader
   */
  public function testWithoutHeader(string $header, array $initial): void {
    $headers = $initial ? [$header => $initial] : [];
    $request = new Request('GET', '', '', $headers);
    $this->assertEquals((bool) $initial, $request->hasHeader($header));
    $this->assertEquals(implode(',', $initial), $request->getHeaderLine($header));
    $this->assertEquals($initial, $request->getHeader($header));

    $updated = $request->withoutHeader($header);

    $this->assertNotSame($request, $updated);
    $this->assertFalse($updated->hasHeader($header));
    $this->assertEquals([], $updated->getHeader($header));
    $this->assertEquals('', $updated->getHeaderLine($header));
  }

  /**
   * Tests withBody().
   */
  public function testWithBody(): void {
    $uri = new Request('GET', '', '');
    $stream = $this->getMockBuilder(StreamInterface::class)->getMock();
    $updated = $uri->withBody($stream);
    $this->assertNotSame($uri, $updated);
    $this->assertNotSame($uri->getBody(), $updated->getBody());
  }

  /**
   * Tests withUri().
   */
  public function testWithUri(): void {
    $uri = new Request('GET', '', '');
    $url = $this->getMockBuilder(UriInterface::class)->getMock();
    $updated = $uri->withUri($url);
    $this->assertNotSame($uri, $updated);
    $this->assertNotSame($uri->getUri(), $updated->getUri());
  }

  /**
   * The test data for testWithRequestTarget().
   *
   * @return array
   *   The test data.
   */
  public function providerTestWithRequestTarget(): array {
    return [
      ['', '/'],
      ['*', '*'],
      ['/test?query', '/test?query'],
      ["\x00\x01", '', TRUE],
    ];
  }

  /**
   * Tests withRequestTarget().
   *
   * @dataProvider providerTestWithRequestTarget
   */
  public function testWithRequestTarget(string $target, string $expected, bool $exception = FALSE): void {
    $request = new Request('GET', '', '');
    $thrown = FALSE;
    $updated = $request;
    try {
      $updated = $request->withRequestTarget($target);
    }
    catch (\InvalidArgumentException $e) {
      $thrown = TRUE;
    }

    $this->assertEquals($thrown, $exception);
    if (!$exception) {
      $this->assertNotSame($request, $updated);
      $this->assertEquals($expected, $updated->getRequestTarget());
    }
  }

}
