<?php

declare(strict_types=1);

namespace Xylemical\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Xylemical\Account\AccountInterface;

/**
 * Provide the route.
 */
interface RouteInterface {

  /**
   * The route information.
   *
   * @return string
   *   The name.
   */
  public function getName(): string;

  /**
   * Get the route arguments.
   *
   * @return string[]
   *   The arguments.
   */
  public function getArguments(): array;

  /**
   * Get the request for the route.
   *
   * @return \Psr\Http\Message\ServerRequestInterface
   *   The request.
   */
  public function getRequest(): ServerRequestInterface;

  /**
   * Set the request for the route.
   *
   * @param \Psr\Http\Message\ServerRequestInterface $request
   *   The request.
   *
   * @return $this
   */
  public function setRequest(ServerRequestInterface $request): static;

  /**
   * Get the context for the route.
   *
   * @return \Xylemical\Controller\ContextInterface
   *   The context.
   */
  public function getContext(): ContextInterface;

  /**
   * Get the account for the route.
   *
   * @return \Xylemical\Account\AccountInterface|null
   *   The account.
   */
  public function getAccount(): ?AccountInterface;

  /**
   * Set the account for the route.
   *
   * @param \Xylemical\Account\AccountInterface|null $account
   *   The account.
   *
   * @return $this
   */
  public function setAccount(?AccountInterface $account): static;

}
