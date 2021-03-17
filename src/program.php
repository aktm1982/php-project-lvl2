<?php

namespace Differ;

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

  makeGenDiff($args['<firstFile>'], $args['<secondFile>']);
}

function makeGenDiff($fileName1, $fileName2)
{
  $json1 = file_get_contents(__DIR__ . "/{$fileName1}"); //$json1 = file_get_contents(realpath($fileName1));
  $json2 = file_get_contents(__DIR__ . "/{$fileName2}"); //$json2 = file_get_contents(realpath($fileName2));

  print_r(getJsonDiff($json1, $json2));
}

function getJsonDiff($json1, $json2)
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

function buildDiff($key, $data1, $data2, $mergedData)
{
  $prefix = getExceedPrefix($key, $data1) .
    getMissPrefix($key, $data2) .
    @getExistPrefix($key, $data1, $data2);

  return $prefix ? 
    "$prefix $key: ". json_encode($mergedData[$key])."\n" :
    MISS_SIGN . " $key: " . json_encode($data1[$key]) ."\n" .
    EXCEED_SIGN . " $key: " . json_encode($data2[$key]). "\n";
}

function getMissPrefix($key, $data)
{
  return !array_key_exists($key, $data) ? MISS_SIGN : "";
}

function getExceedPrefix($key, $data)
{
  return !array_key_exists($key, $data) ? EXCEED_SIGN : "";
}

function getExistPrefix($key, $data1, $data2)
{
  return $data1[$key] === $data2[$key] ? EXIST_SIGN : "";
}