<?php

namespace Differ;

const MISS_PREFIX   = "  - ";
const EXCEED_PREFIX = "  + ";
const EXIST_PREFIX  = "    ";

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
  
  $fullMergedData = [];
  foreach (array_keys($mergedData) as $key) {
    $next = [];
    $next[] = $data1[$key] ?? " ";
    $next[] = $data2[$key] ?? " ";
    $fullMergedData[$key] = $next;
  }

  $dif = "";
  foreach($fullMergedData as $key => $element) {
    $dif .= buildDiffOutput($key, $element);
  }
  
  return $dif;
}

function buildDiffOutput($key, $element)
{
  return getExceedString($key, $element) . getMissString($key, $element) . getExistString($key, $element) ?:
    MISS_PREFIX . "$key: $element[0]\n" . EXCEED_PREFIX . "$key: $element[1]\n";
}


function getExceedString($key, $element)
{
  return $element[0] === " " ? (EXCEED_PREFIX . "$key: " . json_encode($element[1]) . "\n") : null;
}

function getMissString($key, $element)
{
  return $element[1] === " " ? (MISS_PREFIX . "$key: " . json_encode($element[0]) . "\n") : null;
}

function getExistString($key, $element)
{
  return $element[0] === $element[1] ? (EXIST_PREFIX . "$key: " . json_encode($element[0]) . "\n") : null;
}