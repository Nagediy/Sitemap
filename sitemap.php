<?php

// HOST
$host = "https://kreativ-anders.de/";

// IGNORE
$ignore = array('.git', '.github', 'node_modules', '_assets', '_mixins', '_snippets', '_drafts');

// ----------------------------------------------------------------------------------

$directory = $_SERVER["CONTEXT_DOCUMENT_ROOT"];

$filter = function ($file, $key, $iterator) use ($ignore) {
    if ($iterator->hasChildren() && !in_array($file->getFilename(), $ignore)) {
        return true;
    }
    return $file->isFile();
};

$crawlDir = new RecursiveDirectoryIterator(
    $directory,
    RecursiveDirectoryIterator::SKIP_DOTS
);

$crawlFile = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator($crawlDir, $filter)
);

$directory .= "\\";
$sitemap = array($host);

// BUILD SITEMAP
foreach ($crawlFile as $path => $file) {

    $relPath = substr($file, strlen($directory), strlen($file));

    if (strpos($relPath, '\\')) {
      
      $convertPath = str_replace("\\", "/", substr($relPath, 0, strripos($relPath, '\\')) . "\\");
      $relFolderPath = $host . $convertPath;
      array_push($sitemap, $relFolderPath);
    }
}

$sitemap = array_unique($sitemap);
sort($sitemap);

// BUILD XML HEADER
$xml  = "<?xml version='1.0' encoding='UTF-8'?>\n";
$xml .= "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\n";

// LOOP SITEMAP
foreach($sitemap as $page) {

    $file = substr($page, strripos($page, $host) + strlen($host)) . 'index.html';

  $xml .= "\t<url>\n";
  $xml .= "\t\t<loc>" . $page . "</loc>\n";
  $xml .= "\t\t<lastmod>" . date(DATE_ISO8601, filemtime($file)) . "</lastmod>\n";
  //$xml .= "\t\t<changefreq>monthly</changefreq>\n";
  //$xml .= "\t\t<priority>1</priority>\n";
  $xml .= "\t</url>\n";
}

// FINALIZE XML
$xml .= "</urlset>";

// SHOW SITEMAP
header("Content-type: text/xml");
echo $xml;

// WRITE SITEMAP
$sitemap = fopen("sitemap.xml", "w") or die("No sitemap.xml!");
fwrite($sitemap, $xml);
fclose($sitemap);