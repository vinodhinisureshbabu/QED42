(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.leafletMap = {
      attach: function (context, settings) {
        //Initialize th map 
        function initMap() {
          //detect the user location by using GeoLocation API
          if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
            var userLocation = {
              latitude: position.coords.latitude,
              longitude: position.coords.longitude
            };
            //set the map view with user's coordinates
            map = L.map('map').setView([userLocation.latitude, userLocation.longitude], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
              attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            var userMarker = L.marker([userLocation.latitude, userLocation.longitude]).addTo(map).bindPopup('You are here') .openPopup();
            // AJAX request to push coordinates and get the stores data from the controller
            $.ajax({
              url: Drupal.url('get-stores'),
              type: 'POST',
              data: userLocation,
              success: function (response) {
                var storeContent = $('#location-list-content');
                //On each location add marker and list content
                response.forEach(function(location) {
                  //marker
                  var marker = L.marker([location.lat, location.lng]).addTo(map)
                  .bindPopup('<p>'+location.title+'</p>');
                  marker.on('click', function() {
                    $('.store-location').removeClass('highlight');
                    $('.store-location[data-nid="' + location.nid + '"]').addClass('highlight');
                  });
                  //list content
                  var storeInfo = $('<div class="store-location"></div>')
                  .html('<p class="store-name">' + location.title + '</p>' + '<div class="store-address"><p>' + location.address +'</p></div>')
                  .attr('data-nid', location.nid)
                  .click(function() {
                    map.setView([location.lat, location.lng], 16);
                    marker.openPopup();
                  });
                  storeContent.append(storeInfo);
                });
              },
              error: function (error) {
                console.error('Error pushing data:', error);
              }
            });//ajax callback
          },function() {
              alert('Geolocation failed. Please enable location services in your browser.');
          });
          } else {
            alert('Geolocation is not supported by this browser.');
          }
        }
        //Load the map when document is ready
        document.addEventListener('DOMContentLoaded', initMap);
        //Update map on ajax call back from form. 
        // lt - latitude & lg - longtitude
        jQuery.fn.UpdateLocationbyCoordinates = function (lt, lg) {
          'use strict';
          var data = {
            latitude: lt,
            longitude: lg
          };
          var markers = [];
          $.ajax({
            url: Drupal.url('get-stores'),
            type: 'POST',
            data: data,
            success: function (response) {
              //Set the map view from the response of index [0]
              map.setView([response[0].lat, response[0].lng], 8);
              // Update location list
              var storeContent = $('#location-list-content');
              storeContent.empty();
              //On update location add marker and list content
              console.log('Data response' + response)
              response.forEach(function(location) {
                //remove the exiting layer in the map
                markers.forEach(function (marker) {
                  map.removeLayer(marker);
                });

                //add the markers for new locations
                var marker = L.marker([location.lat, location.lng]).addTo(map)
                  .bindPopup('<p>'+location.title+'</p>');
                marker.on('click', function() {
                  $('.store-location').removeClass('highlight');
                  $('.store-location[data-nid="' + location.nid + '"]').addClass('highlight');
                }); // connect the marker and store using data attributes
                var storeInfo = $('<div class="store-location"></div>')
                  .html('<p class="store-name">' + location.title + '</p>' + '<div><p>' + location.address +'</p></div>')
                  .attr('data-nid', location.nid)
                  .click(function() {
                    map.setView([location.lat, location.lng], 16);
                    marker.openPopup();
                  });
                storeContent.append(storeInfo);
              });
            },
            error: function (error) {
              console.error('Error pushing data:', error);
            }
          }); 
        }
      }
    };
})(jQuery, Drupal, drupalSettings);