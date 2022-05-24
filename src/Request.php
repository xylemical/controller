<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use function preg_match;

/**
 * Provides a generic request.
 */
class Request implements RequestInterface {

  /**
   * Provides the regex encompassing.
   */
  public const REQUEST_REGEX = '
  (?(DEFINE)
    (?<obs_fold>  \r\n [ \t]+ )
    (?<field_name> [a-zA-Z0-9\!\#\$\%\&\'\*\+\-\.\^\_\`\|\~]+ )
    (?<field_vchar> [\x21-\x7E\x80-\xFF] )
    (?<field_content> (?&field_vchar) (?: [ \t]+ (?&field_vchar) )? )
    (?<field_value> (?: (?&field_content) | (?&obs_fold) )* )
  )';

  /**
   * The regex used to validate the header name.
   */
  public const HEADER_REGEX = '/' . self::REQUEST_REGEX . '^(?&field_name)$/xD';

  /**
   * The regex used to validate the header value.
   */
  public const HEADER_VALUE_REGEX = '/' . self::REQUEST_REGEX . '^(?&field_value)$/xD';

  public const METHODS = [
    'GET',
    'PUT',
    'POST',
    'PATCH',
    'DELETE',
    'OPTIONS',
    'TRACE',
    'CONNECT',
  ];

  /**
   * The protocol.
   *
   * @var string
   */
  protected string $protocolVersion;

  /**
   * The method.
   *
   * @var string
   */
  protected string $method = 'GET';

  /**
   * The headers.
   *
   * @var array
   */
  protected array $headers;

  /**
   * The request target.
   *
   * @var string
   */
  protected string $requestTarget = '/';

  /**
   * The URI.
   *
   * @var \Psr\Http\Message\UriInterface
   */
  protected UriInterface $uri;

  /**
   * The body.
   *
   * @var \Psr\Http\Message\StreamInterface
   */
  protected StreamInterface $body;

  /**
   * Request constructor.
   *
   * @param string $method
   *   The method.
   * @param string $uri
   *   The uri.
   * @param string $body
   *   The body.
   * @param array $headers
   *   The headers.
   * @param string $protocolVersion
   *   The protocol version.
   */
  public function __construct(string $method, string $uri, string $body, array $headers = [], string $protocolVersion = '1.0') {
    $this->method = $method;
    $this->protocolVersion = $this->validateProtocol($protocolVersion);
    $this->headers = $this->validateHeaders($headers);
    $this->uri = new Uri($uri);
    $this->body = new Stream($body);
  }

  /**
   * {@inheritdoc}
   */
  public function getProtocolVersion(): string {
    return $this->protocolVersion;
  }

  /**
   * {@inheritdoc}
   */
  public function withProtocolVersion($version): RequestInterface {
    $clone = clone($this);
    $clone->protocolVersion = $this->validateProtocol($version) ?: '1.0';
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function getHeaders(): array {
    return $this->headers;
  }

  /**
   * {@inheritdoc}
   */
  public function hasHeader($name): bool {
    return isset($this->headers[$name]);
  }

  /**
   * {@inheritdoc}
   */
  public function getHeader($name): array {
    return $this->headers[$name] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getHeaderLine($name): string {
    return implode(',', $this->headers[$name] ?? []);
  }

  /**
   * {@inheritdoc}
   */
  public function withHeader($name, $value): RequestInterface {
    $clone = clone($this);
    $name = $this->validateHeader($name);
    $clone->headers[$name] = $this->validateHeaderValues((array) $value);
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function withAddedHeader($name, $value): RequestInterface {
    $clone = clone($this);
    $name = $this->validateHeader($name);
    $clone->headers[$name] = array_merge(
      $this->headers[$name] ?? [],
      $this->validateHeaderValues((array) $value)
    );
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function withoutHeader($name): RequestInterface {
    $clone = clone($this);
    unset($clone->headers[$name]);
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function getBody(): StreamInterface {
    return $this->body;
  }

  /**
   * {@inheritdoc}
   */
  public function withBody(StreamInterface $body): RequestInterface {
    $clone = clone($this);
    $clone->body = $body;
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function getRequestTarget(): string {
    return $this->requestTarget ?: '/';
  }

  /**
   * {@inheritdoc}
   */
  public function withRequestTarget($requestTarget): RequestInterface {
    $clone = clone($this);
    $clone->requestTarget = $this->validateRequestTarget((string) $requestTarget);
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function getMethod(): string {
    return $this->method;
  }

  /**
   * {@inheritdoc}
   */
  public function withMethod($method): RequestInterface {
    $clone = clone($this);
    $clone->method = $this->validateMethod($method);
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function getUri(): UriInterface {
    return $this->uri;
  }

  /**
   * {@inheritdoc}
   */
  public function withUri(UriInterface $uri, $preserveHost = FALSE): RequestInterface {
    $clone = clone($this);
    $clone->uri = $uri;
    return $clone;
  }

  /**
   * Validate and normalize the protocol version.
   *
   * @param string $protocol
   *   The protocol.
   *
   * @return string
   *   The protocol.
   */
  protected function validateProtocol(string $protocol): string {
    if ($protocol && !preg_match('/^\d+\.\d+/', $protocol)) {
      throw new \InvalidArgumentException("'{$protocol}' is not a valid protocol.");
    }
    return $protocol;
  }

  /**
   * Validate the header.
   *
   * @param string $header
   *   The header.
   *
   * @return string
   *   The header.
   */
  protected function validateHeader(string $header): string {
    if (!preg_match(static::HEADER_REGEX, $header)) {
      throw new \InvalidArgumentException("'{$header}' is not a valid header.");
    }
    return $header;
  }

  /**
   * Validate and normalize the headers.
   *
   * @param array $headers
   *   The headers.
   *
   * @return array
   *   The normalized headers.
   */
  protected function validateHeaders(array $headers): array {
    foreach ($headers as $header => $value) {
      $header = $this->validateHeader($header);
      $headers[$header] = $this->validateHeaderValues($value);
    }
    return $headers;
  }

  /**
   * Validate the header value.
   *
   * @param string $value
   *   The header value.
   *
   * @return string
   *   The header value.
   *
   * @throws \InvalidArgumentException
   */
  protected function validateHeaderValue(string $value): string {
    if (!preg_match(static::HEADER_VALUE_REGEX, $value)) {
      throw new \InvalidArgumentException("'{$value}' is not a valid header value.");
    }
    return preg_replace('/\r\n[ \t]+/', ' ', $value);
  }

  /**
   * Validates all header values.
   *
   * @param array $values
   *   The values.
   *
   * @return array
   *   The values.
   */
  protected function validateHeaderValues(array $values): array {
    $validated = [];
    foreach ($values as $value) {
      $validated[] = $this->validateHeaderValue($value);
    }
    return $validated;
  }

  /**
   * Validate the method.
   *
   * @param string $method
   *   The method.
   *
   * @return string
   *   The method.
   *
   * @throws \InvalidArgumentException
   */
  protected function validateMethod(string $method): string {
    $method = strtoupper($method);
    if (!in_array($method, static::METHODS)) {
      throw new \InvalidArgumentException("'{$method}' is not a valid method.");
    }
    return $method;
  }

  /**
   * Validates the request target.
   *
   * @param string $target
   *   The target.
   *
   * @return string
   *   The target.
   *
   * @throws \InvalidArgumentException
   */
  protected function validateRequestTarget(string $target): string {
    if (!$target || $target === '*') {
      return $target;
    }
    elseif ((string) (new Uri($target)) !== $target) {
      throw new \InvalidArgumentException("'{$target}' is not a valid request target.");
    }
    return $target;
  }

}
