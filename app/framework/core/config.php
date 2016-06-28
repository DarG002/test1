<?php
 class Config
{
    static private $data = array();
 
    public static function read($cnf, $path = false) {
        $path = $path ? $path : 'framework/';

        // этот конфиг уже парсился?
        if (isset(self::$data[$cnf])) {
            return self::$data[$cnf];
        } else {
            // пробуем парсить
            if (is_file($path . $cnf)) {
                include_once($path . $cnf);
                if (isset($data) && is_array($data)) {
                        self::$data[$cnf] = $data;
                } else {
                    print_r('Data config file is incorrect: <b>' . $path . $cnf . '</b>');
                }

                return self::$data[$cnf];
            } else {
                print_r('There is no data file\nMissed: <b>' . $path . $cnf . '</b>');
            }
        }
    }
}