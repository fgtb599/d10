<?php

namespace Drupal\custom_middleware\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * CustomMiddleware middleware.
 */
class CustomMiddleware implements HttpKernelInterface {

  /**
   * Constructs the object.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   The decorated kernel.
   */
  public function __construct(protected HttpKernelInterface $httpKernel) {}

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response {
    $state = \Drupal::state();
    $ip = $request->getClientIp();

    $data = $state->get('custom_middleware.request.' . $ip);

    if (!$data) {
      $data = new \StdClass();
      $data->time = time();
      $data->count = 1;
      $state->set('custom_middleware.request.' . $ip, $data);
      return $this->httpKernel->handle($request, $type, $catch);
    }

    if ($data->count >= 10) {
      if (($data->time + 60) > time()) {
        return new Response($this->t('Too Many Requests'), 426);
      }

      $data->time = time();
      $data->count = 1;
      $state->set('custom_middleware.request.' . $ip, $data);
      return $this->httpKernel->handle($request, $type, $catch);
    }

    $data->count++;
    $state->set('custom_middleware.request.' . $ip, $data);

    return $this->httpKernel->handle($request, $type, $catch);
  }

}