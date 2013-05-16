<?php
class My_Pdf extends My_Tcpdf_Tcpdf {
	private $_htmlHeader = "";
	
	private $_headerOffset = 0;
	
	private $_htmlFooter = "";
	
	private $_footerOffset = 0;
	
	public function setHeaderHtmlData($data, $headerOffset) {
		$this->_htmlHeader = $data;
		$this->_headerOffset = $headerOffset;
		
		$this->setHeaderMargin($headerOffset);
	}
	
	public function setFooterHtmlData($data, $footerOffset) {
		$this->_htmlFooter = $data;
		$this->_footerOffset = $footerOffset;
	}
	
	public function Header() {
		$this->SetFont('dejavusans', '', 10);
		
		$this->writeHTML($this->_htmlHeader);
	}
	
	public function Footer() {
		$this->SetFont('dejavusans', '', 6);
		
		// zalohovani stareho wrapperu
		$oldWrap = $this->getBreakMargin();
		$this->SetAutoPageBreak(false);
		
		$this->SetY(- $this->_footerOffset);

		$this->writeHTML($this->_htmlFooter);
		
		// obnova dat
		$this->SetAutoPageBreak(true, $oldWrap);
	}
	
	public function Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false) {
		if (strtolower(substr($file, 0, 4) == "http")) {
			// pokus o nacteni informaci z internetu
			$fp = @fopen($file, "r");
			
			if ($fp) {
				// soubor byl nalezen a otevren, probehne naciteni dat
				$d = "";
				
				while (!feof($fp)) {
					$d .= fread($fp, 4012);
				}
				
				$file = "@" . $d;
				
				fclose($fp);
				unset($d);
			}
		}
		
		parent::Image($file, $x, $y, $w, $h, $type, $link, $align, $resize, $dpi, $palign, $ismask, $imgmask, $border, $fitbox, $hidden, $fitonpage);
	}
}
