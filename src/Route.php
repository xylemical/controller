<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Xylemical\Account\AccountInterface;

/**
 * A generic route.
 */
class Route implements RouteInterface {

  /**
   * The name.
   *
   * @var string
   */
  protected string $name;

  /**
   * The route arguments.
   *
   * @var array
   */
  protected array $arguments;

  /**
   * The request.
   *
   * @var \Psr\Http\Message\ServerRequestInterface
   */
  protected ServerRequestInterface $request;

  /**
   * The context.
   *
   * @var \Xylemical\Controller\ContextInterface
   */
  protected ContextInterface $context;

  /**
   * The account.
   *
   * @var \Xylemical\Account\AccountInterface|null
   */
  protected ?AccountInterface $account = NULL;

  /**
   * Route constructor.
   *
   * @param string $name
   *   The name.
   * @param array $arguments
   *   The arguments.
   * @param \Psr\Http\Message\ServerRequestInterface $request
   *   The request.
   * @param \Xylemical\Controller\ContextInterface $context
   *   The context.
   */
  public function __construct(string $name, array $arguments, ServerRequestInterface $request, ContextInterface $context) {
    $this->name = $name;
    $this->arguments = $arguments;
    $this->request = $request;
    $this->context = $context;
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getArguments(): array {
    return $this->arguments;
  }

  /**
   * {@inheritdoc}
   */
  public function getRequest(): ServerRequestInterface {
    return $this->request;
  }

  /**
   * {@inheritdoc}
   */
  public function setRequest(ServerRequestInterface $request): static {
    $this->request = $request;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContext(): ContextInterface {
    return $this->context;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccount(): ?AccountInterface {
    return $this->account;
  }

  /**
   * {@inheritdoc}
   */
  public function setAccount(?AccountInterface $account): static {
    $this->account = $account;
    return $this;
  }

}
