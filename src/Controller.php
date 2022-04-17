<?php

namespace Xylemical\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Xylemical\Controller\Exception\AccessException;
use Xylemical\Controller\Exception\DelayedException;
use Xylemical\Controller\Exception\UnavailableException;

/**
 * Generic controller behaviour.
 */
class Controller {

  /**
   * The requester.
   *
   * @var \Xylemical\Controller\RequesterInterface
   */
  protected RequesterInterface $requester;

  /**
   * The processor.
   *
   * @var \Xylemical\Controller\ProcessorInterface
   */
  protected ProcessorInterface $processor;

  /**
   * The responder.
   *
   * @var \Xylemical\Controller\ResponderInterface
   */
  protected ResponderInterface $responder;

  /**
   * The context factory.
   *
   * @var \Xylemical\Controller\ContextFactoryInterface
   */
  protected ContextFactoryInterface $factory;

  /**
   * Controller constructor.
   *
   * @param \Xylemical\Controller\RequesterInterface $requester
   *   The request.
   * @param \Xylemical\Controller\ResponderInterface $responder
   *   The responder.
   * @param \Xylemical\Controller\ProcessorInterface $processor
   *   The processor.
   * @param \Xylemical\Controller\ContextFactoryInterface $factory
   *   The context factory.
   */
  public function __construct(RequesterInterface $requester, ResponderInterface $responder, ProcessorInterface $processor, ContextFactoryInterface $factory) {
    $this->requester = $requester;
    $this->responder = $responder;
    $this->processor = $processor;
    $this->factory = $factory;
  }

  /**
   * Handles the request and response.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The request.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   */
  public function handle(RequestInterface $request): ResponseInterface {
    try {
      $context = $this->factory->getContext($request);
      $body = $this->requester->getBody($request, $context);
    }
    catch (\Throwable $e) {
      $result = Result::exception($e->getCode(), $e->getMessage());
      $context = new Context();
    }

    if (!isset($result)) {
      try {
        if (!$this->processor->applies($request, $body ?? NULL, $context)) {
          throw new \Exception('No available processor.');
        }
        $result = $this->processor->getResult($request, $body ?? NULL, $context);
      }
      catch (AccessException $e) {
        $result = Result::access($e->getMessage());
      }
      catch (DelayedException $e) {
        $result = Result::delayed($e->getMessage());
      }
      catch (UnavailableException $e) {
        $result = Result::unavailable($e->getMessage());
      }
      catch (\Throwable $e) {
        $result = Result::exception($e->getCode(), $e->getMessage());
      }
    }

    try {
      if ($this->responder->applies($request, $result, $context)) {
        return $this->responder->getResponse($request, $result, $context);
      }
      throw new \Exception('The responder is unable to respond.');
    }
    catch (\Throwable $e) {
      return (new Response())->withStatus(
        $e->getCode() ?: 500,
        $e->getMessage()
      );
    }
  }

}
