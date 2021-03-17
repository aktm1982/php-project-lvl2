<?php

namespace Differ;

use Illuminate\Support\Collection;

const MISS_SIGN   = "  -";
const EXCEED_SIGN = "  +";
const EXIST_SIGN  = "   ";

function genDiff()
{
  $doc = <<<DOC
  Generate diff

  Usage:
    gendiff (-h|--help)
    gendiff (-v|--version)
    gendiff [--format <fmt>] <firstFile> <secondFile>

  Options:
    -h --help                     Show this screen
    -v --version                  Show version
    --format <fmt>                Report format [default: stylish]
  DOC;

  $args = \Docopt::handle($doc, ['version' => 'Generate diff v.0.1']);

  if(isset($args['<firstFile>']) && isset($args['<secondFile>'])) {
    startGenDiff($args['<firstFile>'], $args['<secondFile>']);
  }
}

function startGenDiff($fileName1, $fileName2)
{
  $json1 = file_get_contents(__DIR__ . "/{$fileName1}");
  $json2 = file_get_contents(__DIR__ . "/{$fileName2}");

  print_r(generateDiffOutput($json1, $json2));
}

function generateDiffOutput($json1, $json2)
{
  $data1 = json_decode($json1, true);
  $data2 = json_decode($json2, true);

  $mergedData = array_merge($data1, $data2);
  ksort($mergedData);

  $dataSet = [$data1, $data2];
  $dif = "";

  foreach(array_keys($mergedData) as $key) {
    $dif .= buildDiff($key, $dataSet, $mergedData);
  }
  
  return $dif;
}

function getMissPrefix($key, $dataSet)
{
  return !array_key_exists($key, $dataSet[0]) ? MISS_SIGN : "";
}

function getExceedPrefix($key, $dataSet)
{
  return !array_key_exists($key, $dataSet[1]) ? EXCEED_SIGN : "";
}

function getExistPrefix($key, $dataSet)
{
  return $dataSet[0][$key] === $dataSet[1][$key] ? EXIST_SIGN : "";
}

function buildDiff($key, $dataSet, $mergedData)
{
  $prefix = getExceedPrefix($key, $dataSet) . getMissPrefix($key, $dataSet) . @getExistPrefix($key, $dataSet);

  return $prefix ? 
    "$prefix $key: ". json_encode($mergedData[$key])."\n" :
    MISS_SIGN . " $key: " . json_encode($dataSet[0][$key]) ."\n" .
    EXCEED_SIGN . " $key: " . json_encode($dataSet[1][$key]). "\n";
}