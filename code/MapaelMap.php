<?php

class MapaelMap extends ViewableData
{

    public $MapaelMapConfig = null;

    private static $default_cfg = array(
        "map" => array(
            "name" => "world_countries",
            "zoom" => array("enabled" => true,"maxLevel" => 20),
        )
    );


    private static $allowed_countries = 'AE,AF,AL,AM,AO,AR,AT,AU,AZ,BA,BD,BE,BF,BG,BI,BJ,BN,BO,BR,BS,BT,BW,BY,BZ,CA,CD,CF,CG,CH,CI,CL,CM,CN,CO,CR,CU,CY,CZ,DE,DJ,DK,DO,DZ,EC,EE,EG,ER,ES,ET,FI,FJ,FK,FR,GA,GB,GE,GH,GL,GM,GN,GQ,GR,GT,GW,GY,HN,HR,HT,HU,ID,IE,IL,IN,IQ,IR,IS,IT,JM,JO,JP,KE,KG,KH,KO,KP,KR,KW,KZ,LA,LB,LK,LR,LS,LT,LU,LV,LY,MA,MD,ME,MG,MK,ML,MM,MN,MR,MW,MX,MY,MZ,NA,NC,NC,NE,NG,NI,NL,NO,NP,NZ,OM,PA,PE,PG,PH,PK,PL,PR,PS,PT,PY,QA,RO,RS,RU,RW,SA,SB,SD,SE,SI,SK,SL,SN,SO,SO,SR,SS,SV,SY,SZ,TD,TF,TG,TH,TJ,TL,TM,TN,TR,TT,TW,TZ,UA,UG,US,UY,UZ,VE,VN,VU,WS,YE,ZA,ZM,ZW';

    public static function getAllowedCountries()
    {
        return self::$allowed_countries;
    }

    private static $allowed_countries_array = array();

    public static function getAllowedCountriesArray()
    {
        if (!self::$allowed_countries_array) {
            foreach (explode(',', self::$allowed_countries) as $cc) {
                self::$allowed_countries_array[$cc] = $cc;
            }
        }
        return self::$allowed_countries_array;
    }

    private static $_mcache = array();

    public static function getAreasArray($locale=null)
    {
        $locale = $locale ? $locale : \i18n::get_locale();
        $name = 'areas_array_'.$locale;
        if (isset(self::$_mcache[$name])) {
            return self::$_mcache[$name];
        }
        if (!static::cache_exists($name) || \Director::isDev()) {
            static::flushCountriesCache($locale);
        }
        return self::$_mcache[$name] = static::get_cached($name);
    }

    private static $newTerritory = array(
        'SS' => array(
            'en_US' => 'South Sudan',
            'ru_RU' => 'Южный Судан',
            'uk_UA' => 'Південний Судан',
        ),
        'KO' => array(
            'en_US' => 'Kosovo',
            'ru_RU' => 'Косово',
            'uk_UA' => 'Косово',
        ),
    );

    public static function MapaelConfigArray($locale=null)
    {
        $locale = $locale ? $locale : \i18n::get_locale();
        $cfg = self::$default_cfg;
        $cfg['areas'] = static::getAreasArray($locale);
        return $cfg;
    }

    public static function flushCountriesCache($locale=null)
    {
        $locales = $locale ? array($locale) :
            (class_exists('Translatable') ? \Translatable::get_allowed_locales() : \i18n::get_locale());
        foreach ($locales as $localeItem) {
            $name = 'areas_array_'.$localeItem;
            $areas = array();
            $source = \Zend_Locale::getTranslationList('territory', $localeItem, 2);
            foreach (explode(',', self::$allowed_countries) as $countryCode) {
                $content = isset($source[$countryCode]) ? $source[$countryCode] : null;
                if (!$content) {
                    $content = isset(self::$newTerritory[$countryCode][$localeItem]) ?
                        self::$newTerritory[$countryCode][$localeItem] : null;
                    if (!$content) {
                        $content = isset(self::$newTerritory[$countryCode]['en_US']) ?
                            self::$newTerritory[$countryCode]['en_US'] : $countryCode;
                    }
                }
                $areas[$countryCode]['tooltip'] = array('content' => $content);
//                $areas[$countryCode]['text'] = array('content' => $content);
            }
            static::set_cache($name, $areas);
        }
    }

    public static function getCountryName($countryCode, $locale = null)
    {
        $locale = $locale ? $locale : \i18n::get_locale();
        $source = \Zend_Locale::getTranslationList('territory', $locale, 2);
        return isset($source[$countryCode]) ? $source[$countryCode] : false;
    }

    protected static function get_cached($name)
    {
        $file = SS_MAPAEL_TEMP_FOLDER.'/'.$name;
        return is_file($file) ? unserialize(file_get_contents($file)) : null;
    }

    protected static function set_cache($name, $value)
    {
        $folder = SS_MAPAEL_TEMP_FOLDER;
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        file_put_contents($folder.'/'.$name, serialize($value));
    }

    protected static function cache_exists($name)
    {
        return is_file(SS_MAPAEL_TEMP_FOLDER.'/'.$name);
    }
}
