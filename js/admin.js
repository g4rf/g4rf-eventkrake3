/* global Leaflet */

jQuery(document).ready(function() {
    /* Datepicker */
    Eventkrake.Admin.loadDatepicker(".datepicker:visible");

    /* Ort suchen */
    /*jQuery('input[name="addressfinder"]').keydown(function(event) {
            window.clearTimeout(Eventkrake.Admin.keyTimeout);
    });
    jQuery('input[name="addressfinder"]').keyup(function() {
            var words = jQuery(this).val();
            var url = jQuery(this).data("url");
            var id = jQuery(this).data("id");
            Eventkrake.Admin.keyTimeout = window.setTimeout(function() {
                    Eventkrake.Admin.findLocation(url, id, words);
            }, 500);
    });
    /* Ort auswählen */
    /*jQuery('select[name="locationid"]').change(function() {
        Eventkrake.Admin.showLocationInfo(
            jQuery(this).data("url"),
            jQuery(this).val(),
            "#" + jQuery(this).attr("id") + "_info"
        );
    });

    /* Map für die Auswahl des Ortes */
    if(document.getElementById(Eventkrake.Admin.mapId)) {
        var lat = parseFloat(jQuery("#" + Eventkrake.Admin.latId).val());
        var lng = parseFloat(jQuery("#" + Eventkrake.Admin.lngId).val());
        if(isNaN(lat)) lat = Eventkrake.Geo.StandardLat;
        if(isNaN(lng)) lng = Eventkrake.Geo.StandardLng;

        Eventkrake.Admin.map = Leaflet.map(document.getElementById(Eventkrake.Admin.mapId));
        var layer = new Leaflet.tileLayer(Eventkrake.Map.tileUrl, {
            attribution: Eventkrake.Map.attribution,
            maxZoom: 18
        });
        Eventkrake.Admin.map.setView([lat, lng], 17);
        Eventkrake.Admin.map.addLayer(layer);

        Eventkrake.Admin.map.markers = Eventkrake.Admin.map.markers || [];
        Eventkrake.Admin.map.markers.push(
            Leaflet.marker([lat, lng]).addTo(Eventkrake.Admin.map));

        Eventkrake.Admin.map.on('click', function(e) {
            var latlng = [e.latlng.lat, e.latlng.lng];
            Eventkrake.Geo.getAddress(
                latlng,
                function(notUsed, address) {
                    Eventkrake.Admin.loadNewAddressForLocation(latlng, address);
                }
            );
        });
    }

    jQuery('.eventkrake_lookforaddress').click(function() {
        Eventkrake.Geo.getLatLng(
            jQuery("#" + Eventkrake.Admin.addressId).val(),
            Eventkrake.Admin.loadNewAddressForLocation
        );
    });

    jQuery('#' + Eventkrake.Admin.recId).click(function() {
        jQuery("#" + Eventkrake.Admin.addressId).val(
            jQuery(this).text()
        );
    });

    // Link bei Events zu "Ort bearbeiten"
    jQuery("#eventkrake_locationid_edit_location").click(function() {
        var locationId = jQuery("select[name='eventkrake_locationid']").val();
        if(locationId > 0) {
            window.location.href = jQuery(this).data("url") + locationId;
        }
        return false;
    });

    // suggested categories
    jQuery(".eventkrake-cat-suggestion").click(function() {
        var categories = jQuery("[name='eventkrake_categories']")
                .val().split(",");
        var newCategories = [];
        for(var i = 0; i < categories.length; i++) {
            categories[i] = categories[i].trim();
            if(categories[i].length > 0) newCategories.push(categories[i]);
        }
        newCategories.push(jQuery(this).text());
        jQuery("[name='eventkrake_categories']").val(newCategories.join(", "));
    });

    // add new link in meta
    jQuery(".eventkrake-add-link").click(function(e) {
        e.preventDefault();
        jQuery(".eventkrake-links-template").clone()
                .removeClass("eventkrake-links-template eventkrake-hide")
                .insertBefore(jQuery(this).parent());
    });

    // add new time on events
    jQuery(".eventkrake-add-time").click(function(e) {
        e.preventDefault();
        var dates = jQuery(".eventkrake-template.eventkrake-dates").clone()
                .removeClass("eventkrake-template")
                .insertBefore(jQuery(this).parent());
        Eventkrake.Admin.loadDatepicker(jQuery(".datepicker", dates));
    });

    // remove time on events
    jQuery("body").on("click", ".eventkrake-remove-date", function(e) {
        e.preventDefault();
        jQuery(this).parent().remove();
    });
    
    // search for select
    jQuery(".eventkrake-select-search").on("keyup", function() {
        var select = jQuery(this).next(".eventkrake-select-multiple");
        var search = jQuery(this).val().toLowerCase();
        
        if(search.length > 0) { // search something
            
            jQuery("label", select).each(function(i, label) {                
                if(jQuery(label).text().toLowerCase().indexOf(search) > -1) {
                    jQuery(label).show();
                } else {
                    jQuery(label).hide();
                }
            });
            return;
        } 
        
        // show all
        jQuery("label", select).show();
    });
});

var Eventkrake = Eventkrake || {};
Eventkrake.Admin = {
    mapId: "eventkrake_map",
    addressId: "eventkrake_address",
    recId: "eventkrake_rec",
    latId: "eventkrake_lat",
    lngId: "eventkrake_lng",

    /** @ignore */
    map: null,

    /** @ignore */
    keyTimeout: null,

    /** @ignore */
    imageId: 0,

    /** Ändere Karte, Adresstext und LatLng. */
    loadNewAddressForLocation: function(latlng, address) {
        if(latlng === false) {
            jQuery("#" + Eventkrake.Admin.recId).empty().append(address);
            return;
        }

        Eventkrake.Admin.map.panTo(latlng);
        Eventkrake.Admin.map.markers[0].setLatLng(latlng);

        jQuery("#" + Eventkrake.Admin.recId).empty().append(address);
        if(jQuery("#" + Eventkrake.Admin.addressId).val().length == 0) {
            jQuery("#" + Eventkrake.Admin.addressId).val(address);
        }

        jQuery("#" + Eventkrake.Admin.latId).val(latlng[0]);
        jQuery("#" + Eventkrake.Admin.lngId).val(latlng[1]);
    },

    /** Listet Orte anhand eines Suchstrings auf. */
    /*findLocation: function(url, id, words) {
        var params = {
            action: 'getlocations',
            location_search: words,
            limit: 100
        };
        jQuery.getJSON(url, params, function(data) {
            var sel = "#location_" + id;
            jQuery(sel).find("option").not(".fixed").remove();
            for(var key in data) {
                jQuery(sel).append("<option value='" + data[key].id + "'>" +
                    data[key].name + " (" + data[key].address + ")</option>");
            }
        });
    },*/

    /** Findet Adressen.
     */
    /*findAddress : function() {
            var id = jQuery(e).parents('.yourbash').attr('id');
            var a = '#'+id+' input[name="address[]"]';
            var lat =  '#'+id+' input[name="lat[]"]';
            var lng =  '#'+id+' input[name="lng[]"]';

            Geo.getLatLng(jQuery(a).val(), function(latlng, address) {
                    jQuery(a).val(address);
                    if(latlng === false) {
                            jQuery(lat).val('');
                            jQuery(lng).val('');
                    } else {
                            jQuery(lat).val(latlng.lat());
                            jQuery(lng).val(latlng.lng());
                    }
            });
    },*/

    /** Gibt Infos zu einem Ort in einem DIV aus
     * @param {String} url Die Webservice-URL.
     * @param {Number} locationId Die Id des Ortes.
     * @param {Object} div Das Element, wo die Daten abgelegt werden. Dazu wird
     *      jedes Element mit dem Attribut data-info="{Wert}" mit dem
     *      entsprechenden {Wert} befüllt.
     */
    /*showLocationInfo : function(url, locationId, elem) {
        var params = {
            action: 'getlocation',
            location_id: locationId
        };
        jQuery.getJSON(url, params, function(location) {
            if(typeof location !== "object") return;
            jQuery(elem).find("[data-info]").each(function() {
                var text = location[jQuery(this).data("info")];
                if(jQuery.isArray(text)) {
                    text = text.join(", ");
                }
                jQuery(this).html(text);
            });
        });
    }*/

    loadDatepicker: function(selector) {
        jQuery(selector).datepicker({
            dayNames: ["Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag",
                "Freitag", "Samstag"],
            dayNamesMin: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
            dayNamesShort: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
            monthNames: ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli",
                "August", "September", "Oktober", "November", "Dezember"],
            monthNamesShort: ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul",
                "Aug", "Sep", "Okt", "Nov", "Dez"],

            dateFormat: "DD, d. M yy",

            onSelect: function(dateText, inst)  {
                var date = jQuery.datepicker.parseDate(
                    jQuery(this).datepicker("option", "dateFormat"),
                    dateText,
                    {
                        dayNamesMin: jQuery(this).datepicker("option", "dayNamesMin"),
                        dayNamesShort: jQuery(this).datepicker("option", "dayNamesShort"),
                        dayNames: jQuery(this).datepicker("option", "dayNames"),
                        monthNamesShort: jQuery(this).datepicker("option", "monthNamesShort"),
                        monthNames: jQuery(this).datepicker("option", "monthNames")
                    }
                );

                // save date machine readable
                var machineDate = jQuery(".eventkrake-machine-date",
                                                    jQuery(this).parent());
                machineDate.val(jQuery.datepicker.formatDate("yy-mm-dd", date));
            }
        });
    }
};
