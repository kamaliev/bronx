<?php

namespace core\bronx\db;

use Iterator;
use PDO;
use PDOStatement;

class DBRowIterator implements Iterator{

	private $pdostatement;
	private $key = 1;
	private $result;
	private $class;

	/**
	 * DBRowIterator constructor.
	 *
	 * @param PDOStatement $PDOStatement
	 * @param object $class
	 */
	public function __construct(PDOStatement $PDOStatement, $class) {
		$this->pdostatement = $PDOStatement;
		$this->class = $class;
	}

	/**
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 5.0.0
	 */
	public function current() {
		foreach ( $this->result as $item => $value ) {
			$this->class->$item = $value;
		}
		return $this->class;
	}

	/**
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function next() {
		$this->key++;
	}

	/**
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 5.0.0
	 */
	public function key() {
		return $this->key;
	}

	/**
	 * Checks if current position is valid
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 * @since 5.0.0
	 */
	public function valid() {
		$this->result = $this->pdostatement->fetch(
			PDO::FETCH_OBJ,
			PDO::FETCH_ORI_ABS,
			$this->key
		);
		if($this->result === false) {
			return false;
		}
		return true;
	}

	/**
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function rewind() {
		$this->key = 0;
	}
}