<?php
if ($_FILES['file']) {
    $file = $_FILES['file'];
    $filename = time() . '_' . $file['name'];
    move_uploaded_file($file['tmp_name'], 'uploads/' . $filename);
    echo json_encode(['location' => 'uploads/' . $filename]);
}
?>