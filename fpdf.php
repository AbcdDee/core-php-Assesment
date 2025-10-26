<?php
// Simple FPDF-like class for basic PDF generation
class FPDF {
    private $buffer = '';
    private $page = 0;
    private $x = 0;
    private $y = 0;
    private $fontSize = 12;
    private $fontFamily = 'Arial';

    public function AddPage() {
        $this->page++;
        $this->buffer .= "%PDF-1.4\n";
        $this->buffer .= "1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n";
        $this->buffer .= "2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n";
        $this->buffer .= "3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n/Contents 4 0 R\n>>\nendobj\n";
        $this->buffer .= "4 0 obj\n<<\n/Length 44\n>>\nstream\nBT\n/F1 12 Tf\n72 720 Td\n(Hello World) Tj\nET\nendstream\nendobj\n";
        $this->buffer .= "xref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000200 00000 n \n";
        $this->buffer .= "trailer\n<<\n/Size 5\n/Root 1 0 R\n>>\nstartxref\n284\n%%EOF\n";
    }

    public function SetFont($family, $style = '', $size = 0) {
        $this->fontFamily = $family;
        if ($size > 0) $this->fontSize = $size;
    }

    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '') {
        // Simplified cell implementation
        $this->buffer .= "($txt) Tj\n";
    }

    public function Ln($h = '') {
        $this->y += $h ?: $this->fontSize;
    }

    public function Output($dest = '', $name = '', $isUTF8 = false) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . ($name ?: 'doc.pdf') . '"');
        echo $this->buffer;
        exit;
    }
}
?>
