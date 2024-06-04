<?php

namespace Drupal\store_locator\Services;

use Drupal\node\Entity\Node;


/**
 * Defines the GeocoderConsumerService service, for return parse GeoJson.
 */
class StoresProvider {

 /**
   * Return the near by store nodes of radius 500km.
   *
   * @param string $latitude
   *   The latitude coordinate of the location.
   *
   * @param string $longtitude
   *   The longtitude coordinate  of the location.
   *
   * @return array
   *   An array of matching stores.
   */
    
    public function getStoresByCoordinates($latitude = NULL, $longtitude = NULL){
        $locations = [];
        // Query to find nodes of type 'store' within the given radius
        //@todo - Add the calculate distance expression in the query table itself
        $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'stores');
        $query->accessCheck(TRUE);
        $nids = $query->execute();
        $stores = [];
        foreach ($nids as $nid) {
            $node = Node::load($nid);
            $store_latitude = $node->field_coordinates->lat;
            $store_longitude = $node->field_coordinates->lng;
            $distance = $this->calculateDistance((float) $latitude, (float) $longtitude, $store_latitude, $store_longitude);
            $radius =  500;
            //Add the stores only when the store is within 500 km radius
            if ($distance <= $radius) { 
                if(!empty($node->get('field_address')->first())){
                    $address = $node->get('field_address')->first()->getValue();
                    $address_line1 = isset($address['address_line1']) ? $address['address_line1'] : '';
                    $address_line2 = isset($address['address_line2']) ? $address['address_line2'] : '';
                    $locality = isset($address['locality']) ? $address['locality'] : '';
                    $administrative_area = isset($address['administrative_area']) ? $address['administrative_area'] : '';
                    $postal_code = isset($address['postal_code']) ? $address['postal_code'] : '';
                    $country_code = isset($address['country_code']) ? $address['country_code'] : '';
                }
                $stores[] = [
                    'nid' => $node->id(),
                    'title' => $node->getTitle(),
                    'lat' => $store_latitude,
                    'lng' => $store_longitude,
                    'address' =>  t('Store Address: <p> @address_line_1 , @address_line_2 </p>
                      <p>@locality &nbsp; @postal_code &nbsp; @country_code </p>',['@address_line_1' => $address_line1,
                     '@address_line_2' => $address_line2 , '@locality' => $locality ,'@postal_code' => $postal_code ,
                      '@country_code' => $country_code]),
                    'distance' => $distance,
                ];
            }
        }
        usort($stores, function($store_a, $store_b) {
            return $store_a['distance'] - $store_b['distance'];
        });
        return $stores;
    } 
    /**
   * Return the distance of the stores from user input location to store location.
   *
   * @param string $lat1
   *   The latitude coordinate of the user entered location.
   *
   * @param string $lon1
   *   The longtitude coordinate  of the user entered location.
   * 
   * @param string $lat2
   *   The latitude coordinate of the store location.
   *
   * @param string $lon2
   *   The longtitude coordinate  of the store location.
   *
   * @return $distance
   *  distance between the stores
   */
    protected function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // Radius of the Earth in kilometers
        $latDifference = deg2rad($lat2 - $lat1);
        $lonDifference = deg2rad($lon2 - $lon1);
        $a = sin($latDifference / 2) * sin($latDifference / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDifference / 2) * sin($lonDifference / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c; // Distance in kilometers
        return $distance;
    }

}
