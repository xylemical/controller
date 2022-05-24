<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\UriInterface;
use function explode;
use function implode;
use function intval;
use function is_null;
use function parse_url;
use function preg_match;
use function rawurldecode;
use function rawurlencode;
use function strtolower;

/**
 * Provides generic implementation of UriInterface.
 */
class Uri implements UriInterface {

  /**
   * Contains all the regex definitions from RFC3986.
   */
  public const URI_REGEX = '
  (?(DEFINE)
    (?<HEX> [0-9a-fA-F] )

    (?<scheme> [a-zA-Z] (?: [a-zA-Z] | \d | \+ | \- | \. )* )
    
    (?<pct_encoded> \% (?&HEX) (?&HEX) )
    (?<sub_delims> [\! \$ \& \' \( \) \* \+ \, \; \=] )
    (?<gen_delims> [\: \/ \? \# \[ \] \@] )
    (?<unreserved> [0-9 a-z A-Z \- \. \_ \~] )
    (?<reserved> (?&gen_delims) | (?&sub_delims) )
    (?<reg_name> (?: (?&unreserved) | (?&pct_encoded) | (?&sub_delims) )* )
    
    (?<pchar>    (?&unreserved) | (?&pct_encoded) | (?&sub_delims) | \: | \@ )
    (?<segment>       (?&pchar)* )
    (?<segment_nz>    (?&pchar)+ )
    (?<segment_nz_nc> (?: (?&unreserved) | (?&pct_encoded) | (?&sub_delims) | \@ )+ )
    (?<path_abempty>     (?: \/ (?&segment) )* )
    (?<path_absolute>    \/ (?: (?&segment_nz) (?: \/ (?&segment) )* )? )
    (?<path_noscheme>    (?&segment_nz_nc) (?: \/ (?&segment) )* )
    (?<path_rootless>    (?&segment_nz) (?: \/ (?&segment) )* )
    (?<path_empty>       (?&pchar){0} )

    (?<dec_octet> [0-9] | [0-9][0-9] | 1[0-9]{2} | 2[0-4][0-9] | 25[0-5] )
    
    (?<ipv4_address> (?&dec_octet) \. (?&dec_octet) \. (?&dec_octet) \. (?&dec_octet) )
    (?<h16> (?&HEX){1,4} )
    (?<ls32> (?: (?&h16) \: (?&h16) ) | (?&ipv4_address) )                           
    (?<ipv6_address>                              (?: (?&h16) \: ){6} (?&ls32)
      |                                      \:\: (?: (?&h16) \: ){5} (?&ls32)
      |                           (?&h16)?   \:\: (?: (?&h16) \: ){4} (?&ls32)
      | (?: (?: (?&h16) \: ){0,1} (?&h16) )? \:\: (?: (?&h16) \: ){3} (?&ls32)
      | (?: (?: (?&h16) \: ){0,2} (?&h16) )? \:\: (?: (?&h16) \: ){2} (?&ls32)
      | (?: (?: (?&h16) \: ){0,3} (?&h16) )? \:\:     (?&h16) \:      (?&ls32)
      | (?: (?: (?&h16) \: ){0,4} (?&h16) )? \:\:                     (?&ls32)
      | (?: (?: (?&h16) \: ){0,5} (?&h16) )? \:\:                     (?&h16)
      | (?: (?: (?&h16) \: ){0,6} (?&h16) )? \:\: )
    (?<ipv_future> v (?&HEX)+ \. (?: (?&unreserved) | (?&sub_delims) | \: )+ )
    (?<ip_literal> \[ (?: (?&ipv6_address) | (?&ipv_future) ) \] )
    (?<host> (?&ip_literal) | (?&ipv4_address) | (?&reg_name) )
    
    (?<port> \d* )
    (?<userinfo> (?: (?&unreserved) | (?&pct_encoded) | (?&sub_delims) | \: )* )
    (?<authority> (?: (?&userinfo) \@ )? (?&host) (?: \: (?&port) )? )
      
    (?<hier_part> \/\/ (?&authority) (?&path_abempty) 
      | (?&path_absolute)
      | (?&path_rootless)
      | (?&path_empty) ) 

    (?<query>    (?: (?&pchar) | \/ | \? )* )
    (?<fragment>    (?: (?&pchar) | \/ | \? )* )
      
    (?<absolute_uri> (?&scheme) \: (?&hier_part) (?: \? (?&query) )? )
    (?<relative_ref> (?&relative_part) (?: \? (?&query) )? (?: \# (?&fragment) )? )
    (?<relative_part> \/\/ (?&authority) (?&path_abempty)
      | (?&path_absolute)
      | (?&path_noscheme)
      | (?&path_empty) )

    (?<path> (?&path_abempty)
      | (?&path_absolute)
      | (?&path_noscheme)
      | (?&path_empty) )
                         
    (?<uri> (?&scheme) \: (?&hier_part) (?: \? (?&query) )? (?: \# (?&fragment) )? )
    (?<uri_reference> (?&uri) | (?&relative_ref) )
  )';

  /**
   * The regex to match a scheme from RFC3986.
   */
  public const SCHEME_REGEX = '/' . self::URI_REGEX . '^(?&scheme)$/xD';

  /**
   * The regex to match a host from RFC3986.
   */
  public const HOST_REGEX = '/' . self::URI_REGEX . '^(?&host)$/xD';

  /**
   * The regex to match a user from RFC3986.
   */
  public const USER_REGEX = '/' . self::URI_REGEX . '^(?&reg_name)$/xD';

  /**
   * The regex to match a password from RFC3986.
   */
  public const PASS_REGEX = '/' . self::URI_REGEX . '^(?&reg_name)$/xD';

  /**
   * The regex to match a path from RFC3986.
   */
  public const PATH_REGEX = '/' . self::URI_REGEX . '^(?&path)$/xD';

  /**
   * The regex to match a fragment from RFC3986.
   */
  public const FRAGMENT_REGEX = '/' . self::URI_REGEX . '^(?&fragment)$/x';

  /**
   * The scheme.
   *
   * @var string
   */
  protected string $scheme;

  /**
   * The host.
   *
   * @var string
   */
  protected string $host;

  /**
   * The username.
   *
   * @var string
   */
  protected string $user;

  /**
   * The password.
   *
   * @var string|null
   */
  protected ?string $password = NULL;

  /**
   * The port.
   *
   * @var int|null
   */
  protected ?int $port = NULL;

  /**
   * The path.
   *
   * @var string
   */
  protected string $path;

  /**
   * The query values.
   *
   * @var array
   */
  protected array $query = [];

  /**
   * The fragment.
   *
   * @var string
   */
  protected string $fragment;

  /**
   * Uri constructor.
   *
   * @param string $uri
   *   The URI.
   */
  public function __construct(string $uri) {
    $parts = parse_url($uri);
    $this->scheme = $this->validateScheme($parts['scheme'] ?? '');
    $this->host = $this->validateHost($parts['host'] ?? '');
    $this->port = intval($parts['port'] ?? '') ?: NULL;
    $this->user = $this->validateUser($parts['user'] ?? '');
    if ($this->user && isset($parts['pass'])) {
      $this->password = $this->validatePass($parts['pass']);
    }
    $this->path = $this->validatePath($parts['path'] ?? '');
    $this->fragment = $parts['fragment'] ?? '';
    $this->query = $this->parseQuery($parts['query'] ?? '');
  }

  /**
   * {@inheritdoc}
   */
  public function getScheme(): string {
    return $this->scheme;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthority(): string {
    $authority = $this->host;
    if ($userinfo = $this->getUserInfo()) {
      $authority = "{$userinfo}@{$authority}";
    }
    if (!is_null($this->port)) {
      $authority .= ":{$this->port}";
    }
    return $authority;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserInfo(): string {
    return match (TRUE) {
      !$this->user => '',
      is_null($this->password) => $this->user,
      default => "{$this->user}:{$this->password}",
    };
  }

  /**
   * Get the unencoded username.
   *
   * @return string
   *   The username.
   */
  public function getUser(): string {
    return rawurldecode($this->user);
  }

  /**
   * Get the unencoded password.
   *
   * @return string|null
   *   The password.
   */
  public function getPassword(): ?string {
    return is_null($this->password) ? NULL : rawurldecode($this->password);
  }

  /**
   * {@inheritdoc}
   */
  public function getHost(): string {
    return $this->host;
  }

  /**
   * {@inheritdoc}
   */
  public function getPort(): ?int {
    return $this->port;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath(): string {
    return $this->path;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery(): string {
    $query = [];
    foreach ($this->query as $key => $value) {
      if ($value === TRUE) {
        $query[] = rawurlencode($key);
      }
      else {
        $query[] = rawurlencode($key) . '=' . rawurlencode($value);
      }
    }
    return implode('&', $query);
  }

  /**
   * Get the query values.
   *
   * @return array
   *   The values.
   */
  public function getQueryValues(): array {
    return $this->query;
  }

  /**
   * {@inheritdoc}
   */
  public function getFragment(): string {
    return $this->fragment;
  }

  /**
   * {@inheritdoc}
   */
  public function withScheme($scheme): UriInterface {
    $clone = clone($this);
    $clone->scheme = $this->validateScheme($scheme);
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function withUserInfo($user, $password = NULL): UriInterface {
    $clone = clone($this);
    $clone->user = $this->validateUser(rawurlencode($user));
    $clone->password = !is_null($password) ?
      $this->validatePass(rawurlencode($password)) :
      NULL;
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function withHost($host): UriInterface {
    $clone = clone($this);
    $clone->host = $this->validateHost($host);
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function withPort($port): UriInterface {
    if (!is_null($port) && ($port < 0 || $port > 65535)) {
      throw new \InvalidArgumentException("'{$port}' is not a valid port.");
    }
    $clone = clone($this);
    $clone->port = $port;
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function withPath($path): UriInterface {
    $clone = clone($this);
    $clone->path = $this->validatePath($path);
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function withQuery($query): UriInterface {
    if (rawurldecode($query) === $query) {
      $query = rawurlencode($query);
    }
    $clone = clone($this);
    $clone->query = $this->parseQuery($query);
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function withFragment($fragment): UriInterface {
    $clone = clone($this);
    $clone->fragment = $this->validateFragment($fragment);
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    $output = $this->scheme ? "{$this->scheme}:" : '';
    if ($authority = $this->getAuthority()) {
      $output .= "//{$authority}{$this->getPath()}";
    }
    else {
      $output .= $this->getPath();
    }
    if ($query = $this->getQuery()) {
      $output .= "?{$query}";
    }
    if ($fragment = $this->getFragment()) {
      $output .= "#{$fragment}";
    }
    return $output;
  }

  /**
   * Parse a query string into parts.
   *
   * @param string $query
   *   The query string.
   *
   * @return array
   *   The query values.
   */
  protected function parseQuery(string $query): array {
    $values = [];
    foreach (explode('&', rawurldecode($query)) as $part) {
      if ($part) {
        $subParts = explode('=', $part, 2);
        switch (count($subParts)) {
          case 2:
            $values[$subParts[0]] = $subParts[1];
            break;

          case 1:
            $values[$subParts[0]] = TRUE;
            break;
        }
      }
    }
    return $values;
  }

  /**
   * Validate and normalize the scheme.
   *
   * @param string $scheme
   *   The scheme.
   *
   * @return string
   *   The normalized scheme.
   *
   * @throws \InvalidArgumentException
   *
   * @see https://tools.ietf.org/html/rfc3986#section-3.1
   */
  protected function validateScheme(string $scheme): string {
    if ($scheme && !preg_match(static::SCHEME_REGEX, $scheme)) {
      throw new \InvalidArgumentException("'{$scheme}' is not a valid scheme.");
    }
    return strtolower($scheme);
  }

  /**
   * Validate and normalize the host.
   *
   * @param string $host
   *   The host.
   *
   * @return string
   *   The normalized host.
   *
   * @throws \InvalidArgumentException
   *
   * @see https://tools.ietf.org/html/rfc3986#section-3.2.2
   */
  protected function validateHost(string $host): string {
    if ($host && !preg_match(static::HOST_REGEX, $host)) {
      throw new \InvalidArgumentException("'{$host}' is not a valid host.");
    }
    return strtolower($host);
  }

  /**
   * Validate the username is valid for a URI.
   *
   * @param string $user
   *   The username.
   *
   * @return string
   *   The username.
   *
   * @see https://tools.ietf.org/html/rfc3986#section-3.2.1
   */
  protected function validateUser(string $user): string {
    // No real validation as parse_url rewrites invalid characters, and the
    // withUserInfo() encodes invalid characters.
    return $user;
  }

  /**
   * Validate the password is valid for a URI.
   *
   * @param string $pass
   *   The password.
   *
   * @return string
   *   The password.
   *
   * @see https://tools.ietf.org/html/rfc3986#section-3.2.1
   */
  protected function validatePass(string $pass): string {
    // No real validation as parse_url rewrites invalid characters, and the
    // withUserInfo() encodes invalid characters.
    return $pass;
  }

  /**
   * Validate the path is valid for a URI.
   *
   * @param string $path
   *   The path.
   *
   * @return string
   *   The path.
   *
   * @see https://tools.ietf.org/html/rfc3986#section-3.3
   */
  protected function validatePath(string $path): string {
    if ($path && !preg_match(static::PATH_REGEX, $path)) {
      throw new \InvalidArgumentException("'{$path}' is not a valid path.");
    }
    return $path ?: '/';
  }

  /**
   * Validate the fragment is valid for a URI.
   *
   * @param string $fragment
   *   The fragment.
   *
   * @return string
   *   The fragment.
   *
   * @see https://tools.ietf.org/html/rfc3986#section-3.5
   */
  protected function validateFragment(string $fragment): string {
    if ($fragment && !preg_match(static::FRAGMENT_REGEX, $fragment)) {
      throw new \InvalidArgumentException("'{$fragment}' is not a valid fragment.");
    }
    return $fragment;
  }

}
