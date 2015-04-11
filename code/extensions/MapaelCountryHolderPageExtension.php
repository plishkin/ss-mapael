<?php

/**
 * Class MapaelCountryHolderPageExtension
 * @property HasManyList CountryPages()
 */

class MapaelCountryHolderPageExtension extends DataExtension {

    private static $has_many = array(
        'CountryPages' => 'MapaelCountryPage',
    );

    public function Mapael() {
        return $this->renderWith(array('Mapael'));
    }

    public function MapaelConfig() {
        return json_encode($this->MapaelConfigArray());
    }

    public function MapaelConfigArray() {
        return array('areas' => static::getAreasArray(),);
    }

    public function updateCMSFields(FieldList $fields) {
        /** @var Page|MapaelCountryHolderPageExtension $page */
        $page = $this->getOwner();
        $tab = $fields->findOrMakeTab('Root.CountryPagesTab',$page->fieldLabel('CountryPages'));
        $tab->push(GridField::create(
            'CountryPages',
            $page->fieldLabel('CountryPages'),
            $page->CountryPages(),
            GridFieldConfig_RelationEditor::create()
        ));
    }


    public function updateFieldLabels(&$labels) {
        foreach (array('db', 'has_one', 'has_many', 'many_many', 'belongs_many_many') as $type) {
            if (property_exists(__CLASS__,$type)) {
                foreach (self::${$type} as $name => $val) {
                    $labels[$name] = _t(__CLASS__.".{$type}_{$name}",FormField::name_to_label($name));
                }
            }
        }
    }

}

class MapaelCountryHolderPage_ControllerExtension extends MapaelPage_ControllerExtension {


}