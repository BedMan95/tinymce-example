<?php
    $jsonFile = 'documents.json';

    $documents = [];
    if (file_exists($jsonFile)) {
        $jsonData  = file_get_contents($jsonFile);
        $documents = json_decode($jsonData, true) ?: [];
    }

    $doc_id  = isset($_GET['id']) ? (int) $_GET['id'] : -1;
    $title   = '';
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
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($doc_id >= 0 && isset($documents[$doc_id])) {
            $documents[$doc_id] = $doc;
        } else {
            $new_id             = count($documents);
            $documents[$new_id] = $doc;
            $doc_id             = $new_id;
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
  <script src="assets/plugins/html2pdf/html2pdf.bundle.min.js"></script>
  <script>

    // Tambahkan tombol Save as PDF
  editor.ui.registry.addButton('savepdf', {
    text: 'Save as PDF',
    icon: 'save',
    onAction: function () {
      const content = editor.getContent();
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = content;

      // Ambil data layout dari page setup
      const body = editor.getBody();
      const pageSize = body.dataset.pageSize || 'A4';
      const marginTop = parseFloat(body.dataset.marginTop || 20);
      const marginRight = parseFloat(body.dataset.marginRight || 15);
      const marginBottom = parseFloat(body.dataset.marginBottom || 20);
      const marginLeft = parseFloat(body.dataset.marginLeft || 15);

      // Tentukan ukuran halaman berdasarkan pilihan
      let jsPdfFormat = 'a4';
      if (pageSize === 'A5') jsPdfFormat = 'a5';
      else if (pageSize === 'Letter') jsPdfFormat = 'letter';

      // Gabungkan semua margin dalam satu array [atas, kanan, bawah, kiri]
      const margins = [marginTop, marginRight, marginBottom, marginLeft];

      // Konversi ke PDF sesuai setup
      html2pdf().set({
        margin: margins,
        filename: `${(editor.getDoc().title || 'document')}.pdf`,
        html2canvas: { scale: 2 },
        jsPDF: {
          unit: 'mm',
          format: jsPdfFormat,
          orientation: 'portrait'
        }
      }).from(tempDiv).save();
    }
  });

    tinymce.init({
      selector: '#viewer',
      menubar: false,
      license_key: 'gpl',
      toolbar: 'pagesetup savepdf',
      height: 400,
      plugins: 'pagesetup code',
      content_style: "body { font-family: Arial; font-size: 14px; padding: 10px; }",
      setup: function (editor) {
        // Tambahkan tombol Save as PDF
        editor.ui.registry.addButton('savepdf', {
          text: 'Save as PDF',
          icon: 'save',
          onAction: function () {
            const content = editor.getContent();
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = content;

            html2pdf().set({
              margin: 10,
              filename: 'laporan.pdf',
              html2canvas: { scale: 2 },
              jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            }).from(tempDiv).save();
          }
        });

        // Setelah editor siap, ubah jadi "pseudo read-only"
        editor.on('init', () => {
          editor.getBody().setAttribute('contenteditable', false);
        });
      }
    });
  </script>

  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      background: #f9f9f9;
    }

    h1 {
      color: #333;
    }

    input[type="text"] {
      padding: 10px;
      font-size: 16pt;
      border: 1px solid #ccc;
      border-radius: 4px;
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
      placeholder="Masukkan judul..." style="width:100%; margin-bottom:20px;"><br><br>

    <textarea id="viewer" name="content"><?php echo $content; ?></textarea><br><br>

    <a href="list.php">📋 Lihat Semua Dokumen</a>
  </form>

  <?php if ($doc_id >= 0): ?>
  <div class="info">
    <small>
      ✅ <strong>Dokumen ID:
        <?php echo $doc_id; ?>
      </strong><br>
      📁 Disimpan di: <code>documents.json</code><br>
      ⏰ Terakhir diupdate:
      <?php echo $documents[$doc_id]['created_at'] == null ? 'Baru saja' : $documents[$doc_id]['created_at']; ?>
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