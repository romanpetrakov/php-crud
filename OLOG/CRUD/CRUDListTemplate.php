<?php

namespace OLOG\CRUD;

class CRUDListTemplate
{
    const KEY_LIST_COLUMNS = 'LIST_COLUMNS';
    const OPERATION_ADD_MODEL = 'OPERATION_ADD_MODEL';

    static protected function addModelOperation($model_class_name, $element_config_arr, $context_arr){
        \OLOG\Model\Helper::exceptionIfClassNotImplementsInterface($model_class_name, \OLOG\Model\InterfaceSave::class);

        $new_prop_values_arr = array();
        $reflect = new \ReflectionClass($model_class_name);

        foreach ($reflect->getProperties() as $prop_obj) {
            if (!$prop_obj->isStatic()) {
                $prop_name = $prop_obj->getName();
                if (array_key_exists($prop_name, $_POST)) {
                    // Проверка на заполнение обязательных полей делается на уровне СУБД, через нот нулл в таблице
                    $new_prop_values_arr[$prop_name] = $_POST[$prop_name];
                }
            }
        }

        //
        // сохранение
        //

        $obj = new $model_class_name;

        $obj = FieldsAccess::setObjectFieldsFromArray($obj, $new_prop_values_arr);
        $obj->save();

        // TODO: keep get form
        \OLOG\Redirects::redirectToSelfNoGetForm();
    }

    static public function render($model_class_name, $element_config_arr, $context_arr = array())
    {
        Operations::matchOperation(self::OPERATION_ADD_MODEL, function() use($model_class_name, $element_config_arr, $context_arr) {
            self::addModelOperation($model_class_name, $element_config_arr, $context_arr);
        });

        //
        // готовим список ID объектов для вывода
        //

        $filter = '';
        /* TODO
        if (isset($_GET['filter'])){
            $filter = $_GET['filter'];
        }
        */

        $objs_ids_arr = self::getObjIdsArrForClassName($model_class_name, $context_arr, $filter);

        //
        // готовим список полей, которые будем выводить в таблицу
        //

        // TODO
        /*
        $crud_table_fields_arr = array();

        if (property_exists($model_class_name, 'crud_table_fields_arr') && (count($model_class_name::$crud_table_fields_arr) > 0)) {
            foreach ($props_arr as $delta => $property_obj) {
                if (!in_array($property_obj->getName(), $model_class_name::$crud_table_fields_arr)) {
                    unset($props_arr[$delta]);
                }
            }
        }
        */

        //
        // вывод таблицы
        //

        // LIST TOOLBAR
        echo '<div>';

        $create_form_config_arr = CRUDConfigReader::getOptionalSubkey($element_config_arr, 'CREATE_FORM', []);

        if (!empty($create_form_config_arr)) {
            // TODO: get form may be needed here? think over
            echo '<form id="form" class="form-horizontal" role="form" method="post" action="' . \OLOG\Url::getCurrentUrlNoGetForm() . '">';
            echo Operations::operationCodeHiddenField(self::OPERATION_ADD_MODEL);
            echo '<input type="hidden" name="_class_name" value="' . Sanitize::sanitizeAttrValue($model_class_name) . '">';

            // создаем объект со значениями по умолчанию, который уйдет в форму создания. этот объект сохраняться не будет.
            $context_obj = new $model_class_name();
            // заполняем поля объекта со значениями по умолчанию из контекста
            FieldsAccess::setObjectFieldsFromArray($context_obj, $context_arr);

            // TODO: форма должна быть в попапе?
            // русуем форму создания, поля формы заполняются из объекта со значениями по умолчанию
            $elements_arr = CRUDConfigReader::getRequiredSubkey($create_form_config_arr, 'ELEMENTS');
            foreach ($elements_arr as $element_key => $element_config) {
                CRUDElements::renderFormElement($element_config, $context_obj);
            }

            echo '<div class="row">';
            echo '<div class="col-sm-8 col-sm-offset-4">';
            echo '<button style="width: 100%" type="submit" class="btn btn-primary">Создать</button>';
            echo '</div>';
            echo '</div>';

            echo '</form>';
        }

        echo '</div>';

        /* TODO
        if (isset($model_class_name::$crud_model_title_field)) {
            if (isset($model_class_name::$crud_allow_search)) {
                if ($model_class_name::$crud_allow_search == true) {
                    echo '<div class="pull-right" style="margin-top: 25px;"><form action="' . \Sportbox\Helpers::uri_no_getform() . '"><input name="filter" value="' . $filter . '"><input type="submit" value="искать"></form></div>';
                }
            }
        }
        */

        if (count($objs_ids_arr) == 0) {
            return;
        }

        $columns_config_arr = CRUDConfigReader::getRequiredSubkey($element_config_arr, self::KEY_LIST_COLUMNS);

        echo '<table class="table table-hover">';
        echo '<thead>';
        echo '<tr>';

            foreach ($columns_config_arr as $column_config) {
                $col_title = CRUDConfigReader::getOptionalSubkey($column_config, 'COLUMN_TITLE', '');
                echo '<th>' . Sanitize::sanitizeTagContent($col_title) . '</th>';
            }

        echo '<th>';
        echo '</th>';
        echo '</tr>';
        echo '</thead>';

        echo '<tbody>';

        foreach ($objs_ids_arr as $obj_id) {
            $obj_obj = ObjectLoader::createAndLoadObject($model_class_name, $obj_id);

            echo '<tr>';

            foreach ($columns_config_arr as $column_config){
                echo '<td>';

                $widget_config_arr = CRUDConfigReader::getRequiredSubkey($column_config, 'WIDGET');
                echo Widgets::renderListWidget($widget_config_arr, $obj_obj);

                echo '</td>';

            }

            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        echo Pager::renderPager(count($objs_ids_arr));
    }

    /**
     * Возвращает одну страницу списка объектов указанного класса.
     * Сортировка: TODO.
     * Фильтры: массив $context_arr.
     * Как определяется страница: см. Pager.
     * @param $model_class_name Имя класса модели
     * @param $context_arr array Массив пар "имя поля" - "значение поля"
     * @return array Массив идентикаторов объектов.
     */
    static public function getObjIdsArrForClassName($model_class_name, $context_arr, $title_filter = '')
    {
        $page_size = 100;
        $start = 0;

        // TODO: check interfaceLoad

        $page_size = Pager::getPageSize();
        $start = Pager::getPageOffset();

        $db_table_name = $model_class_name::DB_TABLE_NAME;
        $db_id = $model_class_name::DB_ID;

        $db_id_field_name = FieldsAccess::getIdFieldName($model_class_name);

        // selecting ids by params from context
        $query_param_values_arr = array();

        // TODO
        // внести в контекст кроме имени поля и значения еще и оператор, чтобы можно было делать поиск лайком через
        // контекст, а не отдельный параметр

        $where = ' 1 = 1 ';
        foreach ($context_arr as $column_name => $value) {
            // чистим имя поля, возможно пришедшее из запроса
            $column_name = preg_replace("/[^a-zA-Z0-9_]+/", "", $column_name);

            $where .= ' and ' . $column_name . ' = ?';
            $query_param_values_arr[] = $value;
        }

        /* TODO
        if (isset($model_class_name::$crud_model_title_field)){
            $title_field_name = $model_class_name::$crud_model_title_field;
            if ($title_filter != ''){
                $where .= ' and ' . $title_field_name . ' like ?';
                $query_param_values_arr[] = '%' . $title_filter . '%';
            }
        }
        */

        $order_field_name = $db_id_field_name;

        $obj_ids_arr = \OLOG\DB\DBWrapper::readColumn(
            $db_id,
            "select " . $db_id_field_name . " from " . $db_table_name . ' where ' . $where . ' order by ' . $order_field_name . ' desc limit ' . intval($page_size) . ' offset ' . intval($start),
            $query_param_values_arr
        );

        return $obj_ids_arr;

    }
}