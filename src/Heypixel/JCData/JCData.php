<?php
namespace Heypixel\JCData;
use Heypixel\JCData\Exceptions\StrictAccessException;


/**

	$data = [
		0 => [
			"a_tope" => "Valor en key `0` encontrado",
		],
		"hola" => "jejeje",
		"level1" => [
			"level2" => [
				"hola" => "muahahaha",
				"masarrays" => [3242,1,2,3],
			]
		]
	];
	$JCData = new JCData($data);

	Available methods: 
		$JCData->get($path);
		$JCData->set($path, $value);
		$JCData->clear($path); 

	$path valid arguments:
	1. "level1/level2/hola" will return "muahahaha"
	2. ["level1", "level2", "hola"] will return "muahahaha"
	3. "hola" will it return "jejeje"
	3. "0/a_tope" or [0, "a_tope"] will return "Valor en key `0` encontrado"
	4. "level1" will return: 
		[
			"level2" => [
				"hola" => "muahahaha",
				"masarrays" => [3242,1,2,3],
			]
		]

	
	If get method can't resolve the path, it will return NULL if the strict_mode is disabled, otherwise it will throw a StrictAccessException

	If set or clear method can't resolve the path, it will be added to the data it if the strict_mode is disabled, otherwise it will throw a StrictAccessException
*/
class JCData{
	protected $initial_default_data_structure = null;
	protected $data = null;

	protected $config = [
		"strict_access" => true,
	];

	public function __construct(array $data_structure = [], $config = []){
		$this->config = array_merge($this->config, $config);

		$this->initial_default_data_structure = $data_structure;
		$this->data = $data_structure;
	}

	public function strictAccess($bool = null){
		if(!is_null($bool))
			$this->config["strict_access"] = (bool)$bool;

		return $this->config["strict_access"];
	}
	

	public function get($path, $expected_not_null = false){
		return $this->exploreData($this->data, $path, "get", ["force_strict_access" => $expected_not_null]);
	}

	public function set($path, $value, $return_value = false){
		if(is_null($value) && $this->strictAccess())
			throw new \InvalidArgumentException("value can't be null when strict_access is enabled. Use clear method instead", 1);

		$params = ["value" => $value];
			
		$saved_value = $this->exploreData($this->data, $path, "set", $params);

		return $return_value ? $saved_value : $this;
	}

	public function push($path, $index, $value, $return_value = false){
		if(is_null($value) && $this->strictAccess())
			throw new \InvalidArgumentException("value can't be null when strict_access is enabled. Use clear method instead", 1);
			
		$params = ["index" => $index, "value" => $value];

		$saved_value = $this->exploreData($this->data, $path, "push", $params);

		return $return_value ? $saved_value : $this;
	}

	//It sets to NULL the value
	public function clear($path){
		$this->exploreData($this->data, $path, "set", NULL);
		return $this;
	}

	public function getAll(){
		return $this->data;
	}

	protected function exploreData(&$data, $path, $method = "get", $params = []){
		if(is_int($path)) $path = (string)$path;

		if(is_string($path) && strpos($path, "/") !== false){
			$path = explode("/", $path);
		}

		if($path === "" || !is_numeric($path) && !is_string($path) && !(is_array($path) && count($path) != 0)){
			throw new \InvalidArgumentException("Invalid path argument. It must be a string, a non-empty array or path like \"level1/level2/searched_value\". Instead, a ".gettype($path)." type was received");
		}

		if(is_array($path) && count($path) === 1){
			$path = $path[0];
		};


		if(is_string($path)){
			$searched = $path;
			$path = [];
		}
		else if(is_array($path) && count($path) > 1){
			$searched = end($path);

			if(!is_string($searched) && !is_numeric($searched)){
				throw new \InvalidArgumentException("Last value of your path must be a string or numeric. Instead, a ".gettype($searched)." type was received");
			}

			array_pop($path);
			reset($path);
		}



		$level_data = &$data;

		foreach($path as $k => $k_name){
			if(!is_string($k_name) && !is_numeric($k_name)){
				throw new \InvalidArgumentException("key name must be a string or numeric. At your path there is a ".gettype($k_name)." type");
			} 

			$level_data =& $level_data[$k_name];
		}


		$defined = is_array($level_data) && array_key_exists($searched, $level_data);

		$strict_access = isset($params["force_strict_access"]) ? $params["force_strict_access"] : $this->strictAccess();

		if($strict_access && !$defined){
			throw new StrictAccessException("Key `{$searched}` doesn't exist at saved data. Used path: ".print_r($path, true)." .... Tip! Deactivate the strict_mode if you require free access and writting");
		}

		if($method == "get"){
			return $defined ? $level_data[$searched] : NULL;
		}
		elseif($method == "set"){
			return $level_data[$searched] = $params["value"];
		}
		elseif($method == "push"){
			
			if(!is_array($level_data[$searched])){
				throw new \InvalidArgumentException("Key `{$searched}` isn't a array, you can't push here your data", 1);
			}

			if($params["index"] !== NULL && $params["index"] !== "")
				return $level_data[$searched][$params["index"]] = $params["value"];
			else
				return $level_data[$searched][] = $params["value"];	
		}
	}

	public function resetAll(){
		$this->data = $this->initial_default_data_structure;
		return $this;
	}

}