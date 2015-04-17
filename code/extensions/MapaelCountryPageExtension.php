<?php

/**
 * Class MapaelCountryPageExtension
 * @property string CountryCode ISO 3166-1 code
 * @property string TooltipContent
 * @property string Href
 * @property string Target
 * @property int CountryHolderPageID
 * @method MapaelCountryHolderPage CountryHolderPage()
 * @method HasManyList MapaelCities()
 */

class MapaelCountryPageExtension extends DataExtension {

    private static $db = array(
        'CountryCode' => 'Varchar(2)',

        'TooltipContent' => 'Varchar(256)',
        'Href' => 'Varchar(512)',
        'Target' => 'Varchar(16)',
    );

    private static $has_one = array(
        'CountryHolderPage' => 'MapaelCountryHolderPage',
    );

    private static $has_many = array(
        'MapaelCities' => 'MapaelCity',
    );

    public function updateFieldLabels(&$labels) {
        foreach (array('db', 'has_one', 'has_many', 'many_many', 'belongs_many_many') as $type) {
            if (property_exists(__CLASS__,$type)) {
                foreach (self::${$type} as $name => $val) {
                    $labels[$name] = _t(__CLASS__.".{$type}_{$name}",FormField::name_to_label($name));
                }
            }
        }
    }

    public function updateCMSFields(FieldList $fields) {
        /** @var Page|MapaelCountryPageExtension $page */
        $page = $this->getOwner();
        $labels = $page->fieldLabels();

        $name = MapaelCountryPage::create()->i18n_singular_name();
        $tab = $fields->findOrMakeTab('Root.CountryPageTab',$name);
        $tab->push(
            new TabSet('CountryPageTabSet',
                $main = new Tab('CountryPageMain',_t('CMSMain.TabContent','Content')),
                $cities = new Tab('CountryPageCities',$page->fieldLabel('MapaelCities'))
            )
        );

        $arr = array();
        foreach (MapaelMap::getAreasArray() as $code => $attrs) {
            $arr[$code] = $attrs['tooltip']['content'];
        }
        $main->push( $dd = new CountryDropdownField(
            'CountryCode', $page->fieldLabel('CountryCode'), $arr, $page->CountryCode
        ));
        $dd->setEmptyString(_t(__CLASS__.'.CountryCodeNone','None'));

        $main->push($tf = self::getTooltipContentField($page));
        $tf->setDescription(MapaelMap::getCountryName($page->CountryCode));

        $main->push(self::getTargetField($page));
        $main->push(self::getHrefField($page));

        if ($page->CountryHolderPageID) {
            $main->push(new ReadonlyField(
                'CountryHolderPageReadonly',
                $page->fieldLabel('CountryHolderPage'),
                $page->CountryHolderPage()->Title
            ));
        }

        $cities->push(GridField::create(
            'MapaelCities',
            $page->fieldLabel('MapaelCities'),
            $page->MapaelCities(),
            GridFieldConfig_RelationEditor::create()
        ));

    }

    public static function getAttrField(DataObject $do, $attr, $property=null) {
        return forward_static_call(array(__CLASS__,'get'.$attr.'Field'), $do, $property);
    }

    public static function getTooltipContentField(DataObject $do,$property='TooltipContent') {
        /** @var MapaelCountryPageExtension|MapaelCity $do */
        return new TextField($property, _t(__CLASS__.'.db_TooltipContent',FormField::name_to_label($property)),$do->{$property});
    }

    public static function getHrefField(DataObject $do,$property='Href') {
        /** @var MapaelCountryPageExtension|MapaelCity $do */
        return new TextField($property, _t(__CLASS__.'.db_Href',FormField::name_to_label($property)),$do->{$property});
    }

    public static function getTargetField(DataObject $do,$property='Target') {
        /** @var MapaelCountryPageExtension|MapaelCity $do */
        return new DropdownField($property, _t(__CLASS__.'.db_Target','Open page or link'), array(
            '_self' => _t(__CLASS__.'.OpenInTheSameWindow','in the same window'),
            '_blank' => _t(__CLASS__.'.OpenInANewWindow','in a new window'),
        ),$do->{$property});
    }


}

class MapaelCountryPage_ControllerExtension extends MapaelPage_ControllerExtension {

}
