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
   * Controller constructor.
   *
   * @param \Xylemical\Controller\RequesterInterface $requester
   *   The request.
   * @param \Xylemical\Controller\ResponderInterface $responder
   *   The responder.
   * @param \Xylemical\Controller\ProcessorInterface $processor
   *   The processor.
   */
  public function __construct(RequesterInterface $requester, ResponderInterface $responder, ProcessorInterface $processor) {
    $this->requester = $requester;
    $this->responder = $responder;
    $this->processor = $processor;
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
      $body = $this->requester->getBody($request);
    } catch (\Exception $e) {
      $result = Result::exception($e->getCode(), $e->getMessage());
    }

    if (!isset($result)) {
      try {
        if (!$this->processor->applies($request, $body ?? NULL)) {
          throw new \Exception('No available processor.');
        }
        $result = $this->processor->getResult($request, $body ?? NULL);
      } catch (AccessException $e) {
        $result = Result::access($e->getMessage());
      } catch (DelayedException $e) {
        $result = Result::delayed($e->getMessage());
      } catch (UnavailableException $e) {
        $result = Result::unavailable($e->getMessage());
      } catch (\Exception $e) {
        $result = Result::exception($e->getCode(), $e->getMessage());
      }
    }

    try {
      return $this->responder->getResponse($request, $result);
    } catch (\Exception $e) {
      return (new Response())->withStatus(500, $e->getMessage());
    }
  }


}