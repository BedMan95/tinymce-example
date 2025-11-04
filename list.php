<?php
    $jsonFile  = 'documents.json';
    $documents = [];

    if (file_exists($jsonFile)) {
        $jsonData  = file_get_contents($jsonFile);
        $documents = json_decode($jsonData, true);
        if (! is_array($documents)) {
            $documents = [];
        }

    }
?>

<!DOCTYPE html>
<html>
<head><title>Daftar Dokumen</title></head>
<body>
    <h1>Daftar Dokumen (dari JSON)</h1>
    <?php if (empty($documents)): ?>
        <p>Belum ada dokumen. <a href="index.php">Buat baru</a></p>
    <?php else: ?>
        <ul>
        <?php foreach ($documents as $id => $doc): ?>
            <li>
                <a href="viewer.php?id=<?php echo $id; ?>">
                    <strong>View <?php echo htmlspecialchars($doc['title']); ?></strong>
                </a>
                <a href="index.php?id=<?php echo $id; ?>">
                    <strong>Edit <?php echo htmlspecialchars($doc['title']); ?></strong>
                </a>
                <small>(<?php echo $doc['created_at']; ?>)</small>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <hr>
    <a href="index.php">Kembali ke Editor</a>
</body>
</html>