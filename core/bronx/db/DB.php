<?php
namespace core\bronx\db;

use core\Singleton;
use PDO;
use PDOException;

class DB
{
    use Singleton;

    private static $_instance;
    private $pdo;
    private $stmt;
    private $sql = '';
    private $db_host = 'localhost';
    private $db_name = 'dev';
    private $db_user = 'root';
    private $db_password = 'sqlroot';

    /**
     * Закрываем доступ к функции вне класса.
     * Паттерн Singleton не допускает вызов
     * этой функции вне класса
     *
     */
    private function __construct()
    {
        try {
            $this->pdo = new PDO(
                'mysql:host=' . $this->db_host . ';dbname=' . $this->db_name,
                $this->db_user,
                $this->db_password,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function execute($data = '')
    {
        if ($data != '') {
            $this->stmt->execute($data);
            return $this->pdo->lastInsertId();
        } else {
            $this->stmt->execute();
        }

        return $this;
    }

    public function getLastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function bind($param)
    {
        try {
            foreach ( $param as $key => &$value ) {
                switch ( gettype( $value ) ) {
                    case 'integer' : {
                        $this->stmt->bindParam( $key, $value, PDO::PARAM_INT );
                        break;
                    }
                    case 'string' : {
                        $this->stmt->bindParam( $key, $value, PDO::PARAM_STR );
                        break;
                    }
                    default : {
                        $this->stmt->bindParam( $key, $value, PDO::PARAM_STR );
                    }
                }
            }
            $this->stmt->execute();

            return $this;
        } catch (PDOException $e) {
            if(BRONX_DEBUG == 1)
                echo 'DataBase Error: Данные не могут быть добавлены<br>' . $e->getMessage();
        } catch (\Exception $e) {
            if(BRONX_DEBUG == 1)
                echo 'General Error: Данные не могут быть добавлены<br>' . $e->getMessage();
        }
        return null;
    }

    public function fetchObject()
    {
        $this->stmt->execute();
        return $this->stmt->fetchObject();
    }

    public function getQueryResult()
    {
        $this->stmt = $this->pdo->query($this->getQuery());
        return $this;
    }

    public function fetchAll()
    {
        $this->stmt->execute();
        $this->stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $this->stmt->fetchAll()[0];
    }

    public function getQuery()
    {
        return $this->sql;
    }

    public function query($query)
    {
        $this->stmt = $this->pdo->prepare($query);
        return $this;
    }


	/**
     * @return \PDOStatement
     */
    public function stmt()
    {
        return $this->stmt;
    }

    /**
     * Упрощенный запрос обновления
     *
     * @param string $TABLE
     * @param array  $COLUMNS_VALUES
     * @param array  $WHERE
     * @param array  $BIND
     */
    public function update($TABLE = '', $COLUMNS_VALUES = [], $WHERE = [], $BIND = [])
    {
        foreach($COLUMNS_VALUES as &$value) {
            $value = "$value = :$value";
        }
        $COLUMNS_VALUES = implode(', ', $COLUMNS_VALUES);
        $WHERE = empty($WHERE) ? '' : 'WHERE ' . implode(', ', $WHERE);
        $this->query("UPDATE $TABLE SET $COLUMNS_VALUES $WHERE;");
        if(!empty($BIND)) {
            $this->bind($BIND);
        } else {
            $this->execute();
        }
    }

    /**
     * Упрощенная запись выборки из базы данных
     *
     * @param string $TABLE
     * @param array  $COLUMNS
     * @param array  $WHERE
     * @param array  $BIND
     *
     * @return DB $this
     */
    public function select(string $TABLE = '', array $COLUMNS = [], array $WHERE = [], array $BIND = [])
    {
        $COLUMNS = empty($COLUMNS) ? '*' : implode(',', $COLUMNS);
        $WHERE = empty($WHERE) ? '' : 'WHERE ' . implode(' AND ', $WHERE);
        $this->query("SELECT $COLUMNS FROM $TABLE $WHERE;");
        if(!empty($BIND)) {
            $this->bind($BIND);
        } else {
            $this->execute();
        }
        return $this;
    }

    /**
     * Упрощенная запись INSERT для SQL запроса
     *
     * @param string $TABLE
     * @param array  $COLUMN_VALUES
     * @param array  $BIND
     *
     * @return int $this
     */
    public function insert($TABLE = '', $COLUMN_VALUES = [], $BIND = [])
    {
        $COLUMN = implode(', ', $COLUMN_VALUES);
        $VALUES = implode(', :', $COLUMN_VALUES);
        $this->query("INSERT INTO $TABLE ($COLUMN) VALUES (:$VALUES);");
        $this->bind($BIND);
        return $this->getLastInsertId();
    }
}