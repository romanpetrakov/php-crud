<?php

namespace OLOG\CRUD;

use OLOG\Sanitize;

class CRUDFormWidgetTextarea implements InterfaceCRUDFormWidget
{
    protected $field_name;
    protected $is_required;

    public function __construct($field_name, $is_required = false)
    {
        $this->setFieldName($field_name);
        $this->setIsRequired($is_required);
    }

    public function html($obj)
    {
        $field_name = $this->getFieldName();
        $field_value = CRUDFieldsAccess::getObjectFieldValue($obj, $field_name);
        $is_required_str = '';
        if ($this->is_required) {
            $is_required_str = ' required ';
        }
        return '<textarea name="' . Sanitize::sanitizeAttrValue($field_name) . '"  '. $is_required_str . ' class="form-control" rows="5">' . Sanitize::sanitizeTagContent($field_value) . '</textarea>';
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
    public function getIsRequired()
    {
        return $this->is_required;
    }

    /**
     * @param mixed $is_required
     */
    public function setIsRequired($is_required)
    {
        $this->is_required = $is_required;
    }

}