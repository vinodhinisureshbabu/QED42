<?php

declare(strict_types=1);

namespace Drupal\store_locator\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\store_locator\Services\OpenStreetMapService;
use Drupal\store_locator\Services\StoresProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;


/**
 * Provides a Store Locator form.
 */
final class SearchStorebyLocation extends FormBase {

  /**
   * The OpenStreetMap service variable.
   *
   * @var \Drupal\store_locator\Services\OpenStreetMapService
   */
  protected $geoCoder;

  /**
   * 
   * @var \Drupal\store_locator\Services\StoresProvider
  */
  protected  $storesProvider;

  /**
   * {@inheritdoc}
  */
  public function __construct(OpenStreetMapService $geoCoder, StoresProvider $storesProvider) {
    $this->geoCoder = $geoCoder;
    $this->storesProvider =  $storesProvider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('store_locator.geocodes'),
      $container->get('store_locator.stores_provider'),
    );
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'store_locator_search_storeby_location';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search by postal code or city '),
      '#required' => TRUE,
      '#autocomplete_route_name' => 'store_locator.autocomplete',
      '#prefix' => '<div class="address-wrapper">',
    ];
    
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Find Stores'),
      '#ajax' => [
        'callback' => '::updateMapAndList',
        'event' => 'click',
        'wrapper' => 'map-container',
        ],
        '#suffix' => '</div>',
     // '#validate'=>['::validateAddress']
    ];
    //Placeholder for Map
    $form['location_list'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'location-list'],  
      '#markup' => '<div id="location-list-content"></div>',
      '#prefix' => '<div class="map-wrapper">',
    ];
    //Placeholder for Store Listing
    $form['map_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'map-container'],
      '#markup' => '<div id="map" style="height: 500px;"></div>',
      '#suffix' => '</div>'
    ];
    //attach the leaflet library
    $form['#attached']['library'] = ['store_locator/leaflet','store_locator/map','core/drupal.dialog.ajax'];

    return $form;
  }
  /*
  * An ajax callback to update the store map and listing. 
  */
  public function updateMapAndList(array &$form, FormStateInterface $form_state) {
    $address =  $form_state->getValue('address');
    $response = new AjaxResponse();
    if (empty($form_state->getValue('address'))) {
       $response->addCommand(new HtmlCommand('#edit-address', 'please enter'));
    }


    //get the =coordinates of the given address 
    $coordinates = $this->geoCoder->geoLatLong($address);
   
    //Invoke the javascript function to update the store map and list by passing the coordinates as a argruments
    $response->addCommand(new InvokeCommand('', 'UpdateLocationbyCoordinates', [
      $coordinates['latitude'],
      $coordinates['longitude'],
    ]));
    $form_state->setRebuild();
    return $response;
  }


  /**
   * {@inheritdoc}
   */
  public function validateAddress(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
      if (empty($form_state->getValue('address'))) {
        $form_state->setErrorByName(
          'address',
          $this->t('Enter a location to search'),
        );
      }
    // @endcode
  }

    /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
      if (empty($form_state->getValue('address'))) {
        $form_state->setErrorByName(
          'address',
          $this->t('Enter a location to search'),
        );
      }
    // @endcode
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
  }

}
