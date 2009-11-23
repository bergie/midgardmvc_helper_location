if (navigator.geolocation)
{
    navigator.geolocation.getCurrentPosition(org_routamc_positioning_update_location);
}

function org_routamc_positioning_update_location(location)
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
