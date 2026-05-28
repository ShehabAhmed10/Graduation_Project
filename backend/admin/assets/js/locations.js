document.addEventListener('DOMContentLoaded', function () {
    // default center
    var latInput = document.getElementById('latitude');
    var lngInput = document.getElementById('longitude');
    var zoomInput = document.getElementById('zoom_level');

    var lat = parseFloat(latInput ? latInput.value : 0) || 15.3694;
    var lng = parseFloat(lngInput ? lngInput.value : 0) || 44.1910;
    var zoom = parseInt(zoomInput ? zoomInput.value : 12) || 6;

    var mapEl = document.getElementById('map');
    if (!mapEl) return;

    var map = L.map('map').setView([lat, lng], zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var marker = L.marker([lat, lng], {draggable: true}).addTo(map);

    function updateInputs(lat, lng, zoom) {
        if (latInput) latInput.value = lat.toFixed(6);
        if (lngInput) lngInput.value = lng.toFixed(6);
        if (zoomInput) zoomInput.value = map.getZoom();
    }

    marker.on('dragend', function (e) {
        var p = marker.getLatLng();
        updateInputs(p.lat, p.lng, map.getZoom());
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updateInputs(e.latlng.lat, e.latlng.lng, map.getZoom());
    });

    map.on('zoomend', function () {
        if (zoomInput) zoomInput.value = map.getZoom();
    });

    // update marker if inputs changed manually
    if (latInput && lngInput) {
        latInput.addEventListener('change', function () {
            var la = parseFloat(latInput.value) || marker.getLatLng().lat;
            var lo = parseFloat(lngInput.value) || marker.getLatLng().lng;
            marker.setLatLng([la, lo]);
            map.setView([la, lo]);
        });
        lngInput.addEventListener('change', function () {
            var la = parseFloat(latInput.value) || marker.getLatLng().lat;
            var lo = parseFloat(lngInput.value) || marker.getLatLng().lng;
            marker.setLatLng([la, lo]);
            map.setView([la, lo]);
        });
    }
});
