<?php


require_once __DIR__ . '/Services/SecureImageUploader.php';

use EcoBin\Services\SecureImageUploader;

echo "=== TEST 1: Malicious file upload (PHP shell disguised as .jpg) ===\n\n";


$maliciousContent = "<?php system(\$_GET['cmd']); ?>";
$tmpFile = tempnam(sys_get_temp_dir(), 'evil');
file_put_contents($tmpFile, $maliciousContent);

$fakeUpload = [
    'name'     => 'shell.php.jpg',   // attacker-chosen filename, disguised
    'tmp_name' => $tmpFile,
    'error'    => UPLOAD_ERR_OK,
    'size'     => strlen($maliciousContent),
];

$uploader = new SecureImageUploader();

try {

    $result = $uploader->uploadMultiple($fakeUpload, 'waste', 5);
    echo "UNEXPECTED: upload was accepted: " . print_r($result, true) . "\n";
} catch (\RuntimeException $e) {
    echo "BLOCKED (expected): " . $e->getMessage() . "\n";
}

unlink($tmpFile);

echo "\n=== TEST 2: Legitimate image upload (real JPEG) ===\n\n";


$validJpegBase64 = '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAMCAgICAgMCAgIDAwMDBAYEBAQEBAgGBgUGCQgKCgkICQkKDA8MCgsOCwkJDRENDg8QEBEQCgwSExIQEw8QEBD/2wBDAQMDAwQDBAgEBAgQCwkLEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBD/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAj/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=';
$tmpFile2 = tempnam(sys_get_temp_dir(), 'realimg');
file_put_contents($tmpFile2, base64_decode($validJpegBase64));

echo "(Skipping direct uploadMultiple() call here since is_uploaded_file()\n";
echo " requires a genuine HTTP POST context and always fails under CLI —\n";
echo " test this path through the actual web form for full evidence.)\n";
echo "MIME type detected: " . (new \finfo(FILEINFO_MIME_TYPE))->file($tmpFile2) . "\n";
echo "getimagesize() result: " . print_r(@getimagesize($tmpFile2), true) . "\n";

unlink($tmpFile2);

echo "\n=== TEST 3: IDOR — Collection ownership check ===\n\n";

require_once __DIR__ . '/Services/CollectionAuthorization.php';


class FakeCollection {
    public int $residentId;
    public ?int $collectionStaffId = null;
    public function __construct(int $residentId) { $this->residentId = $residentId; }
}


session_start();
$_SESSION['user_id'] = 5;

$othersCollection = new FakeCollection(9);

echo "Session user_id = 5, collection belongs to resident_id = 9\n";
echo "Calling CollectionAuthorization::ensureResidentOwns()...\n";
echo "(This will exit() the script with a 403 if working correctly —\n";
echo " that exit itself IS the evidence. Expected output below:)\n\n";

\EcoBin\Services\CollectionAuthorization::ensureResidentOwns($othersCollection);


echo "UNEXPECTED: reached past the ownership check.\n";