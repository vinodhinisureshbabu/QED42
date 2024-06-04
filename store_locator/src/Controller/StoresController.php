<?php

declare(strict_types=1);

namespace Drupal\store_locator\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\store_locator\Services\StoresProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Component\Utility\Crypt;

/**
 * Returns responses for Store locator routes.
 */
 class StoresController extends ControllerBase {
/**
 * EntityTypeManagerInterface $entityTypeManager
 */
  protected  $entityTypeManager;

  /**
 * StoresProvider $storesProvider
 */
protected  $storesProvider;


  /**
   * The controller constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, StoresProvider $storesProvider) {
   $this->entityTypeManager =  $entityTypeManager;
   $this->storesProvider =  $storesProvider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('entity_type.manager'),
      $container->get('store_locator.stores_provider'),
    );
  }

  /**
   * Return the store nodes based on latitiude and longtitude.
   *
   * @param object $request
   *   A object of Symfony\Component\HttpFoundation\Request.
   *
   * @return $response
   *   A object of CacheableJsonResponse
   */
  public function page(Request $request) {
    // Get data from the request
    $user_ip = $request->getClientIp();
    $latitude = $request->request->get('latitude');
    $longitude = $request->request->get('longitude');
    $locations = [];
    if(empty($latitude) || empty($longitude)) {
    }
    else{
      //get the stores
      $locations = $this->storesProvider->getStoresByCoordinates($latitude, $longitude);
    }
     // Create a cacheable response
     $response = new CacheableJsonResponse($locations);
    
     // Add a cache context for the user IP address
     $response->addCacheableDependency($this->getCacheContexts($user_ip));

     return $response;
    //return new JsonResponse($locations);
  }
  /**
   * Returns cache contexts for the response.
  */
  protected function getCacheContexts($user_ip) {
    //cache context depends on the IP address
    $cache_contexts = [
      'cache' => [
        'contexts' => [
          'ip_address',
        ],
      ],
    ];

    // Create a unique cache context based on the hashed IP address
    $hashed_ip = Crypt::hashBase64($user_ip);
    $cache_contexts['cache']['contexts'][] = "ip:$hashed_ip";
    return $cache_contexts;
  }
}
