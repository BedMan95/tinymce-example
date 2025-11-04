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

    <script src="assets/plugins/tinymce/tinymce.min.js"></script>
    <script>
        // Plugin "Page Setup"
        tinymce.PluginManager.add('pagesetup', function (editor, url) {
            editor.ui.registry.addButton('pagesetup', {
                text: 'Page Setup',
                icon: 'document-properties',
                onAction: function () {
                    editor.windowManager.open({
                        title: 'Pengaturan Halaman',
                        body: {
                            type: 'panel',
                            items: [
                                {
                                    type: 'selectbox',
                                    name: 'size',
                                    label: 'Ukuran Kertas',
                                    items: [
                                        { text: 'A4 (210×297 mm)', value: 'A4' },
                                        { text: 'A5 (148×210 mm)', value: 'A5' },
                                        { text: 'Letter (216×279 mm)', value: 'Letter' }
                                    ]
                                },
                                { type: 'input', name: 'marginTop', label: 'Margin Atas (mm)', inputMode: 'numeric' },
                                { type: 'input', name: 'marginRight', label: 'Margin Kanan (mm)', inputMode: 'numeric' },
                                { type: 'input', name: 'marginBottom', label: 'Margin Bawah (mm)', inputMode: 'numeric' },
                                { type: 'input', name: 'marginLeft', label: 'Margin Kiri (mm)', inputMode: 'numeric' }
                            ]
                        },
                        buttons: [
                            { type: 'cancel', text: 'Batal' },
                            { type: 'submit', text: 'Terapkan', primary: true }
                        ],
                        initialData: {
                            size: editor.getBody().dataset.pageSize || 'A4',
                            marginTop: editor.getBody().dataset.marginTop || '20',
                            marginRight: editor.getBody().dataset.marginRight || '15',
                            marginBottom: editor.getBody().dataset.marginBottom || '20',
                            marginLeft: editor.getBody().dataset.marginLeft || '15'
                        },
                        onSubmit: function (api) {
                            const data = api.getData();
                            const body = editor.getBody();

                            // simpan ke dataset
                            body.dataset.pageSize = data.size;
                            body.dataset.marginTop = data.marginTop;
                            body.dataset.marginRight = data.marginRight;
                            body.dataset.marginBottom = data.marginBottom;
                            body.dataset.marginLeft = data.marginLeft;

                            // ubah CSS halaman
                            const width = data.size === 'A4' ? '210mm' :
                                data.size === 'A5' ? '148mm' : '216mm';
                            const height = data.size === 'A4' ? '297mm' :
                                data.size === 'A5' ? '210mm' : '279mm';
                            const css = `
                                body {
                                  width: ${width};
                                  min-height: ${height};
                                  margin: 0 auto;
                                  padding: ${data.marginTop}mm ${data.marginRight}mm ${data.marginBottom}mm ${data.marginLeft}mm;
                                  background: white;
                                  box-shadow: 0 0 5px rgba(0,0,0,0.1);
                                  box-sizing: border-box;
                                }
                            `;
                            const doc = editor.getDoc();
                            let styleTag = doc.getElementById('page-style');
                            if (!styleTag) {
                                styleTag = doc.createElement('style');
                                styleTag.id = 'page-style';
                                doc.head.appendChild(styleTag);
                            }
                            styleTag.textContent = css;

                            api.close();
                        }
                    });
                }
            });
        });

        // Inisialisasi TinyMCE
        tinymce.init({
            selector: '#editor',
            license_key: 'gpl',
            height: 600,
            plugins: 'pagesetup advlist autolink lists link image charmap preview anchor searchreplace wordcount visualblocks code fullscreen insertdatetime media table emoticons exportpdf',
            toolbar: 'pagesetup | undo redo | styleselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | link image table | forecolor backcolor | fullscreen code | exportpdf',
            setup: function (editor) {
                // tombol custom Label:Value
                editor.ui.registry.addButton('insertlabel', {
                    text: 'Label:Value',
                    tooltip: 'Sisipkan label dan nilai',
                    onAction: function () {
                        var label = prompt('Label:', 'Nama');
                        if (!label) return;
                        var value = prompt('Nilai:', '');
                        if (value === null) return;

                        var html = `
                            <table style="border:none;width:100%;border-collapse:collapse;margin:2px 0;">
                              <tr>
                                <td style="padding:0;border:none;white-space:nowrap;">${editor.dom.encode(label)}:</td>
                                <td style="padding:0;border:none;width:100%;">
                                  <span style="display:inline-block;border-bottom:1px dotted #ccc;width:100%;padding-left:4px;">${editor.dom.encode(value)}</span>
                                </td>
                              </tr>
                            </table>`;
                        editor.insertContent(html);
                    }
                });

                // TAB = 4 spasi
                editor.on('keydown', function (e) {
                    if (e.keyCode === 9) {
                        e.preventDefault();
                        editor.insertContent('&nbsp;&nbsp;&nbsp;&nbsp;');
                    }
                });

                // 🌟 SET DEFAULT PAGE A4 + margin
                editor.on('init', function () {
                    const body = editor.getBody();
                    body.dataset.pageSize = 'A4';
                    body.dataset.marginTop = '20';
                    body.dataset.marginRight = '15';
                    body.dataset.marginBottom = '20';
                    body.dataset.marginLeft = '15';

                    const css = `
                        body {
                          width: 210mm;
                          min-height: 297mm;
                          margin: 0 auto;
                          padding: 20mm 15mm 20mm 15mm;
                          background: white;
                          box-shadow: 0 0 5px rgba(0,0,0,0.1);
                          box-sizing: border-box;
                        }
                    `;
                    const doc = editor.getDoc();
                    const styleTag = doc.createElement('style');
                    styleTag.id = 'page-style';
                    styleTag.textContent = css;
                    doc.head.appendChild(styleTag);
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
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f9f9f9;
        }

        input[type="text"] {
            padding: 10px;
            font-size: 16pt;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
        }

        button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16pt;
        }

        button:hover {
            background: #0056b3;
        }

        a {
            margin: 0 10px;
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .info {
            background: #e9ecef;
            padding: 10px;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <h1>📝 Editor Dokumen</h1>
    <form method="post">
        <label for="title"><strong>Judul Dokumen:</strong></label><br>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>"
            placeholder="Masukkan judul..." style="margin-bottom:20px;"><br><br>

        <textarea id="editor" name="content"><?php echo $content; ?></textarea><br><br>

        <button type="submit">💾 Simpan ke JSON</button>
        <a href="index.php">🆕 Buat Baru</a> |
        <a href="list.php">📋 Lihat Semua Dokumen</a>
    </form>

    <?php if ($doc_id >= 0): ?>
    <div class="info">
        <small>✅ <strong>Dokumen ID:
                <?php echo $doc_id; ?>
            </strong><br>
            📁 Disimpan di: <code>documents.json</code><br>
            ⏰ Terakhir diupdate:
            <?php echo $documents[$doc_id]['created_at'] == null ? 'Baru saja' : $documents[$doc_id]['created_at']; ?>
        </small>
    </div>
    <?php else: ?>
    <div class="info"><small>🆕 Anda sedang membuat dokumen baru.</small></div>
    <?php endif; ?>

    <hr>
    <footer>
        <small>Text Editor PHP 5.6 + TinyMCE + JSON Storage | Dibuat dengan ❤️</small>
    </footer>
</body>

</html>