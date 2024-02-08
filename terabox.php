<?php
echo "\n";
echo "TERABOX GET LINK DOWNLOAD!!\n";
inputUrlLagi:
$url = readline("MASUKKAN LINK URL AJAIB TERABOXNYA => : ");
$pattern = "/\/s\/(.+)/"; // Pola regex untuk mencocokkan data setelah '/s/'

if (preg_match($pattern, $url, $matches)) {
    $data_setelah_s = $matches[1]; // Ambil data yang cocok
} else {
    echo "URL TIDAK DITEMUKAN / SALAH!! SILAHKAN MASUKKAN KEMBALI URL YANG BENAR!!\n";
    goto inputUrlLagi;
}

$getData = get("https://terabox-dl.qtcloud.workers.dev/api/get-info?shorturl=$data_setelah_s&pwd=");
$responseData = json_decode($getData, true);
if ($responseData['ok'] == 1) {
    $shareid = $responseData['shareid'];
    $uk = $responseData['uk'];
    $sign = $responseData['sign'];
    $timestamp = $responseData['timestamp'];
    $list = $responseData['list'];

    foreach ($list as $key => $item) {
        echo "\nFOLDER KE: " . ($key + 1) . " | NAMA FOLDER: " . strtoupper($item['filename']) . "\n";

        if (isset($item['children'])) {
            $videoCount = 1; // variabel untuk nomor urut
            
            // Urutkan array $item['children'] berdasarkan nama file
            usort($item['children'], 'compareFilename');

            // Tampilkan informasi file setelah diurutkan
            foreach ($item['children'] as $child) {
                $ukuranMB = $child['size'] / (1024 * 1024);
                $fileSizeMB = number_format($ukuranMB, 2);

                // Memeriksa apakah ekstensi file adalah video atau format video
                $fileExtension = pathinfo($child['filename'], PATHINFO_EXTENSION);
                $videoExtensions = ['mp4', 'avi', 'mkv', 'mov']; // Tambahkan ekstensi lain jika diperlukan

                if (in_array(strtolower($fileExtension), $videoExtensions)) {
                    // Menampilkan informasi file jika itu adalah video atau format video
                    echo $videoCount . ". " . $child['filename'] . " -  $fileSizeMB MB\n";

                    // Increment nomor urut video
                    $videoCount++;
                }
            }
            echo "\n";
            selectLagi:
            $selected = readline("MASUKKAN NOMOR VIDIO YANG INGIN ANDA DOWNLOAD => : ");

            // Memastikan nomor yang dimasukkan oleh pengguna valid
            if (!isset($item['children'][$selected - 1]) || empty($item['children'][$selected - 1])) {
                echo "NOMOR VIDIO YANG ANDA MASUKKAN TIDAK DITEMUKAN!! SILAHKAN COBA LAGI!!\n";
                goto selectLagi;
            }

            // Mengambil data file yang dipilih
            $selectedChild = $item['children'][$selected - 1];

            // Menampilkan informasi file yang dipilih
            echo "NAMA VIDIO: " . $selectedChild['filename'] . "\n";
            echo "SIZE VIDIO: " . number_format($selectedChild['size'] / (1024 * 1024), 2) . " MB\n";

            $data = [
                "shareid" => $shareid,
                "uk" => $uk,
                "sign" => $sign,
                "timestamp" => $timestamp,
                "fs_id" => $selectedChild['fs_id']
            ];

            $headers = [
                'authority: terabox-dl.qtcloud.workers.dev',
                'accept: */*',
                'content-type: application/json',
                'origin: https://terabox-dl.qtcloud.workers.dev',
                'referer: https://terabox-dl.qtcloud.workers.dev/',
                'sec-ch-ua: "Not A Brand";v="99", "Google Chrome";v="121", "Chromium";v="121"',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36'
            ];

            $postData = json_encode($data);
            $getLink = post("https://terabox-dl.qtcloud.workers.dev/api/get-download", $postData, $headers);
            $response = json_decode($getLink, true);

            if ($response['ok'] == 1) {
                $downloadLink = $response['downloadLink'];
                echo "LINK DOWNLOAD : $downloadLink\n\n";

                echo "APAKAH ANDA INGIN MENGAMBIL LINK LAINNYA?? *(y/n) : ";
                $choice = strtolower(trim(readline()));
                if ($choice === 'y' || $choice === 'Y') {
                    goto selectLagi;
                } else {
                    exit(1);
                }
            } else {
                echo "GAGAL MENGAMBIL LINK DOWNLOAD!!\n";
                exit;
            }
        } else {
            echo "TIDAK ADA FILE / VIDIO DALAM FOLDER LINK INI!!\n";
        }
    }
}

function get($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

    $resultApi = curl_exec($ch);
    curl_close($ch);
    return $resultApi;
}

function post($url, $postData, $headers)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $resultApi = curl_exec($curl);
    curl_close($curl);
    return $resultApi;
}

function compareFilename($a, $b)
            {
                return strnatcasecmp($a['filename'], $b['filename']);
            }
