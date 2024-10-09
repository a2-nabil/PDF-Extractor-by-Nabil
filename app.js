function setupDownloadButton() {
  document
    .getElementById("downloadButton")
    .addEventListener("click", function (e) {
      // Prevent the default form submission
      e.preventDefault();

      // Select all images within the parent container
      const images = document.querySelectorAll("#extractedDataForm img");

      if (images.length === 0) {
        alert("No images available to download!");
        return;
      }

      images.forEach((img, index) => {
        // Create a link element to trigger the download
        const a = document.createElement("a");
        const dataUploaded = img.getAttribute("data-uploaded");

        // Set the file name based on the data-uploaded attribute or default naming
        a.download = dataUploaded
          ? dataUploaded
          : `img-${index + 1}.${img.src.split(".").pop()}`;

        // Get the source of the image
        a.href = img.src;

        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
      });
    });
}
