<?php
require __DIR__.'/vendor/autoload.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Heypixel\JCData\JCData;


$data = [
	0 => [
		"a_tope" => "Valor en key `0` encontrado",
	],
	1 => [
		"a_tope" => "Valor en key `0` encontrado 2" ,
	],
	"gato" => "nulo",
	"hola" => "jejeje",
	"level1" => [
		"level2" => [
			"hola" => "muahahaha",
			"masarrays" => [3242,1,2,3],
		]
	],
	"animales" => null,
];

$JCData = new JCData($data);
$JCData->strictAccess(false);
echo $variable = $JCData->get("level1/level2/hola");
echo "<br>";
//$JCData->set("level1/level2", "grrrr");
//$JCData->set("pedo", "hola");
//$JCData->set("")

$rows = [];
$rows []= (object)[
	"id" => 2343242,
	"alias" => "alias1",
];
$rows []= (object)[
	"id" => 23243,
	"alias" => "alias2",
];
$JCData->set("animales", ["gato" => "miau", "perro" => "guau-guau", "rows" => $rows]);

echo var_dump($JCData->get("animales/gato/miau"));
echo "<br>";

echo var_dump($JCData->get("animales/rows"));
echo "<br>";

//$JCData->clear(["animales", "gato"]);
//echo var_dump($JCData->getAll());

echo $JCData->get("animales/rows/1")->alias;
echo "<br>";
//echo var_dump($JCData->get("level_z/hola"));
//die(var_dump($JCData->data["perro"]));
//die(var_dump($variable));
?>