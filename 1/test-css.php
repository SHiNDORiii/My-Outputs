<?php
echo "Checking if style.css exists...<br>";

if (file_exists("style.css")) {
    echo "✅ style.css EXISTS in the same folder!<br>";
    echo "File size: " . filesize("style.css") . " bytes<br>";
    echo "File path: " . realpath("style.css") . "<br>";
} else {
    echo "❌ style.css NOT FOUND in: " . realpath(".") . "<br>";
}
?>
<a href="style.css" target="_blank">Click here to try opening style.css directly</a>