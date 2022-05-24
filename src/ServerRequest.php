<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\ServerRequestInterface;
use function array_map;
use function explode;
use function file_get_contents;
use function implode;
use function in_array;
use function parse_str;
use function preg_match;
use function str_starts_with;
use function strtolower;
use function substr;

/**
 * Provides the server request.
 */
class ServerRequest extends Request implements ServerRequestInterface {

  /**
   * The server variables.
   *
   * @var array
   */
  protected array $server = [];

  /**
   * The cookies.
   *
   * @var array
   */
  protected array $cookies = [];

  /**
   * The parsed body.
   *
   * @var array
   */
  protected array $parsedBody = [];

  /**
   * The uploaded files.
   *
   * @var array
   */
  protected array $files = [];

  /**
   * The parsed query values.
   *
   * @var array
   */
  protected array $query = [];

  /**
   * The attributes of the server request.
   *
   * @var array
   */
  protected array $attributes = [];

  /**
   * ServerRequest constructor.
   *
   * @param array $server
   *   The server variables.
   * @param array $cookies
   *   The cookies.
   * @param array $files
   *   The files.
   * @param string $body
   *   The request body.
   * @param array $post
   *   The parsed request data.
   */
  public function __construct(array $server, array $cookies, array $files, string $body, array $post = []) {
    parent::__construct(
      $this->parseMethod($server),
      $this->parseUri($server),
      $body,
      $this->parseHeaders($server),
      $this->parseProtocol($server),
    );
    $this->server = $server;
    $this->cookies = $cookies;
    $this->parsedBody = $post;
    $this->files = $this->parseFiles($files);
    // @phpstan-ignore-next-line
    $this->query = $this->uri->getQueryValues();
  }

  /**
   * {@inheritdoc}
   */
  public function getServerParams(): array {
    return $this->server;
  }

  /**
   * {@inheritdoc}
   */
  public function getCookieParams(): array {
    return $this->cookies;
  }

  /**
   * {@inheritdoc}
   */
  public function withCookieParams(array $cookies): ServerRequestInterface {
    $clone = clone($this);
    $clone->cookies = $cookies;
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryParams(): array {
    return $this->query;
  }

  /**
   * {@inheritdoc}
   */
  public function withQueryParams(array $query): ServerRequestInterface {
    $clone = clone($this);
    $clone->query = $query;
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function getUploadedFiles(): array {
    return $this->files;
  }

  /**
   * {@inheritdoc}
   */
  public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface {
    $clone = clone($this);
    $clone->files = $uploadedFiles;
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function getParsedBody() {
    return $this->parsedBody;
  }

  /**
   * {@inheritdoc}
   */
  public function withParsedBody($data): ServerRequestInterface {
    $clone = clone($this);
    $clone->parsedBody = $data;
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributes(): array {
    return $this->attributes;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttribute($name, $default = NULL) {
    return $this->attributes[$name] ?? $default;
  }

  /**
   * {@inheritdoc}
   */
  public function withAttribute($name, $value): ServerRequestInterface {
    $clone = clone($this);
    $clone->attributes[$name] = $value;
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function withoutAttribute($name): ServerRequestInterface {
    $clone = clone($this);
    unset($clone->attributes[$name]);
    return $clone;
  }

  /**
   * Parse the method from the server values.
   *
   * @param array $server
   *   The server values.
   *
   * @return string
   *   The method.
   */
  protected function parseMethod(array $server): string {
    return $server['REQUEST_METHOD'] ?? 'GET';
  }

  /**
   * Parse the uri from the server values.
   *
   * @param array $server
   *   The server values.
   *
   * @return string
   *   The uri.
   */
  protected function parseUri(array $server): string {
    if (!isset($server['REQUEST_URI'])) {
      $server['REQUEST_URI'] = substr($server['PHP_SELF'], 1);
      if (isset($server['QUERY_STRING'])) {
        $server['REQUEST_URI'] .= "?{$server['QUERY_STRING']}";
      }
    }
    if (!isset($server['HTTP_HOST'])) {
      $server['HTTP_HOST'] = $server['SERVER_HOST'] ?? 'localhost';
    }
    $uri = isset($server['HTTPS']) ? "https" : "http";
    $uri .= "://{$server['HTTP_HOST']}{$server['REQUEST_URI']}";
    $uri = new Uri($uri);
    return (string) $uri;
  }

  /**
   * Parse the headers from the server values.
   *
   * @param array $server
   *   The server values.
   *
   * @return array
   *   The headers.
   */
  protected function parseHeaders(array $server): array {
    $headers = [];
    foreach ($server as $key => $value) {
      $key = strtolower($key);
      if (!str_starts_with($key, 'http_')) {
        continue;
      }

      $key = implode('-', array_map('ucfirst', explode('_', $key)));
      $headers[$key][] = $value;
    }
    return $headers;
  }

  /**
   * Parse the http protocol from the server values.
   *
   * @param array $server
   *   The server.
   *
   * @return string
   *   The protocol version.
   */
  protected function parseProtocol(array $server): string {
    $protocol = '1.0';
    if (preg_match('#^HTTP/(\d+\.\d+)$#', $server['SERVER_PROTOCOL'] ?? '1.0', $match)) {
      $protocol = $match[1];
    }
    return $protocol;
  }

  /**
   * Parse the files into uploaded files.
   *
   * @param array $files
   *   The files.
   *
   * @return array
   *   The uploaded files.
   */
  protected function parseFiles(array $files): array {
    // @todo Parse the files.
    return [];
  }

  /**
   * Creates the server request from the globals.
   *
   * @return \Xylemical\Controller\ServerRequest
   *   The request.
   */
  public static function createFromGlobals(): ServerRequest {
    $request = new ServerRequest(
      $_SERVER,
      $_COOKIE,
      $_FILES,
      file_get_contents('php://input'),
      $_POST,
    );
    if ($request->getHeaderLine('Content-Type') === 'application/www-url-encoded' &&
      in_array($request->getMethod(), ['PUT', 'PATCH', 'DELETE'])) {
      parse_str($request->getBody()->getContents(), $request->parsedBody);
    }
    return $request;
  }

}
