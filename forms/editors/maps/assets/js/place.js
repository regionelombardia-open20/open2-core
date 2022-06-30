function initMaps(){
    var maps = document.getElementsByClassName("map");
    if(maps.length){
        for(var i = 0; i < maps.length; i++)
        {
            var attribute = maps[i].dataset.attribute;
            var function_name = "initMap_"+attribute;
            window[function_name]();

            var input = window["input_"+attribute];
            google.maps.event.addDomListener(input, 'keydown', function(event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                }
            });
        }
    }
}

function updateMarkerByPlaceId(placeId, attribute){
    var geocoder = window["geocoder_"+attribute];
    geocoder.geocode({'placeId': placeId}, function(results, status) {
        if (status === 'OK') {
            if (results[0]) {
                //update the marker
                updateMarkerInfo(attribute, results[0].place_id, results[0].geometry.location);
                updateInfoWindow(attribute, results[0].formatted_address);
            }
        }
    });
}

function resetPlaceWidget(attribute){
    var marker = window["marker_"+attribute];
    var input_place = document.getElementById('place-id-'+attribute);

    input_place.value = "";
    marker.setMap(null);
}

function updateMarkerInfo(attribute, place_id, location){
    var map = window["map_"+attribute];
    var marker = window["marker_"+attribute];

    map.setZoom(17);
    map.setCenter(location);
    marker.setPlace({
        placeId: place_id,
        location: location
    });
}

function updateInfoWindow(attribute, address){
    var infowindow = window["infowindow_"+attribute];
    var map = window["map_"+attribute];
    var marker = window["marker_"+attribute];

    infowindow.setContent(address+"&nbsp;&nbsp;<button type='button' class='btn btn-primary' onclick='resetPlaceWidget(\""+attribute+"\")'>Rimuovi</button>");
    infowindow.open(map, marker);
}

function addCurrentLocationButton(attribute){
    var map = window["map_"+attribute];
    var geocoder = window["geocoder_"+attribute];
    var input = window["input_"+attribute];

    var controlDiv = document.createElement('div');

    var firstChild = document.createElement('button');
    firstChild.style.backgroundColor = '#fff';
    firstChild.style.border = 'none';
    firstChild.style.outline = 'none';
    firstChild.style.width = '28px';
    firstChild.style.height = '28px';
    firstChild.style.borderRadius = '2px';
    firstChild.style.boxShadow = '0 1px 4px rgba(0,0,0,0.3)';
    firstChild.style.cursor = 'pointer';
    firstChild.style.marginRight = '10px';
    firstChild.style.padding = '0px';
    firstChild.title = 'Your Location';
    firstChild.type = 'button';
    controlDiv.appendChild(firstChild);

    var secondChild = document.createElement('div');
    secondChild.style.margin = '5px';
    secondChild.style.width = '18px';
    secondChild.style.height = '18px';
    secondChild.style.backgroundImage = 'url(https://maps.gstatic.com/tactile/mylocation/mylocation-sprite-1x.png)';
    secondChild.style.backgroundSize = '180px 18px';
    secondChild.style.backgroundPosition = '0px 0px';
    secondChild.style.backgroundRepeat = 'no-repeat';
    secondChild.id = 'current_location_img';
    firstChild.appendChild(secondChild);

    firstChild.addEventListener('click', function() {
        if(navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                input.value = "";
                var latlng = {lat: parseFloat(position.coords.latitude), lng: parseFloat(position.coords.longitude)};
                geocoder.geocode({'location': latlng}, function(results, status) {
                    if (status === 'OK') {
                        if (results[0]) {
                            //update the marker
                            updateMarkerInfo(attribute, results[0].place_id, results[0].geometry.location);
                            updateInfoWindow(attribute, results[0].formatted_address);
                        }
                    }
                });
            });
        }
        else{
            $('#current_location_img').css('background-position', '0px 0px');
        }
    });

    controlDiv.index = 1;
    map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(controlDiv);
}