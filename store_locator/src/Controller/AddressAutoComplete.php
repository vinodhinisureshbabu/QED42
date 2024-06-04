<?php

namespace Drupal\store_locator\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\store_locator\Services\OpenStreetMapService;
use Symfony\Component\DependencyInjection\ContainerInterface;



class AddressAutoComplete extends ControllerBase {

      /**
 * StoresProvider $storesProvider
 */
protected  $streetMapService;


/**
 * The controller constructor.
 */
public function __construct(OpenStreetMapService $streetMapService) {
 $this->streetMapService =  $streetMapService;
}

/**
 * {@inheritdoc}
 */
public static function create(ContainerInterface $container): self {
  return new self(
    $container->get('store_locator.geocodes'),
  );
}

  /**
   * Handles the address autocomplete request.
   * @param object $request
   *   A object of Symfony\Component\HttpFoundation\Request.
   *
   * @return $response
   *   A object of JsonResponse
   */
  public function handleAutocomplete(Request $request) {
    $results = [];
    $query = $request->query->get('q');
    if (!$query) {
      return new JsonResponse($results);
    }
    $address =  $this->streetMapService->getAddressSuggestions($query);
    // format the address 
    foreach ($address as $item) {
      $results[] = ['value' => $item['display_name']];
    }
    return new JsonResponse($results);
  }
}