<?php

class MapaelPage_ControllerExtension extends \Extension {

    private static $allowed_actions = array(
        'MapaelMapConfig',
    );

    public function onAfterInit() {
        /** @var Page_Controller $controller */
        $controller = $this->getOwner();

        Requirements::javascript(SS_MAPAEL_FOLDER.'/dist/js.min.js');

        foreach (array(
                     '/javascript/mapael.min.js',
                     '/js/mapael.min.js',
                     '/javascript/mapael.js',
                     '/js/mapael.js'
                 ) as $path) {

            $candidate = $controller->ThemeDir() . $path;
            if (is_file(Director::baseFolder() . '/' . $candidate)) {
                Requirements::javascript($candidate);
                break;
            }
        }

        $css_file = $controller->ThemeDir() . '/css/mapael.css';
        if (!is_file($css_file)) $css_file = SS_MAPAEL_FOLDER . '/dist/css.css';
        Requirements::css($css_file);

    }

    public function MapaelMap() {
        /** @var MapaelMap $mapael */
        $mapael = MapaelMap::create();
        $mapael->MapaelMapConfig = $this->MapaelMapConfig();
        return $mapael->renderWith('MapaelMap');
    }

    public function MapaelMapConfig() {
        $cfg = MapaelMap::MapaelConfigArray();
        $holder = null;
        $selected_country_page = null;
        $page = $this->getMapaelPage();
        if (strstr(MapaelMap::getAllowedCountries(), $page->CountryCode)) {
            $holder = $page->CountryHolderPage();
            $selected_country_page = $page;
        } else {
            $holder = $page;
        }

        $default_config = json_decode(file_get_contents(SS_MAPAEL_FOLDER_PATH . '/json/default_config.json'),true);

        if (!isset($cfg['areas'])) $cfg['areas'] = array();
        foreach ($holder->CountryPages() as $country_page) {
            /** @var Page|MapaelCountryPageExtension $country_page */
            if ($country_page->canView()) {
                $cfg['areas'][$country_page->CountryCode]['attrs'] = $default_config['map']['hasLinkArea']['attrs'];
                if ($selected_country_page && $selected_country_page->ID == $country_page->ID) {
                    $cfg['areas'][$country_page->CountryCode]['attrs'] = array_merge(
                        $cfg['areas'][$country_page->CountryCode]['attrs'],
                        $default_config['map']['selectedArea']['attrs']
                    );
                }
                $cfg['areas'][$country_page->CountryCode]['href'] = $country_page->Link();
                if ($country_page->Target) {
                    $cfg['areas'][$country_page->CountryCode]['target'] = $country_page->Target;
                }
                foreach ($country_page->MapaelCities() as $city) {
                    /** @var MapaelCity $city */
                    $cid = 'city_' . $city->ID;
                    $cfg['plots'][$cid]['latitude'] = $city->Lat;
                    $cfg['plots'][$cid]['longitude'] = $city->Lng;
                    $cfg['plots'][$cid]['href'] = $city->Link();
                    if ($city->Target) $cfg['plots'][$cid]['target'] = $city->Target;
                    $cfg['plots'][$cid]['tooltip']['content'] = $city->Name;
                    if ($city->HidePlot) {
                        $cfg['plots'][$cid]['size'] = 0;
                        $cfg['plots'][$cid]['attrs']['stroke-width'] = 0;
                    }
                    foreach ($city->LinkedToCities() as $linkedCity) {
                        $lcid = 'city_' . $linkedCity->ID;
                        /** @var MapaelCity $linkedCity */
                        $lc_link = $cid . '_' . $lcid;
                        $cfg['links'][$lc_link] = array(
                            'factor' => "-0.3",
                            'between' => array($cid, $lcid),
                            'attrs' => array("stroke-width" => 2),
                            'tooltip' => array('content' => $linkedCity->LinkTooltipContent)
                        );
                        if ($linkedCity->LinkHref) {
                            $cfg['links'][$lc_link]['href'] = $linkedCity->LinkHref;
                            if ($linkedCity->LinkTarget) {
                                $cfg['links'][$lc_link]['target'] = $linkedCity->LinkTarget;
                            }
                        }
                    }

                }

            }
        }

        return json_encode($cfg);
    }

    public function AlternativeContent() {

    }

    private function getMapaelPage() {
        /** @var Page_Controller $controller */
        $controller = $this->getOwner();
        /** @var Page|MapaelCountryHolderPageExtension|MapaelCountryPageExtension $page */
        $page = $controller->data();
        return $page;
    }

}