<?php
require_once 'Student.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = $_POST['studentName'];
    $subjects = $_POST['subjects'];

    $student = new Student($studentName);
    foreach ($subjects as $subject) {
        if (!empty($subject['name']) && !empty($subject['grade']) && !empty($subject['credits'])) {
            $student->addSubject($subject['name'], $subject['grade'], $subject['credits']);
        }
    }
    $student->saveToCSV();
    echo "<p>Report saved to student_report.csv</p>";
    if (isset($_POST['export_pdf'])) {
        $student->exportToPDF();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPA Calculator</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        input, select { width: 100%; box-sizing: border-box; }
        #gpa { font-weight: bold; color: blue; }
        button { margin-top: 10px; padding: 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <h1>GPA Calculator</h1>
    <form method="POST">
        <label for="studentName">Student Name:</label>
        <input type="text" id="studentName" name="studentName" required><br><br>

        <table id="subjectsTable">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Grade</th>
                    <th>Credits</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" name="subjects[0][name]" placeholder="Subject Name"></td>
                    <td>
                        <select name="subjects[0][grade]">
                            <option value="">Select Grade</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="F">F</option>
                        </select>
                    </td>
                    <td><input type="number" name="subjects[0][credits]" min="1" placeholder="Credits"></td>
                    <td><button type="button" onclick="removeRow(this)">Remove</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" onclick="addRow()">Add Subject</button>
        <p>Total GPA: <span id="gpa">0.00</span></p>
        <button type="submit" name="save_csv">Save Report (CSV)</button>
        <button type="submit" name="export_pdf">Export as PDF</button>
    </form>

    <script>
        let rowIndex = 1;

        function addRow() {
            const table = document.getElementById('subjectsTable').getElementsByTagName('tbody')[0];
            const newRow = table.rows[0].cloneNode(true);
            // Update names
            newRow.cells[0].querySelector('input').name = `subjects[${rowIndex}][name]`;
            newRow.cells[0].querySelector('input').value = '';
            newRow.cells[1].querySelector('select').name = `subjects[${rowIndex}][grade]`;
            newRow.cells[1].querySelector('select').value = '';
            newRow.cells[2].querySelector('input').name = `subjects[${rowIndex}][credits]`;
            newRow.cells[2].querySelector('input').value = '';
            table.appendChild(newRow);
            rowIndex++;
        }

        function removeRow(button) {
            const row = button.parentNode.parentNode;
            if (document.querySelectorAll('#subjectsTable tbody tr').length > 1) {
                row.parentNode.removeChild(row);
                calculateGPA();
            }
        }

        function calculateGPA() {
            const rows = document.querySelectorAll('#subjectsTable tbody tr');
            let totalPoints = 0;
            let totalCredits = 0;

            rows.forEach(row => {
                const gradeSelect = row.cells[1].querySelector('select');
                const grade = gradeSelect.value;
                const credits = parseFloat(row.cells[2].querySelector('input').value) || 0;
                let gradePoint = 0;
                if (grade === 'A') gradePoint = 4;
                else if (grade === 'B') gradePoint = 3;
                else if (grade === 'C') gradePoint = 2;
                else if (grade === 'D') gradePoint = 1;
                else if (grade === 'F') gradePoint = 0;
                totalPoints += gradePoint * credits;
                totalCredits += credits;
            });

            const gpa = totalCredits > 0 ? (totalPoints / totalCredits).toFixed(2) : '0.00';
            document.getElementById('gpa').textContent = gpa;
        }

        // Add event listeners for real-time calculation
        document.getElementById('subjectsTable').addEventListener('input', calculateGPA);
        document.getElementById('subjectsTable').addEventListener('change', calculateGPA);
    </script>
</body>
</html>
