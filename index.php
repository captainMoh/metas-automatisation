<?php

include 'C:/wamp64/www/www.entreprise-nettoyage-91.fr/htdocs/routes.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
require 'vendor/autoload.php';

$excelFilePath = 'meta.xlsm';
$cheminFilePhp = "C:/wamp64/www/www.entreprise-nettoyage-91.fr/htdocs/routes.php";
$fichierRoutes = file_get_contents($cheminFilePhp);


function getExcelInfos(string $pathFile)
{
    $spreadsheet = IOFactory::load($pathFile);

    $worksheet = $spreadsheet->getActiveSheet();

    $data = $worksheet->toArray();

    return $data;
}


function formatInfos(array $arrayExcel, array $arrayRoutes)
{
    $counter = 0;
    $arrayUpdate = [];
    foreach ($arrayExcel as $valueExcel) {
        if ($counter > 0) {
            if ($valueExcel[0] !== null) {
                $linkExploded = explode('/', trim($valueExcel[0]));
                $urlEnd = end($linkExploded);

                $key = array_search($urlEnd, $arrayRoutes);

                if (isset($arrayUpdate[$key])) {
                    $arrayUpdate[$key] = [
                        isset($valueExcel[1]) ? $valueExcel[1] : $arrayUpdate[$key][0],
                        isset($valueExcel[3]) ? $valueExcel[3] : null,
                        isset($valueExcel[5]) ? $valueExcel[5] : null,
                    ];
                } else {
                    $arrayUpdate[$key] = [$valueExcel[1], $valueExcel[3], $valueExcel[5]];
                }
            }
        }
        $counter++;
    }
    return $arrayUpdate;
}

function updateInfos($arrayToUpdate, $arrayWithUpdate)
{
    $arrayPageNotFound = [];
    foreach ($arrayWithUpdate as $key => $newValue) {
        if (array_key_exists($key, $arrayToUpdate)) {
            foreach ($newValue as $index => $newMeta) {
                if ($newMeta !== null) {
                    $arrayToUpdate[$key][$index] = $newMeta;
                }
            }
        }
    }
    return $arrayToUpdate;
}

function writeInFile(array $nouveauTableau, $contenuFichier, string $path)
{
    $metaToWrite = "\$metas=array(\n";
    foreach ($nouveauTableau as $key => $value) {
        $title = "\"" . $value[0] . "\"";
        $description = $value[1] !== "" ? "\"" . $value[1] . "\"" : "\$description_default";
        $keywords = $value[2] !== "" ? "\"" . $value[2] . "\"" : "\$keywords_default";

        $variable = "\"" . $key . "\"" . ' => array(' . $title . ',' .  $description . ',' . $keywords . ')';
        $metaToWrite .= $variable . "," . "\n";
    }
    $metaToWrite .= ");";

    $contenuFichier = preg_replace('/\$metas\s*=\s*array\(.*?\);/s', $metaToWrite, $contenuFichier);

    if (file_put_contents($path, $contenuFichier) !== false) {
        echo "Le tableau a été remplacé avec succès dans le fichier.";
    } else {
        echo "Une erreur s'est produite lors de la modification du fichier.";
    }
}

$data = getExcelInfos($excelFilePath);

$arrayUpdate = formatInfos($data, $routes);

$newArrayMetas = updateInfos($metas, $arrayUpdate);

writeInFile($newArrayMetas, $fichierRoutes, $cheminFilePhp);
