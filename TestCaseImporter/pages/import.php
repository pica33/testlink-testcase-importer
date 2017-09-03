<?php
include_once(__DIR__."/CsvReader.php");
include_once(__DIR__.'/csv2xml/Csv2Xml.php');

$smarty = new TLSmarty();
$gui = new stdClass();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST["submit"])) {    
    if ($_FILES["csvFile"]["size"] <= 400000) {
	$fileName = $_FILES["csvFile"]["name"];
	$fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        if ($fileExt == 'csv') {
            $filePath = $_FILES["csvFile"]["tmp_name"];
            $csv =  CsvReader::readCSV($filePath);            
            $csv2Xml = new Csv2Xml();
            $xml = $csv2Xml->createXmlFromCsv($csv);
            $fileNameWoutExt = basename($fileName, $fileExt);
            sendXml($xml, $fileNameWoutExt.'xml');
            return;
        } else {
            $gui->message = plugin_lang_get('invalidFile');
        }
    } else {
        $gui->message = plugin_lang_get('fileTooBig');
    }
}

$gui->title = plugin_lang_get('title');
$gui->labelHeaderMessage = plugin_lang_get('labelHeaderMessage');

$smarty->assign('gui',$gui);
$smarty->display(plugin_file_path('import.tpl'));

function sendXml($xml, $name){
    $tempfolder = ini_get('upload_tmp_dir');
    $file = $tempfolder . '/' . $name;
    $output = fopen($file, "wb");
    fwrite($output, $xml);
    fclose($output);
    header('Content-Description: File Transfer');
    header('Content-Type: text/xml; charset=UTF-8');
    header('Content-Disposition: attachment; filename="'.$name.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: '.filesize($file));
    readfile($file);
    unlink($file);
}