<?php
$jsonFile = 'documents.json';

$documents = [];
if (file_exists($jsonFile)) {
    $jsonData = file_get_contents($jsonFile);
    $documents = json_decode($jsonData, true) ?: [];
}

$doc_id = isset($_GET['id']) ? (int)$_GET['id'] : -1;  
$title = '';
$content = '';

if ($doc_id >= 0 && isset($documents[$doc_id])) {
    $title   = $documents[$doc_id]['title'];
    $content = $documents[$doc_id]['content'];
}

if ($_POST) {
    $title   = htmlspecialchars($_POST['title']); 
    $content = $_POST['content'];                

    $doc = [
        'title'      => $title,
        'content'    => $content,
        'created_at' => date('Y-m-d H:i:s')
    ];

    if ($doc_id >= 0 && isset($documents[$doc_id])) {
        $documents[$doc_id] = $doc;
    } else {
        $new_id = count($documents);
        $documents[$new_id] = $doc;
        $doc_id = $new_id;
    }

    file_put_contents($jsonFile, json_encode($documents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    header("Location: index.php?id=$doc_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Editor</title>
    
    <!-- TinyMCE -->
    <script src="assets/plugins/tinymce/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#editor',
            license_key: 'gpl',
            readonly: false,
            height: 600,
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace wordcount visualblocks code fullscreen insertdatetime media table emoticons exportpdf',
            
            // Toolbar dengan tombol baru
            toolbar: 'undo redo | styleselect | bold italic underline strikethrough | insertlabel | alignleft aligncenter alignright alignjustify | bullist numlist | link image table | forecolor backcolor | fullscreen code | exportpdf',

            // TAB + Tombol Label:Value
            setup: function(editor) {
                // TAB = 4 spasi
                editor.on('keydown', function(e) {
                    if (e.keyCode === 9) {
                        e.preventDefault();
                        editor.insertContent('&nbsp;&nbsp;&nbsp;&nbsp;');
                    }
                });

                // Tombol Label:Value - PAKAI ui.registry!
                editor.ui.registry.addButton('insertlabel', {
                    text: 'Label:Value',
                    tooltip: 'Sisipkan label dan nilai yang rata',
                    onAction: function() {
                        var label = prompt('Label:', 'Nama');
                        if (!label) return;
                        var value = prompt('Nilai:', '');
                        if (value === null) return;

                        var html = '<table style="border:none;width:100%;border-collapse:collapse;margin:2px 0;"><tr>' +
                                '<td style="padding:0;border:none;white-space:nowrap;">' + 
                                editor.dom.encode(label + ':') + 
                                '</td>' +
                                '<td style="padding:0;border:none;width:100%;">' +
                                '<span style="display:inline-block;border-bottom:1px dotted #ccc;width:100%;padding-left:4px;">' +
                                '&nbsp;' + editor.dom.encode(value) +
                                '</span></td></tr></table>';
                        editor.insertContent(html);
                    }
                });
            },

            images_upload_url: 'upload.php',
            automatic_uploads: true,
            relative_urls: false,
            remove_script_host: false,
            content_style: "body { font-family: Arial, sans-serif; font-size: 14pt; }"
        });
        </script>

    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        h1 { color: #333; }
        input[type="text"] { padding: 10px; font-size: 16pt; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16pt; }
        button:hover { background: #0056b3; }
        a { margin: 0 10px; color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .info { background: #e9ecef; padding: 10px; border-radius: 4px; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>📝 Editor Dokumen</h1>
    
    <form method="post">
        <label for="title"><strong>Judul Dokumen:</strong></label><br>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>" placeholder="Masukkan judul..." style="width:100%; margin-bottom:20px;"><br><br>
        
        <textarea id="editor" name="content"><?php echo $content; ?></textarea><br><br>
        
        <button type="submit">💾 Simpan ke JSON</button>
        <a href="index.php">🆕 Buat Baru</a> | 
        <a href="list.php">📋 Lihat Semua Dokumen</a>
    </form>

    <?php if ($doc_id >= 0): ?>
        <div class="info">
            <small>
                ✅ <strong>Dokumen ID: <?php echo $doc_id; ?></strong><br>
                📁 Disimpan di: <code>documents.json</code><br>
                ⏰ Terakhir diupdate: <?php echo $documents[$doc_id]['created_at'] == null ? 'Baru saja' : $documents[$doc_id]['created_at']; ?>
            </small>
        </div>
    <?php else: ?>
        <div class="info">
            <small>🆕 Anda sedang membuat dokumen baru.</small>
        </div>
    <?php endif; ?>

    <hr>
    <footer>
        <small>Text Editor PHP 5.6 + TinyMCE + JSON Storage | Dibuat dengan ❤️</small>
    </footer>
</body>
</html>