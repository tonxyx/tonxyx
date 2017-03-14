<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1);

$timeFilePath = '/var/www/html/lastTime.txt';
$url = 'http://www.njuskalo.hr/auti?modelId=11944&onlyFullPrice=1&price%5Bmin%5D=5500&price%5Bmax%5D=7000&yearManufactured%5Bmin%5D=2004&adsWithImages=1&fuelTypeId=602&mileage%5Bmax%5D=200000';

$time = trim(file_get_contents($timeFilePath));
$date = \DateTime::createFromFormat('Y-m-d\TH:i:sT', $time); // create datetime object from last time

$classname = 'EntityList-item EntityList-item--Regular'; // watch only regular items

$dom = new DOMDocument;
libxml_use_internal_errors(true);
$dom->loadHTMLFile($url); // load DOM

$xpath = new DOMXPath($dom); // read DOM
$items = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

$i = 0;
foreach ($items as $item) {
  $itemTime = $item->getElementsByTagName('time')[0]->getAttribute('datetime');
  $newItemTime = \DateTime::createFromFormat('Y-m-d\TH:i:sT', $itemTime); // create item datetime
  if ($newItemTime > $date) { // compare and if newer save time of newest and send email for all
    if ($i == 0) {
      file_put_contents($timeFilePath, $itemTime);
      $i++;
    }

    try { 
      mail('tonibutkovic23@gmail.com', 'Njuskalo Alert - VW', preg_replace('/\s+/', ' ', $item->nodeValue));
    } catch (\Exception $e) {
      throw $e;
    }
  }
}
