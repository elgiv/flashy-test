<?php

namespace App;

require_once  "../vendor/autoload.php";

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Dotenv\Dotenv;

// Register DotEnv
$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');

class DB extends Builder
{
	/**
	 * @return ArrayCollection
	 */
	protected function get(): ArrayCollection
	{
		return $this->getMatchingRecords()->map(function(array $record) {
			return array_intersect_key($record, array_flip($this->select));
		});
	}

	/**
	 * @throws \Exception
	 */
	protected function insert(...$records): void
	{
		$totalRows = $this->collection->count();

		foreach ($records as $record) {
			$record['id'] = ++$totalRows;

			$this->collection->add($record);
		}

		$this->save();
	}

	/**
	 * @throws \Exception
	 */
	protected function update(array $attributes): void
	{
		foreach ($this->getMatchingRecords() as $key => $record) {
			$this->collection->set($key, array_merge($record, $attributes));
		}

		$this->save();
	}

	/**
	 * @throws \Exception
	 */
	protected function delete(): void
	{
		foreach ($this->getMatchingRecords() as $key => $record) {
			$this->collection->remove($key);
		}

		$this->save();
	}
}

DB::table('users')
	->where('id', 2)
	->update([
		'first_name' => 'Eliran cczxczczxc',
		'last_name' => 'Givoni Update'
	]);
die;

//================= DELETE RECORDS =================//
DB::table('users')
	->where('user_name', 'eligiv')
	->orWhere('id', '>', 2) // Remove all id`s above 2
	->delete();

//================= GET RECORDS OF GIVEN TABLE WITH SPECIFIC COLUMNS =================//
$results = DB::table('users')
	->select('id', 'user_name')
	->get();

var_dump($results);

//================= GET RECORDS OF GIVEN TABLE =================//
$results = DB::table('users')->get();

var_dump($results);

//================= GET RECORDS WITH COMPLEX CONDITIONS =================//
$results = DB::table('users')
	->where('user_name', 'eligiv')
	->orWhere('user_name', 'eligiv1')
	->get();

var_dump($results);

//================= UPDATE MULTIPLE RECORDS WITH COMPLEX CONDITIONS =================//
DB::table('users')
	->where('user_name', 'eligiv')
	->orWhere('user_name', 'eligiv1')
	->update([
		'first_name' => 'Eliran Update',
		'last_name' => 'Givoni Update'
	]);

//================= UPDATE SINGLE RECORDS =================//
DB::table('users')
	->where('id', 1)
	->update([
		'first_name' => 'Eliran cczxczczxc',
		'last_name' => 'Givoni Update'
	]);

//================= NEW RECORDS =================//
DB::table('users')
	->insert([
		'user_name' => 'eligiv',
		'first_name' => 'Eliran',
		'last_name' => 'Givoni'
	], [
		'user_name' => 'eligiv1',
		'first_name' => 'Eliran1',
		'last_name' => 'Givoni1'
	]);

//================= NEW SINGLE RECORD =================//
DB::table('users')->insert([
	'user_name' => 'eligiv',
	'first_name' => 'Eliran',
	'last_name' => 'Givoni'
]);