<?php

namespace App;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Exception;

class Builder
{
	use Macroable;

	/**
	 * @var string
	 */
	protected ?string $table = null;

	/**
	 * @var array
	 */
	protected array $where = [];

	/**
	 * @var array
	 */
	protected array $select = [];

	/**
	 * @var ArrayCollection
	 */
	protected ArrayCollection $collection;

	/**
	 * @param string $table
	 * @return $this
	 * @throws Exception
	 */
	protected function table(string $table): self
	{
		$this->table = $table;
		$this->collection = new ArrayCollection($this->getTableContent());

		return $this;
	}

	/**
	 * @param array $columns
	 * @return $this
	 */
	protected function select(...$columns): self
	{
		$this->select = $columns;

		return $this;
	}

	/**
	 * @param string $field
	 * @param mixed $operator
	 * @param mixed|null $value
	 * @param string $type
	 * @return $this
	 */
	protected function where(string $field, mixed $operator, mixed $value = null, string $type = 'AND'): self
	{
		if (!$value) {
			$value = $operator;
			$operator = '=';
		}

		$this->where[] = compact('field', 'operator', 'value', 'type');

		return $this;
	}

	/**
	 * @param array $attributes
	 * @return $this
	 */
	protected function orWhere(...$attributes): self
	{
		$attributes = array_pad($attributes, 3, null);
		$attributes[] = 'OR';

		call_user_func_array([$this, 'where'], array_merge($attributes));

		return $this;
	}

	/**
	 * @return void
	 * @throws Exception
	 */
	protected function save(): void
	{
		$collection = array_values($this->collection->toArray());

		file_put_contents($this->getTablePath(), json_encode($collection));
	}

	/**
	 * @return ArrayCollection
	 */
	protected function getMatchingRecords(): ArrayCollection
	{
		$criteria = new Criteria();

		foreach ($this->where as $where) {
			$expr = new Comparison($where['field'], $where['operator'], $where['value']);
			$method = strtolower($where['type']) . 'Where';

			$criteria->$method($expr);
		}

		return $this->collection->matching($criteria);
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	protected function getTableName(): string
	{
		if (($table = $this->table)) {
			return $table;
		}

		throw new \Exception('Table not found.');
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	private function getTableContent(): array
	{
		$path = $this->getTablePath();

		if (file_exists($path)) {
			return json_decode(file_get_contents($path), true);
		}

		return [];
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	private function getTablePath(): string
	{
		return $_ENV['DATABASE_PATH'] . '/' . $this->getTableName() . '.json';
	}
}