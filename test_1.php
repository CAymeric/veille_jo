<?php
$rss_url = 'https://eur-lex.europa.eu/FR/display-feed.rss?myRssId=6t8f7ZVGqONIPJ7ox6n70Q%3D%3D';
// Fetch the content.
// See http://php.net/manual/en/function.file-get-contents.php for more
// information about the file_get_contents() function.
//$xml = simplexml_load_file($feed_url);
//$xml->asXML('odd.xml');
libxml_use_internal_errors(TRUE);
//$objXmlDocument = simplexml_load_file("odd.xml");
$objXmlDocument = simplexml_load_file($rss_url);

if ($objXmlDocument === FALSE) {
    echo "There were errors parsing the XML file.\n";
    foreach (libxml_get_errors() as $error) {
        echo $error->message;
    }
    exit;
}
//print_r($objXmlDocument);
$objJsonDocument = json_encode($objXmlDocument);
$arrOutput = json_decode($objJsonDocument, TRUE);

echo "<pre>";
//print_r($arrOutput['channel']['item']);

const DB_HOST = 'localhost:3307';
const DB_USER = 'root';
const DB_PASSWORD = 'Sivep_DB_2020';
const DB_NAME = 'web2sivep';
const DB_CHARSET = 'UTF8';


$dSN = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

$databaseConnection = new PDO($dSN, DB_USER, DB_PASSWORD, [
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$comparatif = [];
$req = $databaseConnection->prepare('SELECT titre, lien, categorie FROM `veille_jo`');
$req->execute();

while ($data = $req->fetch()) {
    $comparatif[] = $data;
}

echo "compartif<pre>";
print_r($comparatif);
$test_1 = [];
foreach ($arrOutput['channel']['item'] as $key => $value) {
    array_push($test_1, array('titre' => $arrOutput['channel']['item'][$key]['title'], 'lien' => $arrOutput['channel']['item'][$key]['link'], 'categorie' => $arrOutput['channel']['item'][$key]['category']));
}
echo "test<pre>";
print_r($test_1);

foreach ($arrOutput['channel']['item'] as $key => $value) {
    $veille = $databaseConnection->prepare('
INSERT INTO veille_jo
SET
  titre = :titre,
  lien = :lien,
  categorie = :categorie
');

    try {

        $veille->execute([
            'titre' => $arrOutput['channel']['item'][$key]['title'],
            'lien' => $arrOutput['channel']['item'][$key]['link'],
            'categorie' => $arrOutput['channel']['item'][$key]['category']
        ]);
    } catch (PDOException $e) {
        echo $e;
    }
}
?>
