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
        $responseHtml = "<h2>Extracted Data:</h2><form id='extractedDataForm'><div class='a2n_img_wrap'>";

        // Add image file inputs
        for ($i = 0; $i < count($imageFields); $i++) {
            $imageSrc = htmlspecialchars($imageFields[$i]);
            $responseHtml .= "
            <div class='img-" . ($i + 1) . "'>
                <input type='file' name='img-" . ($i + 1) . "' id='img-" . ($i + 1) . "' onchange='previewImage(event, " . ($i + 1) . ")'>
                <img src='$imageSrc' alt='' class='img-" . ($i + 1) . "' />
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

        $responseHtml .= "<input class='btn' id='downloadButton' type='submit' value='Download Images'></form>";
        
        // Include JavaScript for image preview
        $responseHtml .= "
        <script>
        function previewImage(event, index, originalSrc) {
            const file = event.target.files[0];
            const imgElement = document.querySelector('.img-' + index + ' img');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgElement.src = e.target.result; // Preview the uploaded image
                };
                reader.readAsDataURL(file);
                imgElement.setAttribute('data-uploaded', file.name); // Store the uploaded file name
            } else {
                imgElement.src = originalSrc; // Reset to original if no file is uploaded
            }
        }
        </script>";

        echo $responseHtml; // Send the response back to AJAX
    } else {
        echo "Failed to upload the file.";
    }
} else {
    echo "No file uploaded.";
}
?>
