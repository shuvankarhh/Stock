<?php

namespace App\Http\Controllers\Admin;

use Imagick;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CheckController extends Controller
{
    public function index()
    {
        return view('check.index');
    }

    public function store()
    {
        define("UPLOAD_PATH", storage_path('uploads/'));

        set_include_path(base_path('src/'));  //set fpdf path to find library
        
        $this->deleteOlderFiles(UPLOAD_PATH);
        if (!file_exists(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0777);
        }
        $pvars = print_r($_POST, true);

        $json = array(
            'csv'               => array(),
            'CheckBuild'        => '',
            'totalChecks'       => '',
            'totalAmount'       => '',
            'outputPDF'         => '',
            'error'             => '',
            'success'           => '1',
            'last_check_number' => '',
            'debug'             => $_POST['ManualMark'],
            'preview'           => array(),
        );
        if (isset($_FILES['data_file'])) {
            $path = UPLOAD_PATH;

            $input_file = preg_replace("/[^a-zA-Z0-9\.]/", "", $_FILES['data_file']['name']);
            $ext = strtolower(pathinfo($input_file, PATHINFO_EXTENSION));
            if ($ext != "csv") {
                $json['success'] = "0";
                $json['error'] = "Error, Only CSV file is allowed";

                return response()->json($json);
            }
            $target = $path . $input_file;
            $json['CheckBuild'] = $_FILES['data_file']['name'];
            $filee = move_uploaded_file($_FILES['data_file']['tmp_name'], $target);

            require(base_path('src/fpdf/rounded_rect2.php'));

            $mylines = file($target, FILE_SKIP_EMPTY_LINES);
            //$csv=array();

            $csv = array_map([$this, 'str_getcsv0'], file($target, FILE_SKIP_EMPTY_LINES));
            //print_r($csv);
            $json['csv'] = $csv;
            $keys = array_shift($csv);

            foreach ($csv as $i => $row) {
                $csv[$i] = array_combine($keys, $row);
            }
            $ccc = 0;
            $pageWidth = 216;
            $pdf = new \PDF_Ellipse('P', 'mm', array(
                $pageWidth,
                297,
            ));
            //$pdf = new FPDF('P','pt',array(100,150));
            $pdf->SetMargins(0, 0);
            $pdf->AddFont('MICR', '', 'MICR.php');
            $pdf->AddFont('MICRA', '', 'IDAutomationMICR.php');

            $fields = file(base_path('src/library/fields.csv'), FILE_SKIP_EMPTY_LINES);
            $fieldArr = array();

            for ($i = 1; $i < count($fields); $i++) {
                $val = $this->str_getcsv0($fields[$i]);
                $key = $val[0];
                array_shift($val);
                $fieldArr[$key] = $val;
            }

            $json['totalChecks'] = count($csv);
            $tamount = 0;
            $starting_check_number = $_POST['starting_num'];
            foreach ($csv as $values) {
                $pdf->AddPage();

                //$values['CkNum']=$values['MicrCkNum'];
                //$values['StubCkNum']=$values['MicrCkNum'];

                foreach ($values as $key => $val) {
                    $style = $fieldArr[$key][4];
                    $fwidth = $fieldArr[$key][2];
                    if ($style == 'Bold') {
                        $style = 'B';
                    } else {
                        if ($style == 'Regular') {
                            $style = '';
                        }
                    }
                    $fontSize = $fieldArr[$key][5] / 1.3;


                    $fieldFont = $fieldArr[$key][3];
                    if ($fieldFont == 'MICR' || $fieldFont == 'IDAutomationMICR') {
                        //$pdf->SetFont('MICRA', $style, $fontSize);
                        $fieldFont = "MICRA";
                    } else {
                        $fieldFont = "Arial";
                        //$pdf->SetFont('Arial', $style, $fontSize);
                    }
                    $pdf->SetFont($fieldFont, $style, $fontSize);
                    // $pdf->SetFillColor(99,130,213);
                    //CkDateDate

                    if ($key == 'AmountNumber' or $key == 'StubInvoiceAmount' or $key == 'StubAmount') {
                        if ($key == 'AmountNumber') {
                            $tamount = $tamount + (float) $val;
                            list($digit, $decimal) = explode(".", $val);
                            $val = "******$" . number_format($digit) . "." . $decimal . "**";
                        }
                        $xpos = $fieldArr[$key][0] - $pdf->GetStringWidth($val);
                    } else {
                        if ($_POST['ManualMark'] == 0 and $key == 'ManualMark') {
                            $val = '';
                            $xpos = $fieldArr[$key][0];
                        } else {
                            if ($key == "PayToTheOrderPayee" or $key == "PayExactlyNumberWords") {
                                $xpos = $fieldArr[$key][0];
                                if (strlen($fwidth) > 0) {
                                    $words = explode(" ", $val);
                                    $strwords = "";
                                    $lineHt = 0;

                                    for ($k = 0; $k < count($words); $k++) {
                                        $oldstr = $strwords;
                                        $strwords = $strwords . " " . $words[$k];
                                        $swidth = $pdf->GetStringWidth($strwords);
                                        if ($swidth > $fwidth) {
                                            $pdf->SetXY($xpos, $fieldArr[$key][1] + $lineHt);
                                            $pdf->SetTextColor(0);
                                            $pdf->write(0, trim($oldstr));
                                            $lineHt = $lineHt + (int) ($fieldArr[$key][5] / 3);
                                            $strwords = $words[$k];
                                        }
                                        if ($k == count($words) - 1) {
                                            $pdf->SetXY($xpos, $fieldArr[$key][1] + $lineHt);
                                            $pdf->SetTextColor(0);
                                            $pdf->write(0, trim($strwords));
                                        }
                                    }
                                    $val = "";
                                }
                                //if(strlen($fwidth)>0 and $swidth>$fwidth){
                                //	WordWrap0($val,$fwidth,$pdf);
                                //	printText($val,$xpos,(int)($fieldArr[$key][1]),$fwidth,$pdf,(int)($fieldArr[$key][5]/2.5));
                                //	$val='';
                                //}
                            }
                            //else if ($key == 'CkNum' or $key == 'MicrCkNum' or $key == 'StubCkNum') {
                            //    $val  = $starting_check_number;
                            //    $xpos = $fieldArr[$key][0];
                            //}
                            else {
                                $xpos = $fieldArr[$key][0];
                            }
                        }
                    }


                    if ($key == 'SignatureLine1' or $key == 'SignatureLine2') {
                        //echo $key . " " . $val . " : ".$fieldArr[$key][0] . "<br>";
                        if ($val == 1) {
                            $pdf->SetDrawColor(0);
                            $pdf->Line($xpos, $fieldArr[$key][1], $xpos + 65, $fieldArr[$key][1]);
                        }
                    } else {
                        $pdf->SetXY($xpos, $fieldArr[$key][1]);

                        $pdf->SetTextColor(0);
                        $pdf->write(0, $val);
                    }
                }

                //echo "<a href='$pdfoutfile'>Download file # $ccc <br></a>";
                $starting_check_number++;
            }

            $starting_check_number--;
            $json['last_check_number'] = $starting_check_number;
            list($digit, $dec) = explode(".", $tamount);

            $json['totalAmount'] = number_format($digit) . "." . $dec;


            if ($_POST['mode'] == 1) {
                $pdfoutfile = UPLOAD_PATH . $input_file . "_" . time() . ".pdf";
                $pdf->Output('F', $pdfoutfile);
                $json['outputPDF'] = $pdfoutfile;
                // if ($json['totalChecks'] > 0) {
                //     $im = new \Imagick();
                //     $im->setResolution(150, 150);
                //     $im->readImage($pdfoutfile . '[0]');
                //     //$im->setOption('crop','1276x575+0+0');
                //     $im->cropImage(1276, 575, 0, 0);
                //     $im->writeImage($pdfoutfile . "_1.jpg");
                //     $im->clear();
                //     $im->destroy();
                //     $json['preview'] = array(
                //         $pdfoutfile . "_1.jpg",

                //     );
                // }
                // if ($json['totalChecks'] > 1) {
                //     $im = new \Imagick();
                //     $im->setResolution(150, 150);
                //     $im->readImage($pdfoutfile . '[' . ($json['totalChecks'] - 1) . ']');
                //     //$im->setOption('crop','1276x575+0+0');
                //     $im->cropImage(1276, 575, 0, 0);
                //     $im->writeImage($pdfoutfile . "_2.jpg");
                //     $im->clear();
                //     $im->destroy();
                //     $json['preview'] = array(
                //         $pdfoutfile . "_1.jpg",
                //         $pdfoutfile . "_2.jpg",
                //     );
                // }
                //shell_exec("convert -density 150 -crop 1276x575+0+0 $pdfoutfile"."[0]"." uploads/1.jpg");
                //shell_exec("convert -density 150 -crop 1276x575+0+0 $pdfoutfile"."[".($json['totalChecks']-1)."]"." uploads/2.jpg");


            }
        }

        return response()->json($json);
    }

    public function show(Request $request)
    {
       return response()->download($request->url);    
    }

    protected function deleteOlderFiles($path)
    {
        if (file_exists($path)) {
            if ($handle = opendir($path)) {
                while (false !== ($file = readdir($handle))) {
                    if ((time() - filectime($path . $file)) > 60 * 60 * 5) { //delete files older than 5 hours
                        if (preg_match('/\.pdf$/i', strtolower($file)) or preg_match('/\.csv$/i', strtolower($file)) or preg_match('/\.jpg$/i', strtolower($file))) {
                            //86400
                            unlink($path . $file);
                        }
                    }
                }
            }
        }
    }

    protected function printText($text, $x, $y, $width, $pdf, $height)
    {
        $arr = explode("\n", $text);
        //$pdf->SetFont('Times', '', 12);
        $counter = 0;
        foreach ($arr as $val) {
            $val = trim($val);
            $pdf->SetXY($x, $y + ($counter * $height));
            $pdf->Write($height, $val);
            $counter++;
        }
    }

    protected function WordWrap0(&$text, $maxwidth, $pdf)
    {
        //$text=str_replace("\t","    ",$text);
        $text = trim($text);
        if ($text === '') {
            return 0;
        }
        $space = $pdf->GetStringWidth(' ');
        $lines = explode("\n", $text);
        $text = '';
        $count = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            // $words = preg_split('/ +/', $line);
            $words = explode(" ", $line);
            $width = 0;

            foreach ($words as $word) {
                $wordwidth = $pdf->GetStringWidth($word);
                if ($wordwidth > $maxwidth) {
                    // Word is too long, we cut it
                    for ($i = 0; $i < strlen($word); $i++) {
                        $wordwidth = $pdf->GetStringWidth(substr($word, $i, 1));
                        if ($width + $wordwidth <= $maxwidth) {
                            $width += $wordwidth;
                            $text .= substr($word, $i, 1);
                        } else {
                            $width = $wordwidth;
                            $text = rtrim($text) . "\n" . substr($word, $i, 1);
                            $count++;
                        }
                    }
                } elseif ($width + $wordwidth <= $maxwidth) {
                    $width += $wordwidth + $space;
                    $text .= $word . ' ';
                } else {
                    $width = $wordwidth + $space;
                    $text = trim($text) . "\n" . $word . ' ';
                    $count++;
                }
            }
            $text = trim($text) . "\n";
            $count++;
        }
        $text = trim($text);

        return $count;
    }

    protected function str_getcsv0($aline)
    {
        $string = trim($aline);
        $string = preg_replace_callback('|"[^"]+"|', function($matches) {
            return str_replace(',','*comma*',$matches[0]);
        }, $string);
        
        $array = explode(',', $string);
        $array = str_replace('*comma*', ',', $array);
        $array = str_replace('"', '', $array);

        return $array;
    }
}
