    function initialize() {
	var options = {
        /*types: ['(cities)'],*/
    componentRestrictions: {
        country: document.getElementById('register_country').value
    }
};
	
        var input = document.getElementById('geo_register');
        var autocomplete = new google.maps.places.Autocomplete(input,options);
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
			document.getElementById('geo_place_id').value = place.place_id;
        });
    }
    google.maps.event.addDomListener(window, 'load', initialize); 

 $("#register_country").change(function () {
        initialize();
    });

$('#geo_register').keydown(function (e) {
  if (e.which == 13 && $('.pac-container:visible').length) return false;
});
