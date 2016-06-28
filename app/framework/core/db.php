<?php
class Db
{
	private static $_instance;

	//Соединения
	private static $_connections = array();

	// БД из конфига
	private static $_name;

	private static $_statements = array();
	private static $_statement = null;

	//Счетчик запросов
	private static $_nq = 0;

	//Время выполнения
	private static $_print_time = 0;

	private static $_return_result = PDO::FETCH_ASSOC; // PDO::FETCH_OBJ, PDO::FETCH_ASSOC, PDO::FETCH_BOTH, PDO::FETCH_NUM

	// Запрещаем создавать объект
    final private function __construct() {}
 
    // Запрещаем клонировать объект
    final private function __clone() {}

	public static function init($name = '')
	{
		if (empty($name)) $name = !empty(self::$_name) ? self::$_name : 'default';

		$config = Config::read('database');
        $db_config = $config[$name];

        try {
            @self::$_instance = new PDO("{$db_config['driver']}:host={$db_config['host']};dbname={$db_config['dbname']}", $db_config['user'], $db_config['pass']);
        } catch (PDOException $e) {
            self::error('Connection failed: ' . $e->getMessage());   
        }

        // запоминаем соединение
        self::$_name = $name;
        self::$_connections[$name] = self::$_instance;

        return self::$_instance;
	}

	public static function query($sql)
	{
		try {
            // выполняем запрос
            self::$_statement = self::init()->prepare($sql);
            self::$_statement->setFetchMode(self::$_return_result);
            self::$_statement->execute();
            self::$_nq++;
        } catch(PDOException $e) {
            self::error_sql($e, $sql);
        }
        return self::$_statement;

	}

	//Возвращает массив с индексом
	//$data = $db::getIndCol("id", "SELECT value, id FROM table");
	public function getInd()
	{
		$args  = func_get_args();
		$index = array_shift($args);
		$args  = array_shift($args);
		$query = self::init()->prepare($args);
		$query->setFetchMode(self::$_return_result);
		$ret = array();
		if ($query->execute())
		{
			while($row = $query->fetch())
			{
				$key = $row[$index];
				unset($row[$index]);
				$ret[$key] = $row;
			}
		}
		return $ret;
	}

    //Возвращает одно значение
    public static function getOne($sql) {
        self::query($sql);
        return self::$_statement ? self::$_statement->fetchColumn() : false;
    }
 
    //Возвращает строку
    public static function getRow($sql) {
        self::query($sql);
        return self::$_statement ? self::$_statement->fetch(self::$_return_result) : false;
    }
   
    //Возвращает ряд строк
    public static function getAll($sql)
    {
        self::query($sql);
        return self::$_statement ? self::$_statement->fetchAll(self::$_return_result) : false;
    }

    public static function getWhere($sql, $where) {
    	$query = ' WHERE '.$where['where'].' LIKE '.$where['like']; 
    	$sql .= ' '.$query;

    	return $sql;
    }

    /*
     * Выполняет обновление записи в БД
     *
     * @param string $sql - запрос, должен содержать %sql%
     * @param array $data - данные для обновления (массив: 0 - значение, 1 - тип)
     * @param array $types - типы данных (массив: поле - тип) (необязательное)
     *   тип может быть:
     *      str, PDO::PARAM_STR - строка (по умолчанию)
     *      int, PDO::PARAM_INT - число
     *      bool, PDO::PARAM_BOOLEAN - 1/0
     *      null, PDO::PARAM_NULL - null
     *      expr - выражение SQL (не экранируется!)
     *
     * @param array $fields - поля, которые необходимо использовать (необязательное)
     * @param array $data_dop - дополнительные данные
     * @return bool
     */

    public static function update($sql, array $data, array $types = array(), array $fields = array(), array $data_dop = array())
    {
        $res = false;
        try {
            $prep = '';
            $fz = empty($fields);
            foreach ($data as $field => $value) {
                if ($fz || in_array($field, $fields)) {
                    if (!preg_match('|^[a-z0-9_-]+$|i', $field)) {
                        self::error("Update failed: field {$field} contains invalid characters");
                    }
                    $prep .= ",`{$field}` = ";
                    if (!empty($types[$field]) && $types[$field] === 'expr') {
                        // выражение
                        $prep .= $value;
                    } else {
                        $prep .= ":{$field}";
                    }
                }
            }
            if (empty($prep)) {
                self::error('Update failed: empty data');
            }
            // подготавливаем
            $prep = substr($prep, 1);
            $sql = str_replace('%sql%', ' SET ' . $prep, $sql);
           
            // хэш запроса
            $hash = md5($sql);
            // проверяем, был ли ранее
            if (!isset(self::$_statements[$hash])) {
                // подготавливаем выражение
                self::$_statement = self::init()->prepare($sql);
                // запоминаем
                self::$_statements[$hash] = self::$_statement;
            } else {
                self::$_statement = self::$_statements[$hash];
            }
           
            // установка данных
            foreach ($data as $field => $value) {
                if ($fz || in_array($field, $fields)) {
                    if (empty($types[$field])) {
                        $type = PDO::PARAM_STR;
                    } else {
                        $t = $types[$field];
                        // если выражение - пропускаем
                        if ($t === 'expr') continue;
                        if ($t === 'str' || $t === PDO::PARAM_STR) {
                            $type = PDO::PARAM_STR;
                        } elseif ($t === 'int' || $t === PDO::PARAM_INT) {
                            $type = PDO::PARAM_INT;
                        } elseif ($t === 'bool' || $t === PDO::PARAM_BOOL) {
                            $type = PDO::PARAM_BOOL;
                        } elseif ($t === 'null' || $t === PDO::PARAM_NULL) {
                            $type = PDO::PARAM_NULL;
                        } else {
                            self::error('Update failed: Unknown type for column: ' . $field . "\n\nQuery:\n" . $sql);
                        }
                    }
                    self::$_statement->bindValue($field, $value, $type);
                }
            }
            foreach ($data_dop as $field => $value) {
                self::$_statement->bindValue($field, $value);
            }
            // выполняем
            $res = self::$_statement->execute();
        } catch(PDOException $e) {
            self::error($e->getMessage() . "\n\nQuery:\n" . $sql);
        }
        return $res;
    }
	
}
?>

