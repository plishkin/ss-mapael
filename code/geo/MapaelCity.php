<?php

/**
 * Class MapaelCity
 * @property string Name
 * @property string Href
 * @property string Target
 * @property string TooltipContent
 * @property bool HidePlot
 * @property float Lat
 * @property float Lng
 * @property int CountryPageID
 * @method ManyManyList LinkedToCities()
 * @method ManyManyList LinkedFromCities()
 */
class MapaelCity extends DataObject {

    private static $db = array(
        'Name' => 'Varchar(128)',
        'Lat' => 'Varchar(16)', //-180 to 180 degrees
        'Lng' => 'Varchar(16)', //-180 to 180 degrees

        'TooltipContent' => 'Varchar(256)',
        'Href' => 'Varchar(512)',
        'Target' => 'Varchar(16)',

        'HidePlot' => 'Boolean(0)'
    );

    private static $has_one = array(
        'CountryPage' => 'MapaelCountryPage',
    );

    private static $many_many = array(
        'LinkedToCities' => 'MapaelCity',
    );

    private static $belongs_many_many = array(
        'LinkedFromCities' => 'MapaelCity',
    );

    private static $summary_fields = array(
        'Name','Lat','Lng','TooltipContent','Href','Target'
    );

    private static $searchable_fields = array(
        'Name','Lat','Lng','TooltipContent','Href'
    );

    static $many_many_extraFields = array(
        'LinkedToCities' => array(
            'LinkTooltipContent' => 'Varchar(128)',
            'LinkHref' => 'Varchar(512)',
            'LinkTarget' => 'Varchar(16)',
        ),
    );

    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);
        foreach (self::$many_many_extraFields as $fields) {
            foreach ($fields as $field => $component) {
                $labels[$field] = _t(__CLASS__.'.db_'.$field);
            }
        }
        foreach (array('TooltipContent','Href','Target') as $field) {
            $labels[$field] = _t('MapaelCountryPageExtension.db_'.$field);
        }
        return $labels;
    }


    private static $singular_name = 'City';

    private static $plural_name = 'Cities';

    public function Link() {
        if ($this->Href) return $this->Href;
        return $this->CountryPageID ? $this->CountryPage()->Link() : null;
    }

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        $fields->removeByName('LinkedFromCities');

        $grid = new GridField(
            'LinkedToCities',
            $this->fieldLabel('LinkedToCities'),
            $this->LinkedToCities(),
            GridFieldConfig::create()
                ->addComponent(new GridFieldDetailForm())
                ->addComponent(new GridFieldButtonRow())
                ->addComponent($GridFieldAddExistingSearchButton = new GridFieldAddExistingSearchButton('buttons-before-right'))
                ->addComponent($GridFieldAddExistingAutocompleter = new GridFieldAddExistingAutocompleter('buttons-before-right'))
                ->addComponent(new GridFieldToolbarHeader())
                ->addComponent(new GridFieldTitleHeader())
                ->addComponent($comp = new GridFieldEditableColumns())
                ->addComponent(new GridFieldEditButton())
                ->addComponent(new GridFieldDeleteAction('unlinkrelation'))
                ->addComponent(new GridFieldDeleteAction())
                ->addComponent($GridFieldAddNewInlineButton = new GridFieldAddNewInlineButton())
        );

        $GridFieldAddNewInlineButton->setTitle(_t(__CLASS__.'.AddNewCity','Add new city'));
        $GridFieldAddExistingSearchButton->setTitle(_t(__CLASS__.'.SearchAndAddExistingCity','Search and add existing city'));

        $LinkTargetField = MapaelCountryPageExtension::getTargetField($this,'LinkTarget');
        $LinkTargetField->setTitle(_t(__CLASS__.'.LinkTarget','Link target'));

        $field_names = array_keys(array_merge($this->summaryFields(),self::$many_many_extraFields['LinkedToCities']));
        $arr = array();
        foreach ($field_names as $name) {
            preg_match('/(TooltipContent|Href|Target)$/',$name,$matches);
            $title = $this->fieldLabel($name);
            $arr[$name] = $title;
            if ($matches) {
                $arr[$name] = array(
                   'title' => $title,
                    'callback' => function($record, $column, $grid) {
                        preg_match('/(TooltipContent|Href|Target)$/',$column,$matches);
                        return MapaelCountryPageExtension::getAttrField($record,$matches[1],$column);
                    }
                );
            }
        }
        $comp->setDisplayFields($arr);

        $fields->replaceField('LinkedToCities',$grid);

        $fields->replaceField('Target', MapaelCountryPageExtension::getTargetField($this));
        $fields->replaceField('Href', MapaelCountryPageExtension::getHrefField($this));
        $fields->replaceField('TooltipContent', MapaelCountryPageExtension::getTooltipContentField($this));

        return $fields;
    }


}
