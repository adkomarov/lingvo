<?php
namespace Drupal\lingvo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\lingvo\Service\DataService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

class LingvoController extends ControllerBase implements ContainerInjectionInterface {

  protected DataService $dataService;

  public function __construct(DataService $data_service) {
    $this->dataService = $data_service;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('lingvo.data_service')
    );
  }

  public function viewIcon($iconId) {
    return [
      '#markup' => $this->t($this->dataService->get_icon_file_path($iconId)),
    ];
  }

  public function build() {
    return [
      '#markup' => 'Map placeholder.',
    ];
  }

  public function getWordsForObject($objectId) {
    return new JsonResponse(['words' => []]);
  }

  public function getIconData($iconId) {
    return new JsonResponse(['icon' => []]);
  }
}
