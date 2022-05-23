<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use PHPUnit\Framework\TestCase;

/**
 * Tests \Xylemical\Controller\Uri.
 */
class UriTest extends TestCase {

  /**
   * The test data for testGetScheme().
   *
   * @return array
   *   The test data.
   */
  public function providerTestGetScheme(): array {
    return [
      ['/resources', ''],
      ['http://resources', 'http'],
      ['hTTp://resources', 'http'],
    ];
  }

  /**
   * Tests getScheme().
   *
   * @dataProvider providerTestGetScheme
   */
  public function testGetScheme(string $uri, string $expected): void {
    $uri = new Uri($uri);
    $this->assertEquals($expected, $uri->getScheme());
  }

  /**
   * The test data for testWithScheme().
   *
   * @return array
   *   The test data.
   */
  public function providerTestWithScheme(): array {
    return [
      ['', ''],
      ['http', 'http'],
      ['hTTp', 'http'],
      ['http:', '', TRUE],
    ];
  }

  /**
   * Tests withScheme().
   *
   * @dataProvider providerTestWithScheme
   */
  public function testWithScheme(string $scheme, string $expected, bool $exception = FALSE): void {
    $uri = new Uri('');
    $thrown = FALSE;
    $updated = $uri;
    try {
      $updated = $uri->withScheme($scheme);
    }
    catch (\InvalidArgumentException $e) {
      $thrown = TRUE;
    }

    $this->assertEquals($thrown, $exception);
    if (!$exception) {
      $this->assertNotSame($uri, $updated);
      $this->assertEquals($expected, $updated->getScheme());
    }
  }

  /**
   * The test data for testGetHost().
   *
   * @return array
   *   The test data.
   */
  public function providerTestGetHost(): array {
    return [
      ['/resources', ''],
      ['http://resources', 'resources'],
      ['hTTp://reSources', 'resources'],
    ];
  }

  /**
   * Tests getHost().
   *
   * @dataProvider providerTestGetHost
   */
  public function testGetHost(string $uri, string $expected): void {
    $uri = new Uri($uri);
    $this->assertEquals($uri->getHost(), $expected);
  }

  /**
   * The test data for testWithHost().
   *
   * @return array
   *   The test data.
   */
  public function providerTestWithHost(): array {
    return [
      ['', ''],
      ['localhost', 'localhost'],
      ['LoCaLhOsT', 'localhost'],
      ['localhost.local', 'localhost.local'],
      ['.local', '.local'],
      ['local.', 'local.'],
      ['0.0.0.0', '0.0.0.0'],
      ['0.0.0.0.', '0.0.0.0.'],
      ['.0.0.0.0', '.0.0.0.0'],
      ['0.0.0', '0.0.0'],
      ['0.0.0.0.0', '0.0.0.0.0'],
      ['[::0]', '[::0]'],
      ["local\x17host", '', TRUE],
    ];
  }

  /**
   * Tests withHost().
   *
   * @dataProvider providerTestWithHost
   */
  public function testWithHost(string $host, string $expected, bool $exception = FALSE): void {
    $uri = new Uri('');
    $thrown = FALSE;
    $updated = $uri;
    try {
      $updated = $uri->withHost($host);
    }
    catch (\InvalidArgumentException $e) {
      $thrown = TRUE;
    }

    $this->assertEquals($thrown, $exception);
    if (!$exception) {
      $this->assertNotSame($uri, $updated);
      $this->assertEquals($expected, $updated->getHost());
    }
  }

  /**
   * The test data for testWithUserInfo().
   *
   * @return array
   *   The test data.
   */
  public function providerTestWithUserInfo(): array {
    return [
      ['', '', ''],
      ['user', NULL, 'user'],
      ['user', '', 'user:'],
      ['user', 'password', 'user:password'],
      ['{@us}er', "{@password}\x01", '%7B%40us%7Der:%7B%40password%7D%01'],
    ];
  }

  /**
   * Tests withUserInfo().
   *
   * @dataProvider providerTestWithUserInfo
   */
  public function testWithUserInfo(string $user, ?string $password, string $expected, bool $exception = FALSE): void {
    $uri = new Uri('');
    $thrown = FALSE;
    $updated = $uri;
    try {
      $updated = $uri->withUserInfo($user, $password);
    }
    catch (\InvalidArgumentException $e) {
      $thrown = TRUE;
    }

    $this->assertEquals($thrown, $exception);
    if (!$exception) {
      $this->assertNotSame($uri, $updated);
      $this->assertEquals($expected, $updated->getUserInfo());
      $this->assertEquals($user, $updated->getUser());
      $this->assertEquals($password, $updated->getPassword());
    }
  }

  /**
   * The test data for testWithPort().
   *
   * @return array
   *   The test data.
   */
  public function providerTestWithPort(): array {
    return [
      [NULL, NULL],
      [0, 0],
      [1000, 1000],
      [65536, NULL, TRUE],
      [-1, NULL, TRUE],
    ];
  }

  /**
   * Tests withPort().
   *
   * @dataProvider providerTestWithPort
   */
  public function testWithPort(?int $port, ?int $expected, bool $exception = FALSE): void {
    $uri = new Uri('');
    $thrown = FALSE;
    $updated = $uri;
    try {
      $updated = $uri->withPort($port);
    }
    catch (\InvalidArgumentException $e) {
      $thrown = TRUE;
    }

    $this->assertEquals($thrown, $exception);
    if (!$exception) {
      $this->assertNotSame($uri, $updated);
      $this->assertEquals($expected, $updated->getPort());
    }
  }

  /**
   * The test data for testWithPath().
   *
   * @return array
   *   The test data.
   */
  public function providerTestWithPath(): array {
    return [
      ['', '/'],
      ['/', '/'],
      ['//', '//'],
      ['path', 'path'],
      ['path/path', 'path/path'],
      ['{path}', '', TRUE],
    ];
  }

  /**
   * Tests withPath().
   *
   * @dataProvider providerTestWithPath
   */
  public function testWithPath(string $path, string $expected, bool $exception = FALSE): void {
    $uri = new Uri('');
    $thrown = FALSE;
    $updated = $uri;
    try {
      $updated = $uri->withPath($path);
    }
    catch (\InvalidArgumentException $e) {
      $thrown = TRUE;
    }

    $this->assertEquals($thrown, $exception);
    if (!$exception) {
      $this->assertNotSame($uri, $updated);
      $this->assertEquals($expected, $updated->getPath());
    }
  }

  /**
   * The test data for testWithQuery().
   *
   * @return array
   *   The test data.
   */
  public function providerTestWithQuery(): array {
    return [
      ['', '', []],
      ['a', 'a', ['a' => TRUE]],
      ['a=1', 'a=1', ['a' => '1']],
      ['a=1&b&c=2', 'a=1&b&c=2', ['a' => '1', 'b' => TRUE, 'c' => '2']],
      [
        'a=1&b=1=1&c=2',
        'a=1&b=1%3D1&c=2',
        ['a' => '1', 'b' => '1=1', 'c' => '2'],
      ],
      ['a=%20', 'a=%20', ['a' => ' ']],
    ];
  }

  /**
   * Tests withQuery().
   *
   * @dataProvider providerTestWithQuery
   */
  public function testWithQuery(string $query, string $expected, array $values, bool $exception = FALSE): void {
    $uri = new Uri('');
    $thrown = FALSE;
    $updated = $uri;
    try {
      $updated = $uri->withQuery($query);
    }
    catch (\InvalidArgumentException $e) {
      $thrown = TRUE;
    }

    $this->assertEquals($thrown, $exception);
    if (!$exception) {
      $this->assertNotSame($uri, $updated);
      $this->assertEquals($expected, $updated->getQuery());
      $this->assertEquals($values, $updated->getQueryValues());
    }
  }

  /**
   * The test data for testWithFragment().
   *
   * @return array
   *   The test data.
   */
  public function providerTestWithFragment(): array {
    return [
      ['', ''],
      ['fragment', 'fragment'],
      ["\x01", '', TRUE],
    ];
  }

  /**
   * Tests withFragment().
   *
   * @dataProvider providerTestWithFragment
   */
  public function testWithFragment(string $fragment, string $expected, bool $exception = FALSE): void {
    $uri = new Uri('');
    $thrown = FALSE;
    $updated = $uri;
    try {
      $updated = $uri->withFragment($fragment);
    }
    catch (\InvalidArgumentException $e) {
      $thrown = TRUE;
    }

    $this->assertEquals($thrown, $exception);
    if (!$exception) {
      $this->assertNotSame($uri, $updated);
      $this->assertEquals($expected, $updated->getFragment());
    }
  }

  /**
   * The test data for testWithToString().
   *
   * @return array
   *   The test data.
   */
  public function providerTestWithToString(): array {
    return [
      ['', '/'],
      ['/', '/'],
      ['//', '/'],
      ['//something.com', '//something.com/'],
      [
        'https://localhost',
        'https://localhost/',
      ],
      [
        'https://user@localhost',
        'https://user@localhost/',
      ],
      [
        'https://user:pass@localhost',
        'https://user:pass@localhost/',
      ],
      [
        'https://localhost:8080',
        'https://localhost:8080/',
      ],
      [
        'https://user:pass@localhost:8080',
        'https://user:pass@localhost:8080/',
      ],
      [
        'data:format',
        'data:format',
      ],
      [
        'https://localhost/path/to/something?query#fragment',
        'https://localhost/path/to/something?query#fragment',
      ],
      [
        'https://localhost/path/to/something#fragment?query',
        'https://localhost/path/to/something#fragment?query',
      ],
      ["https://user\x17@localhost", "https://user_@localhost/"],
      ["https://loc\x17host/", 'https://loc_host/'],
      ['{toString}', '', TRUE],
    ];
  }

  /**
   * Tests withToString().
   *
   * @dataProvider providerTestWithToString
   */
  public function testWithToString(string $url, string $expected, bool $exception = FALSE): void {
    $thrown = FALSE;
    $uri = NULL;
    try {
      $uri = new Uri($url);
    }
    catch (\InvalidArgumentException $e) {
      $thrown = TRUE;
    }

    $this->assertEquals($thrown, $exception);
    if (!$exception) {
      $this->assertEquals($expected, (string) $uri);
    }
  }

}
