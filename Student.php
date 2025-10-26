<?php
class Student {
    private $name;
    private $subjects = [];
    private $gpa;

    public function __construct($name) {
        $this->name = $name;
    }

    public function addSubject($subjectName, $grade, $credits) {
        $this->subjects[] = [
            'name' => $subjectName,
            'grade' => $grade,
            'credits' => $credits
        ];
        $this->calculateGPA();
    }

    private function calculateGPA() {
        $totalPoints = 0;
        $totalCredits = 0;
        foreach ($this->subjects as $subject) {
            $gradePoint = $this->getGradePoint($subject['grade']);
            $totalPoints += $gradePoint * $subject['credits'];
            $totalCredits += $subject['credits'];
        }
        $this->gpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.00;
    }

    private function getGradePoint($grade) {
        $gradePoints = [
            'A' => 4,
            'B' => 3,
            'C' => 2,
            'D' => 1,
            'F' => 0
        ];
        return isset($gradePoints[$grade]) ? $gradePoints[$grade] : 0;
    }

    public function getGPA() {
        return $this->gpa;
    }

    public function getName() {
        return $this->name;
    }

    public function getSubjects() {
        return $this->subjects;
    }

    public function saveToCSV($filename = 'student_report.csv') {
        $file = fopen($filename, 'w');
        fputcsv($file, ['Student Name', 'Subject', 'Grade', 'Credits']);
        foreach ($this->subjects as $subject) {
            fputcsv($file, [$this->name, $subject['name'], $subject['grade'], $subject['credits']]);
        }
        fputcsv($file, ['GPA', $this->gpa]);
        fclose($file);
    }

    public function exportToPDF($filename = 'student_report.pdf') {
        require_once 'fpdf.php';
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Student GPA Report', 0, 1, 'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Student Name: ' . $this->name, 0, 1);
        $pdf->Ln(5);
        $pdf->Cell(60, 10, 'Subject', 1);
        $pdf->Cell(30, 10, 'Grade', 1);
        $pdf->Cell(30, 10, 'Credits', 1);
        $pdf->Ln();
        foreach ($this->subjects as $subject) {
            $pdf->Cell(60, 10, $subject['name'], 1);
            $pdf->Cell(30, 10, $subject['grade'], 1);
            $pdf->Cell(30, 10, $subject['credits'], 1);
            $pdf->Ln();
        }
        $pdf->Ln(5);
        $pdf->Cell(0, 10, 'Overall GPA: ' . $this->gpa, 0, 1);
        $pdf->Output('D', $filename);
    }
}
?>
