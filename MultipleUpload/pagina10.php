<?php
require('classes/MultipleUpload.php');
//echo '<pre> FILES TOTALES SIN MODIFICAR <br>' . var_export($_FILES, true) . '</pre><hr>';

$documentos = new MultipleUpload('doc');
echo '<pre> FILES TOTALES <br>' . var_export($documentos->getFiles(), true) . '</pre><hr>';
$documentos->setTarget('upload');
echo '<pre>Se sube a la carpeta' . var_export($documentos->getTarget(), true) . '</pre>';
//$documentos->setName('pepe');
//echo '<pre> FILES TOTALES <br>' . var_export($documentos->getFiles(), true) . '</pre><hr>';
//$documentos->setType('text/plain');
//echo '<pre> TIPO VALIDO? <br>' . var_export($documentos->isValidType(0), true) . '</pre><hr>';
$documentos->setPolicy(MultipleUpload::POLICY_OVERWRITE);
$r = $documentos->upload();
echo '<pre>' . var_export($r, true) . '</pre>';
