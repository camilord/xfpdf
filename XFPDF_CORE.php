<?php
/**
 * Created by PhpStorm.
 * User: Camilo3rd
 * Date: 19/01/2018
 * Time: 12:22 AM
 */

namespace camilord\xfpdf;


use camilord\xfpdf\FPDF_Protection;

class XFPDF_CORE extends FPDF_Protection
{

    private $font_family = 'Arial';
    private $fontlist = [];

    public function __construct($orientation='P', $unit='mm', $size='A4')
    {
        parent::__construct($orientation, $unit, $size);
        $this->fontlist = [
            'arial', 'courier', 'calibri', 'verdana'
        ];
    }

    public function setDefaultFont($fontname) {
        $this->font_family = $fontname;
    }

    // Colored table
    public function createTable($header, $data, $w = array(30, 27, 38, 20, 35, 40))
    {
        $this->Ln();
        // Colors, line width and bold font
        $this->SetX(10);
        $this->SetFillColor(191,186,186);
        $this->SetTextColor(1);
        $this->SetDrawColor(1);
        $this->SetLineWidth(.3);
        $this->SetFont($this->font_family,'B',8);
        // Header
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(223,223,223);
        $this->SetTextColor(0);
        $this->SetFont($this->font_family,'',8);
        // Data
        $fill = false;
        foreach($data as $row)
        {
            $this->SetX(10);
            $this->Cell($w[0],6,$this->ellipsis($row[0],18),'LR',0,'L',$fill);
            $this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
            $this->Cell($w[2],6,$row[2],'LR',0,'L',$fill);
            $this->Cell($w[3],6,$row[3],'LR',0,'L',$fill);
            $this->Cell($w[4],6,$row[4],'LR',0,'L',$fill);
            $this->Cell($w[5],6,$this->ellipsis($row[5],25),'LR',0,'L',$fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->SetX(10);
        // Closing line
        $this->Cell(array_sum($w),0,'','T');
    }

    public function renderParagraph($building_address, $x_axis, $max = 42, $font_size = 10, $font_style = 'B') {
        $addr = array();
        if (strlen(trim($building_address)) > $max) {
            $tmp = explode(' ', trim($building_address));
            $ctr = 0;
            $addr[$ctr] = '';
            for ($i = 0; $i < count($tmp); $i++) {
                if (strlen($addr[$ctr]) <= $max) {
                    if ($addr[$ctr] == '') {
                        $addr[$ctr] .= $tmp[$i];
                    } else {
                        $addr[$ctr] .= ' '.$tmp[$i];
                    }
                } else {
                    $ctr++;
                    $addr[$ctr] = '';
                    $addr[$ctr] .= $tmp[$i];
                }
            }
            $building_address = $addr;
            unset($addr);
        }
        if (is_array($building_address)) {
            $ctr = 0;
            foreach ($building_address as $addrs) {
                if ($ctr > 0) {
                    $this->Ln();
                }
                $this->setX($x_axis);
                $this->SetFont($this->font_family,$font_style,$font_size);
                $this->Cell(30,6,$addrs,0);
                $ctr++;
            }
        } else {
            $this->Cell(30,6,$building_address,0);
        }
    }

    public function WriteHTML($html)
    {
        //$this->debug = true;
        $allowed_tags = "<pagebreak><runpdf><a><img><p><br><font><table><tr><td><blockquote><h1><h2><h3><h4><pre><red><blue><li><lib><hr><b><i><u><strong><em>";

        //remove all unsupported tags
        $html=strip_tags($html, $allowed_tags);
        //$html=str_replace("\n",' ',$html); //replace carriage returns by spaces

        /*
         * description: remove redundant break lines... and fixture for issue in paragraphing...
         * date: may 3, 2013 4:25pm
         * added by camilo3rd
         * severity: unknown
         */
        /*$html = preg_replace('#<br />(\s*<br />)+#', '<br />', $html);
        $html = str_replace('<b>', '<br /><b>', $html);
        $html = str_replace('<strong>', '<br /><strong>', $html);
        $html = str_replace('<runpdf', '<br /><br /><runpdf', $html);*/
        $html = str_replace("\r",'',$html);
        $html = str_replace("\n",'{nl}',$html);
        $html = str_replace('<br /><br />{nl}<br /><br />', '<br /><br />', $html);
        $html = str_replace('<b>', '<br /><br /><b>', $html);
        $html = str_replace('<lib><br /><br />','<lib>',$html);
        $html = str_replace('<runpdf', '<br /><br /><runpdf', $html);
        $html = str_replace('</b></lib><lib>','</b><br /><br /><br />',$html);
        $html = str_replace('{nl}',"\n",$html);
        // strip BBCodes tags
        $html = $this->stripBBCode($html);
        // ================================================================[ END ]==---

        // debug
//        if ($this->debug) { echo $html; exit; }

        $html = str_replace('&trade;','�',$html);
        $html = str_replace('&copy;','�',$html);
        $html = str_replace('&euro;','�',$html);
        $html = str_replace('&lsquo;','\'',$html);
        $html = str_replace('&rsquo;','\'',$html);
        $html = str_replace('&#61553;','[   ]',$html);

        $a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
        $skip = false;
        $tmp_container = array();
        foreach($a as $i => $e)
        {
            if ($e == "") {
                continue;
            }

            /**
             * for debuging only...
             */
            //$debug = $this->GetX() . ':' . $this->GetY() . ' -> ' . substr($e, 0, 10);

            /**
             * create new page if the header is about at the end of the page...
             */
            if (in_array($e, array('h1','h2','h3','h4')) && $this->GetY() > 245) {
                $this->AddPage();
            }

            if (!$skip) {
                if($this->HREF) {
                    $e = str_replace("\n","",str_replace("\r","",$e));
                }

                if(($i%2) == 0) {
                    // new line
                    if ($this->PRE) {
                        $e = str_replace("\r", "\n", $e);
                    } else {
                        $e = str_replace("\r","",$e);
                    }
                    //Text
                    if ($this->HREF) {
                        $this->PutLink($this->HREF,$e);
                        $skip = true;
                    } else {
                        //$tmp_container[] = $e . ' -> ' . $this->GetX() . ' : '. $this->GetY();
                        $this->Write(6,stripslashes($this->txtentities($e)));
                        //$this->Write(6,"\n");
                    }
                } else {
                    //Tag
                    //if (substr(trim($e),0,1)=='/')
                    //$this->CloseTag(strtoupper(substr($e,strpos($e,'/'))));
                    if($e[0] == '/') {
                        $this->CloseTag(strtoupper(substr($e, 1)));
                    } else {
                        //Extract attributes
                        $a2 = explode(' ',$e);
                        $tag = strtoupper(array_shift($a2));
                        $attr = array();
                        foreach($a2 as $v) {
                            if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3)) {
                                $attr[strtoupper($a3[1])]=$a3[2];
                            }
                        }
                        $tmp_container[] = $tag;
                        $this->OpenTag($tag,$attr);
                    }
                }
            } else {
                $this->HREF='';
                $skip=false;
            }
        }
    }

    public function OpenTag($tag,$attr)
    {
        //Opening tag
        switch($tag) {
            case 'PAGEBREAK':
                $this->AddPage();
                break;
            case 'STRONG':
            case 'B':
                $this->SetStyle('B',true);
                //$this->SetFontSize(12);
                break;
            case 'H1':
                $this->Ln(5);
                $this->SetTextColor(0,0,0);
                $this->SetFontSize(22);
                $this->SetStyle('B',true);
                break;
            case 'H2':
                $this->Ln(5);
                $this->SetFontSize(18);
                $this->SetStyle('B',true);
                break;
            case 'H3':
                $this->Ln(5);
                $this->SetFontSize(16);
                $this->SetStyle('B',true);
                break;
            case 'H4':
                $this->Ln(8);
                $this->SetTextColor(0,0,0);
                $this->SetFontSize(14);
                $this->SetStyle('B',true);
                break;
            /*case 'H5':
                $this->Ln(5);
                $this->SetTextColor(0,0,0);
                $this->SetFontSize(12);
                $this->SetStyle('B',true);
                break;*/
            case 'PRE':
                $this->SetFont('Courier','',11);
                $this->SetFontSize(11);
                $this->SetStyle('B',false);
                $this->SetStyle('I',false);
                $this->PRE=true;
                break;
            case 'RED':
                $this->SetTextColor(255,0,0);
                break;
            case 'BLOCKQUOTE':
                $this->mySetTextColor(100,0,45);
                $this->Ln(3);
                break;
            case 'BLUE':
                $this->SetTextColor(0,0,255);
                break;
            case 'I':
            case 'EM':
                $this->SetStyle('I',true);
                break;
            case 'U':
                $this->SetStyle('U',true);
                break;
            case 'A':
                $this->HREF=$attr['HREF'];
                break;
            case 'IMG':
                if(isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
                    if(!isset($attr['WIDTH']))
                        $attr['WIDTH'] = 0;
                    if(!isset($attr['HEIGHT']))
                        $attr['HEIGHT'] = 0;
                    $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), $this->px2mm($attr['WIDTH']), $this->px2mm($attr['HEIGHT']));
                    $this->Ln(3);
                }
                break;
            case 'LI':
                $this->Ln(2);
                $this->Write(5,'     ' . chr(149) . ' ');
                $this->SetLeftMargin(30);
                $this->mySetTextColor(-1);
                break;
            case 'LIB':
                $this->SetLeftMargin(20);
                $this->Ln(7);
                $this->Write(5,'     ' . chr(149) . ' ');
                $this->SetLeftMargin(30);
                $this->mySetTextColor(-1);
                break;
            case 'BR':
                //$this->Ln(2);
                $this->Ln(3);
                break;
            case 'P':
                //$this->Ln(5);
                $this->Ln(6);
                break;
            case 'HR':
                $this->PutLine();
                break;
            case 'FONT':
                if (isset($attr['COLOR']) && $attr['COLOR']!='') {
                    $coul=$this->hex2dec($attr['COLOR']);
                    $this->mySetTextColor($coul['R'],$coul['G'],$coul['B']);
                    $this->issetcolor=true;
                }
                if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
                    $this->SetFont(strtolower($attr['FACE']));
                    $this->issetfont=true;
                }
                break;
            case 'TABLE': // TABLE-BEGIN
                if( $attr['BORDER'] != '' ) $this->tableborder=$attr['BORDER'];
                else $this->tableborder=0;
                break;
            case 'TR': //TR-BEGIN
                break;
            case 'TD': // TD-BEGIN
                if( $attr['WIDTH'] != '' ) $this->tdwidth=($attr['WIDTH']/4);
                else $this->tdwidth=40; // SET to your own width if you need bigger fixed cells
                if( $attr['HEIGHT'] != '') $this->tdheight=($attr['HEIGHT']/6);
                else $this->tdheight=6; // SET to your own height if you need bigger fixed cells
                if( $attr['ALIGN'] != '' ) {
                    $align=$attr['ALIGN'];
                    if($align=="LEFT") $this->tdalign="L";
                    if($align=="CENTER") $this->tdalign="C";
                    if($align=="RIGHT") $this->tdalign="R";
                }
                else $this->tdalign="L"; // SET to your own
                if( $attr['BGCOLOR'] != '' ) {
                    $coul=$this->hex2dec($attr['BGCOLOR']);
                    $this->SetFillColor($coul['R'], $coul['G'], $coul['B']);
                    $this->tdbgcolor=true;
                }
                $this->tdbegin=true;
                break;
        }
    }

    public function CloseTag($tag)
    {
        //Closing tag
        if ($tag=='H1' || $tag=='H2' || $tag=='H3' || $tag=='H4'){
            $this->Ln(6);
            //$this->SetFont('Times','',12);
            $this->SetFontSize(12);
            $this->SetStyle('U',false);
            $this->SetStyle('B',false);
            $this->mySetTextColor(-1);
        }
        if ($tag=='LI'){
            $this->SetLeftMargin(20);
        }
        if ($tag=='LIB'){
            $this->SetLeftMargin(20);
        }
        if ($tag=='PRE'){
            //$this->SetFont('Times','',12);
            $this->SetFontSize(12);
            $this->PRE=false;
        }
        if ($tag=='RED' || $tag=='BLUE')
            $this->mySetTextColor(-1);
        if ($tag=='BLOCKQUOTE'){
            $this->mySetTextColor(0,0,0);
            $this->Ln(3);
        }
        if($tag=='STRONG')
            $tag='B';
        if($tag=='EM')
            $tag='I';
        if($tag=='B' || $tag=='I' || $tag=='U')
            $this->SetStyle($tag,false);
        if($tag=='A')
            $this->HREF='';
        if($tag=='FONT'){
            if ($this->issetcolor==true) {
                $this->SetTextColor(0,0,0);
            }
            if ($this->issetfont) {
                $this->SetFont('Times','',12);
                $this->issetfont=false;
            }
        }
        if($tag=='TD') { // TD-END
            $this->tdbegin=false;
            $this->tdwidth=0;
            $this->tdheight=0;
            $this->tdalign="L";
            $this->tdbgcolor=false;
        }
        if($tag=='TR') { // TR-END
            $this->Ln();
        }
        if($tag=='TABLE') { // TABLE-END
            //$this->Ln();
            $this->tableborder=0;
        }
    }

    public function SetStyle($tag,$enable)
    {
        $this->$tag+=($enable ? 1 : -1);
        $style='';
        foreach(array('B','I','U') as $s) {
            if($this->$s>0)
                $style.=$s;
        }
        $this->SetFont('',$style);
    }

    public function PutLink($URL,$txt)
    {
        //Put a hyperlink
        $this->SetTextColor(0,0,255);
        $this->SetStyle('U',true);
        $this->Write(5,$txt,$URL);
        $this->SetStyle('U',false);
        $this->mySetTextColor(-1);
    }

    public function PutLine()
    {
        $this->Ln(2);
        $this->Line($this->GetX(),$this->GetY(),$this->GetX()+187,$this->GetY());
        $this->Ln(3);
    }

    public function mySetTextColor($r,$g=0,$b=0){
        static $_r=0, $_g=0, $_b=0;

        if ($r==-1)
            $this->SetTextColor($_r,$_g,$_b);
        else {
            $this->SetTextColor($r,$g,$b);
            $_r=$r;
            $_g=$g;
            $_b=$b;
        }
    }

    // BBCode Stripper -- added last May 10, 2013 10:21am by camilo3rd
    private function stripBBCode($str) {
        return preg_replace('|[[\/\!]*?[^\[\]]*?]|si', '', $str);
    }

    public function txtentities($html){
        $trans = get_html_translation_table(HTML_ENTITIES);
        $trans = array_flip($trans);
        return strtr($html, $trans);
    }

    public function hex2dec($color = "#000000"){
        $tbl_color = array();
        $tbl_color['R']=hexdec(substr($color, 1, 2));
        $tbl_color['G']=hexdec(substr($color, 3, 2));
        $tbl_color['B']=hexdec(substr($color, 5, 2));
        return $tbl_color;
    }

    public function px2mm($px){
        return $px*25.4/72;
    }

    public function ellipsis($str, $max = 30) {
        if (strlen($str) > $max) {
            return substr($str,0,$max).'...';
        } else {
            return $str;
        }
    }

    /**
     * @param $name
     * @param $value
     */
    function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    function __get($name)
    {
        return $this->{$name};
    }
}
