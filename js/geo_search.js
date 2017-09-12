    function initialize() {
	var options = {
        /*types: ['(cities)'],*/
    componentRestrictions: {
        country: document.getElementById('search_country').value
    }
};
	
        var input = document.getElementById('search_geo');
        var autocomplete = new google.maps.places.Autocomplete(input,options);
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
            /*document.getElementById('search_lat').value = place.geometry.location.lat();
            document.getElementById('search_lng').value = place.geometry.location.lng();*/
			document.getElementById('search_place_id').value = place.place_id;
        });
    }
    google.maps.event.addDomListener(window, 'load', initialize); 

 $("#search_country").change(function () {
        initialize();
    });

$('#search_geo').keydown(function (e) {
  if (e.which == 13 && $('.pac-container:visible').length) return false;
});
