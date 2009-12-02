if (navigator.geolocation)
{
    navigator.geolocation.getCurrentPosition(midgardmvc_helper_location_update_location);
}

function midgardmvc_helper_location_update_location(location)
{   
    jQuery.post
    (
        '/mgd:userlocation/',
        {
            latitude: location.coords.latitude,
            longitude: location.coords.longitude,
        }
    );
}
