var MapaelMap = {
    init: function(){
        var cfg = jQuery.extend(true,{},
            window.MapaelMapConfig.default_config,
            MapaelMapConfig.page_config
        );

        if (typeof window.ThemedMapaelMapConfig != 'undefined') {
            cfg = jQuery.extend(true,{}, cfg, window.ThemedMapaelMapConfig);
        }
        if (cfg) $("#mapael").mapael(cfg);
    }
};

jQuery(document).ready(function() {
    MapaelMap.init();
    return true;
});


