<?php
class SendNotif_model extends CI_Model
{
    public function send_notif($server_key, $token, $title, $body, $screen)
    {
        # agar diparse sebagai JSON di browser
        header('Content-Type:application/json');

        # atur zona waktu sender server ke Jakarta (WIB / GMT+7)
        date_default_timezone_set("Asia/Jakarta");


        $headers = [
            'Content-Type:application/json',
            'Accept:application/json',
            'Authorization: key=' . $server_key . ''
        ];


        // echo $post_raw_json;
        // exit();


        # Inisiasi CURL request
        $ch = curl_init();

        # atur CURL Options
        curl_setopt_array($ch, array(
            CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send', # URL endpoint
            CURLOPT_HTTPHEADER => $headers, # HTTP Headers
            CURLOPT_RETURNTRANSFER => 1, # return hasil curl_exec ke variabel, tidak langsung dicetak
            CURLOPT_FOLLOWLOCATION => 1, # atur flag followlocation untuk mengikuti bila ada url redirect di server penerima tetap difollow
            CURLOPT_CONNECTTIMEOUT => 60, # set connection timeout ke 60 detik, untuk mencegah request gantung saat server mati
            CURLOPT_TIMEOUT => 60, # set timeout ke 120 detik, untuk mencegah request gantung saat server hang
            CURLOPT_POST => 1, # set method request menjadi POST
            CURLOPT_POSTFIELDS => '{"notification": {"body": "' . $body . '","title": "' . $title . '","sound": "default","badge":"1"}, "priority": "high", "data": {"click_action": "FLUTTER_NOTIFICATION_CLICK", "screen": "' . $screen . '", "status": "done"}, "to": "' . $token . '"}', # attached post data dalam bentuk JSON String,
            // CURLOPT_VERBOSE => 1, # mode debug
            // CURLOPT_HEADER => 1, # cetak header
            CURLOPT_SSL_VERIFYPEER => true
        ));

        # eksekusi CURL request dan tampung hasil responsenya ke variabel $resp
        $resp = curl_exec($ch);

        # validasi curl request tidak error
        if (curl_errno($ch) == false) {
            # jika curl berhasil
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($http_code == 200) {
                # http code === 200 berarti request sukses (harap pastikan server penerima mengirimkan http_code 200 jika berhasil)
                //   return $resp;
                $send = '{"notification": {"body": "' . $body . '","title": "' . $title . '","sound": "default","badge":"1"}, "priority": "high", "data": {"click_action": "FLUTTER_NOTIFICATION_CLICK", "screen": "' . $screen . '", "status": "done"}, "to": "' . $token . '"}';
                $this->db->insert('log_notif', array('log' => $send, 'resp' => $resp));
                return $resp;
            } else {
                # selain itu request gagal (contoh: error 404 page not found)
                // echo 'Error HTTP Code : '.$http_code."\n";

                $send = '{"notification": {"body": "' . $body . '","title": "' . $title . '","sound": "default","badge":"1"}, "priority": "high", "data": {"click_action": "FLUTTER_NOTIFICATION_CLICK", "screen": "' . $screen . '", "status": "done"}, "to": "' . $token . '"}';
                $this->db->insert('log_notif', array('log' => $send, 'resp' => $resp));
                return $resp;
            }
        } else {
            # jika curl error (contoh: request timeout)
            # Daftar kode error : https://curl.haxx.se/libcurl/c/libcurl-errors.html
            // echo "Error while sending request, reason:".curl_error($ch);
        }

        # tutup CURL
        curl_close($ch);
    }
}
