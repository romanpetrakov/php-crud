<?php

namespace OLOG\CRUD;

class CRUDCompiler {
    /**
     * компиляция строки: разворачивание обращений к полям объектов
     * @param $str
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public static function compile($str, array $data){

        // TODO: clean and finish

        $matches = [];

        // сначала подставляем значения в самых внутренних фигурных скобках, потом которые снаружи, и так пока все скобки не будут заменены
        // поддерживается два вида выражений:
        // - {obj->field} заменяется на значение поля field объекта obj. obj - это ключ массива data, т.е. здесь можно использовать такие строки, которые передаются сюда вызывающими функциями
        // -- обычно виджеты передают объект, который показывается в виджете, с именем this
        // - {class_name.id->field} заменяется на значение поля field объекта класса class_name с идентификатором id
        while (preg_match('@{([^}{]+)}@', $str, $matches)){
            $expression = $matches[1];
            $replacement = 'UNKNOWN_EXPRESSION';

            $magic_matches = [];
            if (preg_match('@^(\w+)\->(\w+)$@', $expression, $magic_matches)){
                $obj_key_in_data = $magic_matches[1];
                $obj_field_name = $magic_matches[2];

                \OLOG\Assert::assert($data[$obj_key_in_data]);
                $replacement = CRUDFieldsAccess::getObjectFieldValue($data[$obj_key_in_data], $obj_field_name);

                if (is_null($replacement)){
                    $replacement = 'NULL'; // TODO: review?
                }
            }

            if (preg_match('@^([\w\\\\]+)\.(\w+)->(\w+)$@', $expression, $magic_matches)){
                $class_name = $magic_matches[1];
                $obj_id = $magic_matches[2];
                $obj_field_name = $magic_matches[3];

                if ($obj_id != 'NULL') { // TODO: review?
                    $obj = CRUDObjectLoader::createAndLoadObject($class_name, $obj_id);
                    $replacement = CRUDFieldsAccess::getObjectFieldValue($obj, $obj_field_name);
                } else {
                    $replacement = '';
                }
            }

            $str = preg_replace('@{([^}{]+)}@', $replacement, $str);
        }

        return $str;
    }
}