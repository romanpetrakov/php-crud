<?php

namespace OLOG\CRUD;

use OLOG\Sanitize;

class CRUDFormWidgetRadios implements InterfaceCRUDFormWidget
{
    protected $field_name;
    protected $options_arr;

    public function __construct($field_name, $options_arr)
    {
        $this->setFieldName($field_name);
        $this->setOptionsArr($options_arr);
    }

    /*
<label class="radio-inline">
  <input type="radio" name="inlineRadioOptions" id="inlineRadio1" value="option1"> 1
</label>
<label class="radio-inline">
  <input type="radio" name="inlineRadioOptions" id="inlineRadio2" value="option2"> 2
</label>
<label class="radio-inline">
  <input type="radio" name="inlineRadioOptions" id="inlineRadio3" value="option3"> 3
</label>
     */
    public function html($obj)
    {
        $field_name = $this->getFieldName();
        $field_value = CRUDFieldsAccess::getObjectFieldValue($obj, $field_name);

        //$options = '<option></option>';
        $options = '';

        $options_arr = $this->getOptionsArr();

        foreach($options_arr as $value => $title)
        {
            $selected_html_attr = '';
            if ($field_value == $value) {
                $selected_html_attr = ' checked ';
            }

            $options .= '<label class="radio-inline"><input type="radio" name="' . Sanitize::sanitizeAttrValue($field_name) . '" value="' .  Sanitize::sanitizeAttrValue($value) . '" ' . $selected_html_attr . ' > ' . $title . '</label>';
        }

        //return '<select name="' . $field_name . '" class="form-control">' . $options . '</select>';
        return $options;
    }

    /**
     * @return mixed
     */
    public function getFieldName()
    {
        return $this->field_name;
    }

    /**
     * @param mixed $field_name
     */
    public function setFieldName($field_name)
    {
        $this->field_name = $field_name;
    }

    /**
     * @return mixed
     */
    public function getOptionsArr()
    {
        return $this->options_arr;
    }

    /**
     * @param mixed $options_arr
     */
    public function setOptionsArr($options_arr)
    {
        $this->options_arr = $options_arr;
    }


}