<?php
class Audit_View_Helper_Pdf extends Zend_View_Helper_Abstract {
	
	public function pdf() {
		return $this;
	}
	
	public function header(My_Tcpdf_Tcpdf $pdf, $text, $size = 20, $mod = "b") {
		$prevMod = $pdf->getFontStyle();
	
		$pdf->SetFont("dejavusans", $mod, $size);
		$pdf->SetX(20);
		$pdf->Write(1, $text);
		$pdf->Ln(17);
		
		$pdf->SetFontSize(12);
		$pdf->SetFont("dejavusans", $prevMod);
	}
	
	public function clientTable(My_Tcpdf_Tcpdf $pdf, $audit, $report) {
		$pdf->SetFont("dejavusans", "", 12);
		
		$lines = array();
		$lines[] = $this->createLine("Název dokumentu", $report->name);
		$lines[] = $this->createLine("Organizace", $report->org);
		$lines[] = $this->createLine("Sídlem", $report->org_hq);
		$lines[] = $this->createLine("IČO", $report->ico);
		$lines[] = $this->createLine("Provozovna sídlem", $report->sub_hq);
		$lines[] = $this->createLine("Datum provedení", $report->done_at);
		$lines[] = $this->createLine("Místo provedení", $report->done_in);
		$lines[] = $this->createLine("Provedl", $report->auditor_name);
		$lines[] = $this->createLine("Účastníci za klienta", $report->contact_name);
		
		$html = "<table border=\"1\" cellpadding=\"5\" width=\"190mm\">" . implode("", $lines) . "</table>";
		$pdf->writeHTML($html);
	}
	
	public function createLine($name,  $value) {
		return "<tr><td><i>$name</i></td><td>$value</td></tr>";
	}
	
	public function form(My_Tcpdf_Tcpdf $pdf, $groups) {
		$index = 1;
		$rows = array();
		
		foreach ($groups["groups"] as $group) {
			$name = $group["name"];
			$info = $groups["groupsInfo"][$group["id"]];
			
			$max = $info["max"];
			$gained = $info["gained"];
			
			$percent = (1 - $gained / $max) * 100;
			$percent = round($percent);
			
			$rows[] = "<tr><td width=\"15mm\">$index</td><td width=\"150mm\">$name</td><td width=\"15mm\" style=\"color:red;font-weight:bolder\">$percent%</td></tr>";
		}
		
		$table = "<table border=\"1\" cellpadding=\"5\" cellspacing=\"0\">" . implode("", $rows) . "</table>";
		
		$pdf->writeHTML($table);
	}
	
	public function footer(My_Tcpdf_Tcpdf $pdf, $logoPath, $text) {
		$maxI = $pdf->getNumPages();
		
		for ($i = 2; $i <= $maxI; $i++) {
			// logo
			$pdf->setPage($i);
			$pdf->SetAutoPageBreak(false);
			$pdf->Image($logoPath, 20, 270, 30);
			
			// text
			$pdf->SetXY(55, 270);
			$pdf->SetFontSize(8);
			$pdf->MultiCell(150, 20, $text, 0, "L");
		}
	}
	
	public function mistakes(My_Tcpdf_Tcpdf $pdf, $mistakes) {
		
		// vygenerovani radku
		$rows = array();
		$pdf->SetFont("dejavusans", "", 10);
		
		foreach ($mistakes as $mistake) {
			$rows[] = "<tr><td width=\"25mm\">" . $mistake["weight"] . "</td><td width=\"50mm\">" . $mistake["mistake"] . "</td><td width=\"50mm\">" . $mistake["suggestion"] . "</td><td width=\"50mm\">" . $mistake["comment"] . "</td></tr>";
		}
		
		// sestveni vysledku
		$html = "<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\"><thead><tr bgcolor=\"#c6c6c6\"><th width=\"25mm\">Závažnost</th><th width=\"50mm\">Neshoda</th><th width=\"50mm\">Navrhované opatření</th><th width=\"50mm\">Komentář</th></tr></thead><tbody>"
					. implode("", $rows) . "</tbody></table>";
		
		$pdf->writeHTML($html);
	}
}