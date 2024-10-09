<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdf'])) {
    // Ensure the uploaded file is a PDF
    $file = $_FILES['pdf'];
    $filePath = 'uploads/' . basename($file['name']);

    // Move the uploaded file to the uploads directory
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Execute the Python script to extract text and images
        $command = escapeshellcmd("python extract.py " . escapeshellarg($filePath));
        $output = shell_exec($command);

        // Prepare extracted data for display
        $outputLines = explode("\n", trim($output));
        $textFields = [];
        $imageFields = [];

        foreach ($outputLines as $line) {
            if (strpos($line, 'Saved image:') !== false) {
                // Extract image filenames
                $imageFields[] = trim(str_replace("Saved image: ", "", $line));
            } else {
                // Extract text
                $textFields[] = trim($line);
            }
        }

        // Prepare the HTML for extracted data
        $responseHtml = "<h2>Extracted Data:</h2><form id='extractedDataForm' action='' method='POST'><div class='a2n_img_wrap'>";

        // Add image file inputs
        for ($i = 0; $i < count($imageFields); $i++) {
            $responseHtml .= "
            <div class='img-" . ($i + 1) . "'>
                <input type='file' name='img-" . ($i + 1) . "' id='img-" . ($i + 1) . "' value='" . htmlspecialchars($imageFields[$i]) . "' readonly>
                <img src='" . htmlspecialchars($imageFields[$i]) . "' alt='' class='img-" . ($i + 1) . "' />
            </div>";
        }
        $responseHtml .= "</div>";

        // Add text inputs
        for ($i = 0; $i < count($textFields); $i++) {
            $responseHtml .= "<div class='input-box'>
                <input type='text' name='text-" . ($i + 1) . "' id='text-" . ($i + 1) . "' value='" . htmlspecialchars($textFields[$i]) . "'>
                <label>Text-" . ($i + 1) . "</label>
            </div>";
        }

        // Fill in additional text fields if needed
        for ($i = count($textFields); $i < 6; $i++) {
            $responseHtml .= "<div class='input-box'>
                <input type='text' name='text-" . ($i + 1) . "' id='text-" . ($i + 1) . "'>
                <label>Additional text</label>
            </div>";
        }

        $responseHtml .= "<input class='btn' type='submit' value='Submit'></form>";
        echo $responseHtml; // Send the response back to AJAX
    } else {
        echo "Failed to upload the file.";
    }
} else {
    echo "No file uploaded.";
}
?>