<?php

global $google_default, $smooth_dark, $ultra_light, $subtle_gray, $shades_grey, $blue_water, $blue_dark_sea, $midnight_commander;

$google_default = '[], {name: "Default Style"}';

$smooth_dark = '[ 
{ "elementType": "geometry", "stylers": [ { "color": "#212121" } ] }, 
{ "elementType": "labels.icon", "stylers": [ { "visibility": "off" } ] }, 
{ "elementType": "labels.text.fill", "stylers": [ { "color": "#757575" } ] }, 
{ "elementType": "labels.text.stroke", "stylers": [ { "color": "#212121" } ] }, 
{ "featureType": "administrative", "elementType": "geometry", "stylers": [ { "color": "#757575" } ] }, 
{ "featureType": "administrative.country", "elementType": "labels.text.fill", "stylers": [ { "color": "#9e9e9e" } ] }, 
{ "featureType": "administrative.land_parcel", "stylers": [ { "visibility": "off" } ] }, 
{ "featureType": "administrative.locality", "elementType": "labels.text.fill", "stylers": [ { "color": "#bdbdbd" } ] }, 
{ "featureType": "poi", "elementType": "labels.text.fill", "stylers": [ { "color": "#757575" } ] }, 
{ "featureType": "poi.park", "elementType": "geometry", "stylers": [ { "color": "#181818" } ] }, 
{ "featureType": "poi.park", "elementType": "labels.text.fill", "stylers": [ { "color": "#616161" } ] }, 
{ "featureType": "poi.park", "elementType": "labels.text.stroke", "stylers": [ { "color": "#1b1b1b" } ] }, 
{ "featureType": "road", "elementType": "geometry.fill", "stylers": [ { "color": "#2c2c2c" } ] }, 
{ "featureType": "road", "elementType": "labels.text.fill", "stylers": [ { "color": "#8a8a8a" } ] }, 
{ "featureType": "road.arterial", "elementType": "geometry", "stylers": [ { "color": "#373737" } ] }, 
{ "featureType": "road.highway", "elementType": "geometry", "stylers": [ { "color": "#3c3c3c" } ] }, 
{ "featureType": "road.highway.controlled_access", "elementType": "geometry", "stylers": [ { "color": "#4e4e4e" } ] }, 
{ "featureType": "road.local", "elementType": "labels.text.fill", "stylers": [ { "color": "#616161" } ] }, 
{ "featureType": "transit", "elementType": "labels.text.fill", "stylers": [ { "color": "#757575" } ] }, 
{ "featureType": "water", "elementType": "geometry", "stylers": [ { "color": "#000000" } ] }, 
{ "featureType": "water", "elementType": "labels.text.fill", "stylers": [ { "color": "#3d3d3d" } ] } 
], {name: "Smooth Dark"}';
 
$ultra_light = '[
{"featureType":"water","elementType":"geometry","stylers":[{"color":"#e9e9e9"},{"lightness":17}]},
{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#f5f5f5"},{"lightness":20}]},
{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ffffff"},{"lightness":17}]},
{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#ffffff"},{"lightness":29},{"weight":0.2}]},
{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#ffffff"},{"lightness":18}]},
{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#ffffff"},{"lightness":16}]},
{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#f5f5f5"},{"lightness":21}]},
{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#dedede"},{"lightness":21}]},
{"elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#ffffff"},{"lightness":16}]},
{"elementType":"labels.text.fill","stylers":[{"saturation":36},{"color":"#333333"},{"lightness":40}]},
{"elementType":"labels.icon","stylers":[{"visibility":"off"}]},
{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#f2f2f2"},{"lightness":19}]},
{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#fefefe"},{"lightness":20}]},
{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#fefefe"},{"lightness":17},{"weight":1.2}]}
], {name: "Ultra Light"}';

$subtle_gray = '[
{"featureType":"administrative","elementType":"all","stylers":[{"saturation":"-100"}]},
{"featureType":"administrative.province","elementType":"all","stylers":[{"visibility":"off"}]},
{"featureType":"landscape","elementType":"all","stylers":[{"saturation":-100},{"lightness":65},{"visibility":"on"}]},
{"featureType":"poi","elementType":"all","stylers":[{"saturation":-100},{"lightness":"50"},{"visibility":"simplified"}]},
{"featureType":"road","elementType":"all","stylers":[{"saturation":"-100"}]},
{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},
{"featureType":"road.arterial","elementType":"all","stylers":[{"lightness":"30"}]},
{"featureType":"road.local","elementType":"all","stylers":[{"lightness":"40"}]},
{"featureType":"transit","elementType":"all","stylers":[{"saturation":-100},{"visibility":"simplified"}]},
{"featureType":"water","elementType":"geometry","stylers":[{"hue":"#ffff00"},{"lightness":-25},{"saturation":-97}]},
{"featureType":"water","elementType":"labels","stylers":[{"lightness":-25},{"saturation":-100}]}
], {name: "Subtle Gray"}';

$shades_grey = '[
{"featureType":"all","elementType":"labels.text.fill","stylers":[{"saturation":36},{"color":"#000000"},{"lightness":40}]},
{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#000000"},{"lightness":16}]},
{"featureType":"all","elementType":"labels.icon","stylers":[{"visibility":"off"}]},
{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#000000"},{"lightness":20}]},
{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#000000"},{"lightness":17},{"weight":1.2}]},
{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":20}]},
{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":21}]},
{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#000000"},{"lightness":17}]},
{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#000000"},{"lightness":29},{"weight":0.2}]},
{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":18}]},
{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":16}]},
{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":19}]},
{"featureType":"water","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":17}]}
], {name: "Shades grey"}';

$blue_water = '[
{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},
{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},
{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},
{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},
{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},
{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},
{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},
{"featureType":"water","elementType":"all","stylers":[{"color":"#46bcec"},{"visibility":"on"}]}
], {name: "Blue Water"}';

$blue_dark_sea = '[
{"featureType":"administrative","elementType":"all","stylers":[{"visibility":"on"}]},
{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#b9c8d3"},{"visibility":"off"}]},
{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#b9c8d3"}]},
{"featureType":"administrative","elementType":"labels.text.stroke","stylers":[{"color":"#b9c8d3"},{"visibility":"on"}]},
{"featureType":"administrative","elementType":"labels.icon","stylers":[{"color":"#b9c8d3"},{"weight":"7.01"},{"visibility":"on"}]},
{"featureType":"landscape","elementType":"all","stylers":[{"color":"#7c98ab"},{"visibility":"on"}]},
{"featureType":"landscape.man_made","elementType":"all","stylers":[{"color":"#0277cc"},{"visibility":"off"}]},
{"featureType":"landscape.natural.landcover","elementType":"all","stylers":[{"color":"#b9c8d3"},{"visibility":"off"}]},
{"featureType":"landscape.natural.terrain","elementType":"all","stylers":[{"visibility":"on"},{"color":"#b9c8d3"}]},
{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},
{"featureType":"poi.attraction","elementType":"all","stylers":[{"color":"#ff0000"}]},
{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},
{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},
{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},
{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"},{"gamma":"1.00"},{"weight":"1.00"}]},
{"featureType":"water","elementType":"all","stylers":[{"color":"#15284b"},{"visibility":"on"}]}
], {name: "Blue Dark Sea"}';

$midnight_commander = '[
{"featureType":"all","elementType":"labels.text.fill","stylers":[{"color":"#ffffff"}]},
{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"color":"#000000"},{"lightness":13}]},
{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},
{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#144b53"},{"lightness":14},{"weight":1.4}]},
{"featureType":"landscape","elementType":"all","stylers":[{"color":"#08304b"}]},
{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#0c4152"},{"lightness":5}]},
{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},
{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#0b434f"},{"lightness":25}]},
{"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},
{"featureType":"road.arterial","elementType":"geometry.stroke","stylers":[{"color":"#0b3d51"},{"lightness":16}]},
{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#000000"}]},
{"featureType":"transit","elementType":"all","stylers":[{"color":"#146474"}]},
{"featureType":"water","elementType":"all","stylers":[{"color":"#021019"}]}
], {name: "Midnight Commander"}';

?>