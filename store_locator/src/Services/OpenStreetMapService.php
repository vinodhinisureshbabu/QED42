<?php

namespace Drupal\store_locator\Services;

use GuzzleHttp\ClientInterface;
use Drupal\Component\Serialization\Json;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;


/**
 * Defines the OpenStreetMapService service, for return coordinates and address.
 */
class OpenStreetMapService {

  protected $endPoint = "https://nominatim.openstreetmap.org/search";

  /**
   * Drupal http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Service constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The http client.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(
    ClientInterface $http_client,
  ) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
    );
  }

  /**
   * Return coordinates of matching $address.
   *
   * @param string $address
   *   The address query for search a place.
   *
   * @return array
   *   An array of matching coordinates.
   */
  public function geoLatLong($address) {
    try {
      $response = $this->httpClient->request('GET', $this->endPoint, [
        'query' => [
          'format' => 'json',
          'q' => $address
        ],
      ]);   
    } 
    catch (ClientException $e) {
      $response = $e->getResponse();
      return $response;
    }
    catch (ServerException $e) {
      $response = $e->getResponse();
      return $response;
    }
    $data = Json::decode($response->getBody());
    $coordinates = ['latitude' => $data[0]['lat'], 'longitude' => $data[0]['lon']];
    return $coordinates;
  }
  /**
   * Return address suggestions for autocomplete element.
   *
   * @param string $query
   *   The address query for search a location.
   *
   * @return array
   *   An array of matching address suggestions.
   */
  public function getAddressSuggestions($query) {
    try{
      $response = $this->httpClient->request('GET', $this->endPoint, [
        'query' => [
          'format' => 'json',
          'q' => $query
        ],
      ]);
    }
    catch (ClientException $e) {
      $response = $e->getResponse();
      return $response;
    }
    catch (ServerException $e) {
      $response = $e->getResponse();
      return $response;
    }
    $suggestions = Json::decode($response->getBody());
    return array_values($suggestions); //sort the key - avoid a script error
  }
}
