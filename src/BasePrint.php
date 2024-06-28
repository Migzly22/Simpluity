<?php
namespace Simpluity\Simpluity;


require_once '../vendor/autoload.php';

use Dompdf\Dompdf;

class BasePrint {

    public function __construct() {

    }
    // $dataconversion = array [stringtochange => data] to replace specific string in the html
    public function print($htmlfile, $orientation, $dataconversion = null ) {

        try {
          // Create a new Dompdf instance
            $dompdf = new Dompdf();

            // Load HTML content
            $html = file_get_contents($htmlfile);

            if(isset($dataconversion)){
                foreach ($dataconversion as $name => $data) {
                    $html = str_replace($name, $data, $html);
                }
            }

            $dompdf->loadHtml($html);

            $dompdf->setPaper('A4', $orientation);

            $dompdf->render();

            $dompdf->stream('generated_pdf.pdf', [
                'Attachment' => true
            ]);
        } catch (Exception $e) {
            echo $e;
        }


    }
}