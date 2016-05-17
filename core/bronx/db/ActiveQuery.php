<?php

namespace core\bronx\db;

use core\bronx\controller\Model;

abstract class ActiveQuery extends Model {

	public static function tableName() {
		$path = get_called_class();
		$path = rtrim( str_replace( '\\', '/', $path ), '/\\' );
		if ( ( $pos = mb_strrpos( $path, '/' ) ) !== false ) {
			return mb_substr( $path, $pos + 1 );
		}
	}

	public static function findOne( array $QUERY ) {
		list($where, $bind) = self::queryTransform($QUERY);
		$object = DB::getInstance()->select( mb_strtolower(static::tableName()), [ '*' ], $where, $bind )->fetchObject();

		if(empty($object))
			return null;

		$class = new static();
		foreach($object as $item => $value) {
			$class->$item = $value;
		}
		return $class;
	}

	public static function findAll( array $QUERY )
	{
		list($where, $bind) = self::queryTransform($QUERY);
		$stmt = DB::getInstance()->select( mb_strtolower(static::tableName()), [ '*' ], $where, $bind )->stmt();
		return new DBRowIterator($stmt, new static());
	}

	public static function queryTransform($QUERY)
	{
		$where = [ ];
		$bind  = [ ];
		foreach ( $QUERY as $item => $value ) {
			$where[] = "{$item} = :{$item}";
			$bind[ ':' . $item ] = $value;
		}
		return [$where, $bind];
	}

	protected function createRow()
	{
		list(, $bind) = static::queryTransform($this->_properties);
		$column = [];
		foreach($this->_properties as $item => $value) {
			$column[] = $item;
		}
		DB::getInstance()->insert(mb_strtolower(static::tableName()), $column, $bind);
	}

	protected function updateRow()
	{
		list(, $bind) = static::queryTransform($this->_properties);
		$column = [];
		foreach($this->_properties as $item => $value) {
			$column[] = $item;
		}
		DB::getInstance()->update(mb_strtolower(static::tableName()), $column, ['id = :id'], $bind);
	}
}