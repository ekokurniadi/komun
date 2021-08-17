<?php
class Api extends MY_Controller
{
	function __construct()
	{
		parent::__construct();
		// ini_set('display_errors', 0);
		date_default_timezone_set('Asia/jakarta');
		$this->load->model('SendNotif_model');
	}

	public function login()
	{
		if ($_POST) {
			$username = $this->input->post('username');
			$password = $this->input->post('password');
			$token_fcm = $this->input->post('token');

			$cek = $this->db->get_where('user', array('username' => $username, 'password' => $password));

			if ($cek->num_rows() == 1) {
				$data = $cek->row();

				// update fcm token
				$this->db->where('id', $data->id);
				$this->db->update('user', array('token' => $token_fcm));
				$result = array(
					'status' => "1",
					'idUser' => $data->id,
					'nama' => $data->nama,
					'username' => $data->username,
					'level' => $data->level,
					'message' => "Selamat datang dan selamat beraktifitas $data->nama"
				);
				echo json_encode($result);
			} else {
				$result = array(
					'status' => "0",
					'message' => 'Gagal, Username atau Password tidak cocok'
				);
				echo json_encode($result);
			}
		}
	}

	public function lupaPassword()
	{
		$username = $this->input->post("username");
		$password = $this->input->post("password");
		$cek = $this->db->get_where('user', ['username' => $username]);
		if ($cek->num_rows() == 1) {
			$data = array(
				"password" => $password
			);
			$this->db->where('username', $username);
			$update = $this->db->update('user', $data);
			if ($update) {
				echo json_encode(array(
					"status" => "200",
					"message" => "Berhasil mengubah password"
				));
			} else {
				echo json_encode(array(
					"status" => "false",
					"message" => "Gagal mengubah password, mohon coba kembali beberapa saat lagi"
				));
			}
		} else {
			echo json_encode(array(
				"status" => "false",
				"message" => "Gagal mengubah password, mohon cek kembali username anda"
			));
		}
	}

	public function register()
	{
		$nama = $this->input->post("nama");
		$alamat = $this->input->post("alamat");
		$notelp = $this->input->post("noTelp");
		$password = $this->input->post("password");
		$image = $_POST['image'];
		$name = $_POST['name'];
		if ($name != "") {
			$realImage = base64_decode($image);
			$files = file_put_contents("./image/" . $name, $realImage);
		}

		$data = array(
			"nama" => $nama,
			"password" => $password,
			"username" => $notelp,
			"alamat" => $alamat,
			"level" => "user",
			"token" => "",
			"picture" => $name
		);
		$cek = $this->db->get_where('user', ["username" => $notelp]);
		if ($cek->num_rows() > 0) {
			echo json_encode(array(
				"status" => "error",
				"message" => "No. Telp sudah pernah didaftarkan, silahkan login ke akun anda."
			));
		} else {
			$insert = $this->db->insert('user', $data);
			if ($insert) {
				echo json_encode(array(
					"status" => "200",
					"message" => "Registrasi berhasil, silahkan login ke akun anda."
				));
			} else {
				echo json_encode(array(
					"status" => "error",
					"message" => "Gagal, silahkan coba kembali beberapa saat lagi."
				));
			}
		}
	}
	public function registerKeamanan()
	{
		$id = $this->input->post('id');
		$nama = $this->input->post("nama");
		$alamat = $this->input->post("alamat");
		$notelp = $this->input->post("noTelp");


		$data = array(
			"id_user" => $id,
			"nama" => $nama,
			"alamat" => $alamat,
			"no_telp" => $notelp,
		);
		$cek = $this->db->get_where('keamanan', ["id_user" => $id]);
		if ($cek->num_rows() > 0) {
			echo json_encode(array(
				"status" => "error",
				"message" => "Anda sudah terdaftar sebagai keamnan"
			));
		} else {
			$insert = $this->db->insert('keamanan', $data);
			if ($insert) {
				echo json_encode(array(
					"status" => "200",
					"message" => "Registrasi sebagai keamanan berhasil"
				));
			} else {
				echo json_encode(array(
					"status" => "error",
					"message" => "Gagal, silahkan coba kembali beberapa saat lagi."
				));
			}
		}
	}

	public function getBeranda()
	{
		$search = $this->input->post("filter");
		$user   = $this->input->post("idUser");
		$where = " WHERE 1=1 AND NOT EXISTS(SELECT c.id_user from komunitas_followers c where c.id_komunitas=a.id and c.id_user='$user') ";
		if ($search == "") {
			$where .= "";
		} else {
			$where .= "AND (a.nama_komunitas like '%$search%')";
		}
		$data = $this->db->query("SELECT a.id,a.nama_komunitas,a.tentang,a.kegiatan,a.info,a.lokasi,b.nama,a.admin FROM komunitas a join user b on a.admin=b.id $where order by nama_komunitas asc");
		if ($data->num_rows() > 0) {
			$response = array();
			foreach ($data->result() as $rows) {
				array_push($response, array(
					"id" => $rows->id,
					"namaKomunitas" => $rows->nama_komunitas,
					"tentang" => $rows->tentang,
					"kegiatan" => $rows->kegiatan,
					"info" => $rows->info,
					"lokasi" => $rows->lokasi,
					"admin" => $rows->nama,
					"idAdmin" => $rows->admin,
					"pengikut" => $this->db->get_where('komunitas_followers', array('id_komunitas' => $rows->id))->num_rows()
				));
			}
			echo json_encode(array(
				"status" => 200,
				"values" => $response
			));
		} else {
			echo json_encode(array(
				"status" => 404,
				"message" => "Data tidak ditemukan"
			));
		}
	}

	public function getKomunitasByuserID()
	{
		$search = $this->input->post("filter");
		$user   = $this->input->post("idUser");
		$where = " WHERE 1=1 AND a.admin ='$user' ";
		if ($search == "") {
			$where .= "";
		} else {
			$where .= "AND (a.nama_komunitas like '%$search%')";
		}
		$data = $this->db->query("SELECT a.id,a.nama_komunitas,a.tentang,a.kegiatan,a.info,a.lokasi,b.nama,a.admin FROM komunitas a join user b on a.admin=b.id $where order by nama_komunitas asc");
		if ($data->num_rows() > 0) {
			$response = array();
			foreach ($data->result() as $rows) {
				array_push($response, array(
					"id" => $rows->id,
					"namaKomunitas" => $rows->nama_komunitas,
					"tentang" => $rows->tentang,
					"kegiatan" => $rows->kegiatan,
					"info" => $rows->info,
					"lokasi" => $rows->lokasi,
					"admin" => $rows->nama,
					"idAdmin" => $rows->admin,
					"pengikut" => $this->db->get_where('komunitas_followers', array('id_komunitas' => $rows->id))->num_rows()
				));
			}
			echo json_encode(array(
				"status" => "200",
				"values" => $response
			));
		} else {
			echo json_encode(array(
				"status" => 404,
				"message" => "Data tidak ditemukan"
			));
		}
	}

	public function buatKomunitas()
	{
		$nama = $this->input->post('nama');
		$tentang = $this->input->post('tentang');
		$kegiatan = $this->input->post('kegiatan');
		$info = $this->input->post('info');
		$contact = $this->input->post('contact');
		$lokasi = $this->input->post('lokasi');
		$admin = $this->input->post('admin');
		$image = $_POST['image'];
		$name = $_POST['name'];
		$realImage = base64_decode($image);
		$files = file_put_contents("./image/" . $name, $realImage);
		$data = array(
			"nama_komunitas" => $nama,
			"tentang" => $tentang,
			"kegiatan" => $kegiatan,
			"info" => $info,
			"lokasi" => $lokasi,
			"cover" => $name,
			"admin" => $admin,
			"contact" => $contact,
		);
		$insert = $this->db->insert('komunitas', $data);
		if ($insert) {
			echo json_encode(array(
				"status" => "200",
				"message" => "Berhasil membuat komunitas."
			));
		} else {
			echo json_encode(array(
				"status" => 403,
				"message" => "Terjadi kesalahan mohon coba kembali."
			));
		}
	}
	public function editKomunitas()
	{
		$id = $this->input->post('id');
		$nama = $this->input->post('nama');
		$tentang = $this->input->post('tentang');
		$kegiatan = $this->input->post('kegiatan');
		$info = $this->input->post('info');
		$contact = $this->input->post('contact');
		$lokasi = $this->input->post('lokasi');

		$data = array(
			"nama_komunitas" => $nama,
			"tentang" => $tentang,
			"kegiatan" => $kegiatan,
			"info" => $info,
			"lokasi" => $lokasi,
			"contact" => $contact,
		);
		$cek =  $this->db->get_where('komunitas', array('id' => $id));
		if ($cek->num_rows() > 0) {
			$this->db->where('id', $id);
			$update = $this->db->update('komunitas', $data);
			if ($update) {
				echo json_encode(array(
					"status" => "200",
					"message" => "Berhasil memperbarui data komunitas."
				));
			} else {
				echo json_encode(array(
					"status" => 403,
					"message" => "Terjadi kesalahan mohon coba kembali."
				));
			}
		} else {
			echo json_encode(array(
				"status" => 403,
				"message" => "Terjadi kesalahan mohon coba kembali."
			));
		}
	}

	public function getChatRoom()
	{

		$starts       = $this->input->post("start");
		$length       = $this->input->post("length");
		$LIMIT        = "LIMIT $starts, $length ";
		$search       = $this->input->post('searching');
		$idUser     = $this->input->post('id');

		$where = "WHERE 1=1 AND idTo ='$idUser' or idFrom ='$idUser' ";
		if ($search == "") {
			$where .= "";
		} else {
			$where .= " AND (idTo like '%$search%') ";
		}

		$where .= " ORDER BY created_at DESC";
		if (isset($LIMIT)) {
			if ($LIMIT != '') {
				$where .= ' ';
			}
		}
		$data = $this->db->query("SELECT * from message_user  $where");
		if ($data->num_rows() > 0) {
			$response = array();
			foreach ($data->result() as $rows) {
				array_push($response, array(
					"idRoom" => $rows->id_room,
					"idTo" => $rows->idTo,
					"idFrom" => $rows->idFrom,
					"namaTujuan" => $this->db->get_where('user', ['id' => $rows->idTo])->row()->nama,
					"namaPengirim" => $this->db->get_where('user', ['id' => $rows->idFrom])->row()->nama,
					"fotoTujuan" => $this->db->get_where('user', ['id' => $rows->idTo])->row()->picture,
					"fotoPengirim" => $this->db->get_where('user', ['id' => $rows->idFrom])->row()->picture,
					"unReadMessageTujuan" => $this->db->get_where('message', ['recipient' => $rows->idTo, 'status_recipient' => 0, 'id_chat' => $rows->id_room])->num_rows(),
					"unReadMessagePengirim" => $this->db->get_where('message', ['sender' => $rows->idFrom, 'status' => 0, 'id_chat' => $rows->id_room])->num_rows(),
					"lastMessageTujuan" => $this->db->get_where('message', ['recipient' => $rows->idTo, 'id_chat' => $rows->id_room])->row()->message,
					"lastMessagePengirim" => $this->db->get_where('message', ['sender' => $rows->idFrom, 'id_chat' => $rows->id_room])->row()->message,
					"lastTimeTujuan" => $this->db->get_where('message', ['recipient' => $rows->idTo, 'id_chat' => $rows->id_room])->row()->created_at == null ? "" : $this->db->get_where('message', ['recipient' => $rows->idTo, 'id_chat' => $rows->id_room])->row()->created_at,
					"lastTimePengirim" => $this->db->get_where('message', ['sender' => $rows->idFrom, 'id_chat' => $rows->id_room])->row()->created_at == null ? "" : $this->db->get_where('message', ['sender' => $rows->idFrom, 'id_chat' => $rows->id_room])->row()->created_at,
					"created" => formatTanggal(substr($rows->created_at, 0, 10)),
					"time" => substr($rows->created_at, 11, 8),
					"telp" => $this->db->get_where('user', ['id' => $rows->idTo])->row()->username,
				));
			}
			echo json_encode(array(
				"status" => "200",
				"data" => $response,
			));
		} else {
			echo json_encode(array(
				"status" => "404",
				"message" => "Kotak pesan masih kosong",
			));
		}
	}


	public function sendMessage()
	{
		$sender = $this->input->post('sender');
		$message = $this->input->post('message');
		$recipient = $this->input->post('recipient');
		$id_chat = $this->input->post('id_chat');
		$date = date('Y-m-d H:i:s');
		$title = "Broadcast Message";
		$body = $message;
		$screen = "list_trx";
		$data = array(
			"id_chat" => $id_chat,
			"message" => $message,
			"sender" => $sender,
			"recipient" => $recipient,
			"created_at" => $date,
			"msg_type" => 0,
			"status" => 0,
			"status_recipient" => 1
		);
		$insert = $this->db->insert('message', $data);
		$token = $this->db->get_where('user', array('id' => $recipient))->row()->token;
		$this->SendNotif_model->send_notif(get_setting('server_fcm_app'), $token, $title, $body, $screen);
		$this->db->query("UPDATE message_user set created_at ='$date' where id_room='$id_chat'");
		if ($insert) {
			echo json_encode(array(
				"status" => "200",
				"message" => "Berhasil",
			));
		} else {
			echo json_encode(array(
				"status" => "500",
				"message" => "Gagal",
			));
		}
	}

	public function getMessage()
	{

		$starts       = $this->input->post("start");
		$length       = $this->input->post("length");
		$LIMIT        = "LIMIT $starts, $length ";
		$search       = $this->input->post('searching');
		$idUser       = $this->input->post('id');

		$where = "WHERE 1=1 AND id_chat='$idUser' ";
		if ($search == "") {
			$where .= "";
		} else {
			$where .= " AND (idTo like '%$search%') ";
		}

		$where .= " ORDER BY id ASC";
		if (isset($LIMIT)) {
			if ($LIMIT != '') {
				$where .= ' ' . $LIMIT;
			}
		}
		$data = $this->db->query("SELECT * from message  $where");
		if ($data->num_rows() > 0) {
			$response = array();
			foreach ($data->result() as $rows) {
				array_push($response, array(
					"message" => $rows->message,
					"sender" => $rows->sender,
					"created" => formatTanggal(substr($rows->created_at, 0, 10)),
					"time" => substr($rows->created_at, 11, 8),
				));
			}
			echo json_encode(array(
				"status" => "200",
				"data" => $response,
			));
		} else {
			echo json_encode(array(
				"status" => "404",
				"message" => "Kotak pesan masih kosong",
			));
		}
	}

	function acak($panjang)
	{
		$karakter = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789';
		$string = '';
		for ($i = 0; $i < $panjang; $i++) {
			$pos = rand(0, strlen($karakter) - 1);
			$string .= $karakter{
				$pos};
		}
		return $string;
	}

	public function createMessage()
	{
		$sender = $this->input->post('sender');
		$senderName = $this->input->post('senderName');
		$recipient = $this->input->post('recipient');
		$recipientName = $this->input->post('recipientName');
		$kode = $this->acak(10);
		$response = array();
		$cek = $this->db->query("SELECT * FROM message_user where idFrom='$sender' and idTo='$recipient'");
		if ($cek->num_rows() > 0) {
			echo json_encode(array(
				"status" => "200",
				"idRoom" => $cek->row()->id_room,
				"recepientId" => $cek->row()->idTo,
				"recepient" => $cek->row()->nameTo
			));
		} else {
			$insert_message_user = array(
				"id_room" => $kode,
				"idTo" => $recipient,
				"idFrom" => $sender,
				"nameTo" => $recipientName,
				"nameFrom" => $senderName,
				"created_at" => date('Y-m-d H:i:s')
			);
			$message = array(
				"id_chat" => $kode,
				"message" => "Hai,",
				"sender" => $sender,
				"recipient" => $recipient,
				"created_at" => date('Y-m-d H:i:s'),
				"msg_type" => 0,
				"status" => 0,
				"status_recipient" => 0
			);
			$prosesInsert = $this->db->insert('message_user', $insert_message_user);
			$insertMessage = $this->db->insert('message', $message);
			echo json_encode(array(
				"status" => "200",
				"idRoom" => $kode,
				"recepientId" => $recipient,
				"recepient" => $recipientName
			));
		}
	}

	public function getAlbum()
	{
		$id = $this->input->post('id');
		$this->db->order_by('id', 'DESC');
		$data = $this->db->get_where('komunitas_album', array('id_komunitas' => $id));

		if ($data->num_rows() > 0) {
			$response = array();
			foreach ($data->result() as $rows) {
				array_push($response, array(
					"id" => $rows->id,
					"foto" => $rows->foto,
				));
			}
			echo json_encode(array(
				"status" => "200",
				"values" => $response
			));
		} else {
			echo json_encode(array(
				"status" => 404,
				"message" => "Data tidak ditemukan"
			));
		}
	}
	public function getAlbumUser()
	{
		$id = $this->input->post('id');
		$this->db->order_by('id', 'DESC');
		$data = $this->db->get_where('user_album', array('id_user' => $id));

		if ($data->num_rows() > 0) {
			$response = array();
			foreach ($data->result() as $rows) {
				array_push($response, array(
					"id" => $rows->id,
					"foto" => $rows->foto,
				));
			}
			echo json_encode(array(
				"status" => "200",
				"values" => $response
			));
		} else {
			echo json_encode(array(
				"status" => 404,
				"message" => "Data tidak ditemukan"
			));
		}
	}

	public function getBerandaFollow()
	{
		$search = $this->input->post("filter");
		$user   = $this->input->post("idUser");
		$where = " WHERE 1=1 AND EXISTS(SELECT c.id_user from komunitas_followers c where c.id_komunitas=a.id and c.id_user='$user') ";
		if ($search == "") {
			$where .= "";
		} else {
			$where .= "AND (a.nama_komunitas like '%$search%')";
		}
		$data = $this->db->query("SELECT a.id,a.nama_komunitas,a.tentang,a.kegiatan,a.info,a.lokasi,b.nama,a.admin FROM komunitas a join user b on a.admin=b.id $where order by nama_komunitas asc");
		if ($data->num_rows() > 0) {
			$response = array();
			foreach ($data->result() as $rows) {
				array_push($response, array(
					"id" => $rows->id,
					"namaKomunitas" => $rows->nama_komunitas,
					"tentang" => $rows->tentang,
					"kegiatan" => $rows->kegiatan,
					"info" => $rows->info,
					"lokasi" => $rows->lokasi,
					"admin" => $rows->nama,
					"idAdmin" => $rows->admin,
					"pengikut" => $this->db->get_where('komunitas_followers', array('id_komunitas' => $rows->id))->num_rows()
				));
			}
			echo json_encode(array(
				"status" => 200,
				"values" => $response
			));
		} else {
			echo json_encode(array(
				"status" => 404,
				"message" => "Data tidak ditemukan"
			));
		}
	}

	public function follow()
	{
		$idUser = $this->input->post('idUser');
		$idKomunitas = $this->input->post('idKomunitas');

		$cek = $this->db->query("SELECT * FROM komunitas_followers where id_user='$idUser' and id_komunitas='$idKomunitas'");
		if ($cek->num_rows() > 0) {
			echo json_encode(array(
				"status" => 300,
				"message" => "Anda sudah mengikuti komunitas ini !"
			));
		} else {
			$data = array();
			$data = array(
				"id_komunitas" => $idKomunitas,
				"id_user" => $idUser,
				"status" => 1
			);
			$insert = $this->db->insert('komunitas_followers', $data);
			if ($insert) {
				echo json_encode(array(
					"status" => 200,
					"message" => "Berhasil mengikuti komunitas"
				));
			} else {
				echo json_encode(array(
					"status" => 500,
					"message" => "Terjadi kesalahan, silahkan coba kembali"
				));
			}
		}
	}

	public function updateFotoProfile()
	{
		$id = $_POST['id'];
		$image = $_POST['image'];
		$name = $_POST['name'];
		$folderPath = "./image/" . $name;
		$realImage = base64_decode($image);
		$files = file_put_contents("./image/" . $name, $realImage);
		$data = array(
			"picture" => $name,
		);
		$this->db->where('id', $id);
		$this->db->update('user', $data);
		echo json_encode(array(
			"status" => "1",
			"pesan" => "Foto Profil berhasil di perbarui",
		));
	}

	public function updateState()
	{
		$id = $this->input->post('id');
		$status = $this->input->post('status');

		$this->db->where('id', $id);
		$upd = $this->db->update('user', array("status" => $status));
		if ($upd) {
			echo json_encode(array(
				"status" => "200",
				"message" => "Berhasil merubah data",
			));
		} else {
			echo json_encode(array(
				"status" => "500",
				"message" => "Terjadi kesalahan",
			));
		}
	}

	public function getCurrentUser()
	{
		$id = $this->input->post('id');
		$data = $this->db->query("SELECT * from user where id='$id'");
		$result = array();
		if ($data->num_rows() > 0) {
			$result = array(
				"status" => 200,
				"id" => $data->row()->id,
				"nama" => $data->row()->nama,
				"alamat" => $data->row()->alamat == "" ? "-" : $data->row()->alamat,
				"level" => $data->row()->level,
				"username" => $data->row()->username,
				"password" => $data->row()->password,
				"picture" => $data->row()->picture == "" ? "default.png" : $data->row()->picture,
				"statususer" => $data->row()->status,
			);
			echo json_encode($result);
		}
	}

	public function updateProfileUser()
	{
		$id = $this->input->post("id");
		$name = $this->input->post("nama");
		$alamat = $this->input->post("alamat");
		$password = $this->input->post("password");
		$result = array(
			"nama" => $name,
			"alamat" => $alamat,
			"password" => $password
		);

		// echo json_encode($result);
		$this->db->where('id', $id);
		$update = $this->db->update('user', $result);
		if ($update) {
			echo json_encode(array(
				"status" => 200,
				"message" => "Berhasil melakukan perubahan data."
			));
		} else {
			echo json_encode(array(
				"status" => "error",
				"message" => "Gagal melakukan perubahan data."
			));
		}
	}


	public function unfollow()
	{
		$idUser = $this->input->post('idUser');
		$idKomunitas = $this->input->post('idKomunitas');

		$cek = $this->db->query("SELECT * FROM komunitas_followers where id_user='$idUser' and id_komunitas='$idKomunitas'");
		if ($cek->num_rows() > 0) {
			$this->db->where('id_user', $idUser);
			$this->db->where('id_komunitas', $idKomunitas);
			$delete = $this->db->delete('komunitas_followers');
			echo json_encode(array(
				"status" => 200,
				"message" => "Berhasil, anda telah berhenti mengikuti komunitas ini"
			));
		} else {
			echo json_encode(array(
				"status" => 300,
				"message" => "Anda belum mengikuti komunitas ini !"
			));
		}
	}

	public function getById()
	{
		$idKomunitas = $this->input->post('id');
		$data = $this->db->get_where('komunitas', array('id' => $idKomunitas));
		$response = array();
		if ($data->num_rows() > 0) {
			$response = array(
				"nama_komunitas" => $data->row()->nama_komunitas,
				"info" => $data->row()->info,
				"pengikut" => (string)$this->db->get_where('komunitas_followers', array('id_komunitas' => $idKomunitas))->num_rows(),
				"post" => (string)$this->db->get_where('komunitas_album', array('id_komunitas' => $idKomunitas))->num_rows(),
				"lokasi" => $data->row()->lokasi,
				"cover" => $data->row()->cover,
				"tentang" => $data->row()->tentang,
				"contact" => $data->row()->contact,
				"admin" => $data->row()->admin,
			);
			echo json_encode(array(
				"status" => 200,
				"values" => $response
			));
		} else {
			echo json_encode(array(
				"status" => "403",
				"message" => "Data tidak ditemukan"
			));
		}
	}
	public function getByIdEdit()
	{
		$idKomunitas = $this->input->post('id');
		$data = $this->db->get_where('komunitas', array('id' => $idKomunitas));
		$response = array();
		if ($data->num_rows() > 0) {
			$response = array(
				"nama_komunitas" => $data->row()->nama_komunitas,
				"info" => $data->row()->info,
				"kegiatan" => $data->row()->kegiatan,
				"lokasi" => $data->row()->lokasi,
				"tentang" => $data->row()->tentang,
				"cover" => $data->row()->cover,
				"contact" => $data->row()->contact,
			);
			echo json_encode(array(
				"status" => 200,
				"values" => $response
			));
		} else {
			echo json_encode(array(
				"status" => "403",
				"message" => "Data tidak ditemukan"
			));
		}
	}

	public function uploadAlbum()
	{
		$id = $_POST['id'];
		$image = $_POST['image'];
		$name = $_POST['name'];
		$folderPath = "./image/" . $name;
		$realImage = base64_decode($image);
		$files = file_put_contents("./image/" . $name, $realImage);
		$data = array(
			"id_komunitas" => $id,
			"foto" => $name,
		);
		$this->db->insert('komunitas_album', $data);
		echo json_encode(array(
			"status" => "1",
			"pesan" => "Foto berhasil di Unggah",
		));
	}

	public function uploadAlbumUser()
	{
		$id = $_POST['id'];
		$image = $_POST['image'];
		$name = $_POST['name'];
		$folderPath = "./image/" . $name;
		$realImage = base64_decode($image);
		$files = file_put_contents("./image/" . $name, $realImage);
		$data = array(
			"id_user" => $id,
			"foto" => $name,
			"created_at" => date('Y-m-d H:i:s'),
			"post" => 0
		);
		$this->db->insert('user_album', $data);
		echo json_encode(array(
			"status" => "1",
			"pesan" => "Foto berhasil di Unggah",
		));
	}


	public function updateFotoProfileKomunitas()
	{
		$id = $_POST['id'];
		$image = $_POST['image'];
		$name = $_POST['name'];
		$realImage = base64_decode($image);
		$files = file_put_contents("./image/" . $name, $realImage);
		$data = array(
			"cover" => $name,
		);
		$this->db->where('id', $id);
		$this->db->update('komunitas', $data);
		echo json_encode(array(
			"status" => "1",
			"pesan" => "Foto Profil berhasil di perbarui",
		));
	}

	public function getNotif()
	{
		if ($_POST) {
			$id = $this->input->post('id');
			$deleted = $this->input->post('deleted');
			$result = array();
			$data = $this->db->query("SELECT a.*,b.nama FROM notifikasi a join user b on a.user_id=b.id where a.user_id = '$id' and a.deleted ='$deleted' order by a.id DESC");

			$response = array();
			if ($data->num_rows() <= 0) {
				$result = array(
					"status" => "0",
					"pesan" => "Tidak ada notifikasi"
				);
				echo json_encode($result);
			} else {
				foreach ($data->result() as $rows) {
					$sub_array = array();
					$sub_array[] = $rows->pesan;
					$sub_array[] = $rows->status;
					$sub_array[] = formatTanggal(substr($rows->created, 0, 10));
					$sub_array[] = substr($rows->created, 11, 19);
					$sub_array[] = $rows->id;
					$response[] = $sub_array;
				}

				$result = array(
					"status" => "1",
					"pesan" => "Success",
					"values" => $response
				);
				echo json_encode($result);
			}
		}
	}

	public function updateStatusNotif()
	{
		$id = $this->input->post('id');
		$status = $this->input->post('status');
		$deleted = $this->input->post('deleted');

		$update = $this->db->query("UPDATE notifikasi set status = '$status',deleted ='$deleted' where id='$id'");
		if ($update) {
			echo json_encode(array(
				"status" => 200,
				"message" => "Berhasil melakukan perubahan data."
			));
		} else {
			echo json_encode(array(
				"status" => "error",
				"message" => "Gagal"
			));
		}
	}

	public function fetch_data()
	{
		$starts       = $this->input->post("start");
		$length       = $this->input->post("length");
		$LIMIT        = "LIMIT  $starts, $length ";
		$draw         = $this->input->post("draw");
		$search       = $this->input->post('searching');
		$orders       = isset($_POST['order']) ? $_POST['order'] : '';
		$id           = $this->input->post('id');

		$where = "WHERE 1=1 AND b.id_komunitas='$id'";
		// $searchingColumn;
		$result = array();
		if (isset($search)) {
			if ($search != '') {
				$where .= " AND (a.nama LIKE '%$search%')";
			}
		}

		if (isset($orders)) {
			if ($orders != '') {
				$order = $orders;
				$order_column = [''];
				$order_clm  = $order_column[$order[0]['column']];
				$order_by   = $order[0]['dir'];
				$where .= " ORDER BY $order_clm $order_by ";
			} else {
				$where .= " ORDER BY a.nama ASC ";
			}
		} else {
			$where .= " ORDER BY a.nama ASC ";
		}
		if (isset($LIMIT)) {
			if ($LIMIT != '') {
				$where .= ' ' . $LIMIT;
			}
		}
		$index = 1;
		$fetch = $this->db->query("SELECT a.nama,a.id,a.picture from user a join komunitas_followers b on a.id = b.id_user $where");
		foreach ($fetch->result() as $rows) {
			$sub_array = array();
			$sub_array[] = $index;
			$sub_array[] = $rows->id;
			$sub_array[] = $rows->nama;
			$sub_array[] = $rows->picture;
			$result[]      = $sub_array;
			$index++;
		}
		$output = array(
			"data"            =>     $result,

		);
		echo json_encode($output);
	}


	public function fetch_komunitas()
	{
		$starts       = $this->input->post("start");
		$length       = $this->input->post("length");
		$LIMIT        = "LIMIT  $starts, $length ";
		$draw         = $this->input->post("draw");
		$search       = $this->input->post('searching');
		$orders       = isset($_POST['order']) ? $_POST['order'] : '';

		$where = "WHERE 1=1 ";
		// $searchingColumn;
		$result = array();
		if (isset($search)) {
			if ($search != '') {
				$where .= " AND (nama_komunitas LIKE '%$search%')";
			}
		}

		if (isset($orders)) {
			if ($orders != '') {
				$order = $orders;
				$order_column = [''];
				$order_clm  = $order_column[$order[0]['column']];
				$order_by   = $order[0]['dir'];
				$where .= " ORDER BY $order_clm $order_by ";
			} else {
				$where .= " ORDER BY nama_komunitas ASC ";
			}
		} else {
			$where .= " ORDER BY nama_komunitas ASC ";
		}
		if (isset($LIMIT)) {
			if ($LIMIT != '') {
				$where .= ' ';
			}
		}
		$index = 1;
		$fetch = $this->db->query("SELECT * from komunitas $where");
		$response = array();
		foreach ($fetch->result() as $rows) {
			array_push($response, array(
				"index" => $index,
				"id" => $rows->id,
				"nama_komunitas" => $rows->nama_komunitas,
				"isChecked" => false,
			));
			$index++;
		}
		$output = array(
			"data"            => $response,

		);
		echo json_encode($output);
	}

	public function fetch_kontak()
	{
		$starts       = $this->input->post("start");
		$length       = $this->input->post("length");
		$LIMIT        = "LIMIT  $starts, $length ";
		$draw         = $this->input->post("draw");
		$search       = $this->input->post('searching');
		$orders       = isset($_POST['order']) ? $_POST['order'] : '';
		$id           = $this->input->post('id');


		$where = "WHERE 1=1 and a.id !='$id'";
		// $searchingColumn;
		$result = array();
		if (isset($search)) {
			if ($search != '') {
				$where .= " AND (a.nama LIKE '%$search%')";
			}
		}

		if (isset($orders)) {
			if ($orders != '') {
				$order = $orders;
				$order_column = [''];
				$order_clm  = $order_column[$order[0]['column']];
				$order_by   = $order[0]['dir'];
				$where .= " ORDER BY $order_clm $order_by ";
			} else {
				$where .= " ORDER BY a.nama ASC ";
			}
		} else {
			$where .= " ORDER BY a.nama ASC ";
		}
		if (isset($LIMIT)) {
			if ($LIMIT != '') {
				$where .= ' ' . $LIMIT;
			}
		}
		$index = 1;
		$fetch = $this->db->query("SELECT a.nama,a.id,a.picture,a.username from user a $where");
		foreach ($fetch->result() as $rows) {
			$sub_array = array();
			$sub_array[] = $index;
			$sub_array[] = $rows->id;
			$sub_array[] = $rows->nama;
			$sub_array[] = $rows->picture;
			$sub_array[] = $rows->username;
			$result[]      = $sub_array;
			$index++;
		}
		$output = array(
			"data"            =>     $result,

		);
		echo json_encode($output);
	}

	public function broadcastMessage()
	{
		if ($this->input->post('arrayId') != "") {
			header('Content-Type: application/json');
			$id = str_replace(']', '', str_replace('[', '', str_replace('"', '', $this->input->post('arrayId'))));
			$pesan = $this->input->post('pesan');
			$array = explode(',', $id);
			$response = array();
			$title = "Broadcast Message";
			$body = $pesan;
			$screen = "list_trx";
			$count = count($array);
			if ($count > 1) {
				foreach ($array as $key => $value) {
					$data = $this->db->query("select * from komunitas_followers where id_komunitas='$key'");
					if ($data->num_rows() > 0) {
						foreach ($data->result() as $rows) {
							$token = $this->db->get_where('user', array('id' => $rows->id_user))->row()->token;
							$this->SendNotif_model->send_notif(get_setting('server_fcm_app'), $token, $title, $body, $screen);
							$ins = array("user_id" => $rows->id_user, "pesan" => $pesan, "status" => 0, "created" => date('Y-m-d H:i:s'), "deleted" => 0);
							$this->db->insert('notifikasi', $ins);
						}
					}
					array_push($response, array(
						"id" => $key,
					));
				}
			} else if ($count == 1) {
				$data = $this->db->query("select * from komunitas_followers where id_komunitas='$id'");
				if ($data->num_rows() > 0) {
					foreach ($data->result() as $rows) {
						$token = $this->db->get_where('user', array('id' => $rows->id_user))->row()->token;
						$this->SendNotif_model->send_notif(get_setting('server_fcm_app'), $token, $title, $body, $screen);
						$ins = array("user_id" => $rows->id_user, "pesan" => $pesan, "status" => 0, "created" => date('Y-m-d H:i:s'), "deleted" => 0);
						$this->db->insert('notifikasi', $ins);
					}
				}
				array_push($response, array(
					"idnya" => $id,
					"log" => $this->db->last_query()
				));
			}

			echo json_encode(array(
				"status" => "200",
				"pesan" => $pesan,
				"komunitas" => $id,
				"jumlah" => $count,
				"response" => $response
			));
		}
	}

	public function getMembantu()
	{
		$user   = $this->input->post("idUser");
		$data = $this->db->query("SELECT a.id,a.waktu,a.keterangan,a.lokasi,a.user_id,a.user_penolong,a.status from cari_bantuan a where a.user_penolong='$user' and not exists(SELECT b.user_penolong from cari_bantuan_his b where b.user_penolong='$user' and b.id_cari=a.id)");
		if ($data->num_rows() > 0) {
			$response = array();
			foreach ($data->result() as $rows) {
				array_push($response, array(
					"id" => $rows->id,
					"userPemohon" => $this->db->get_where('user', array('id' => $rows->user_id))->row()->nama,
					"tanggal" => formatTanggal(substr($rows->waktu, 0, 10)),
					"jam" => substr($rows->waktu, 11, 8),
					"keterangan" => $rows->keterangan,
					"alamat" => $rows->lokasi,
					"status" => $rows->status,

				));
			}
			echo json_encode(array(
				"status" => 200,
				"values" => $response
			));
		} else {
			echo json_encode(array(
				"status" => 404,
				"message" => "Data tidak ditemukan"
			));
		}
	}

	public function getBantuan()
	{
		$user   = $this->input->post("idUser");
		$data = $this->db->query("SELECT a.id,a.waktu,a.keterangan,a.lokasi,a.user_id,a.user_penolong,a.status from cari_bantuan a where a.user_id='$user'");
		if ($data->num_rows() > 0) {
			$response = array();
			foreach ($data->result() as $rows) {
				array_push($response, array(
					"id" => $rows->id,
					"userPemohon" => $this->db->get_where('user', array('id' => $rows->user_id))->row()->nama,
					"tanggal" => formatTanggal(substr($rows->waktu, 0, 10)),
					"jam" => substr($rows->waktu, 11, 8),
					"keterangan" => $rows->keterangan,
					"alamat" => $rows->lokasi,
					"status" => $rows->status,
					"pembantu" => $this->db->get_where('user', array('id' => $rows->user_penolong))->row()->nama == "" ? "" : $this->db->get_where('user', array('id' => $rows->user_penolong))->row()->nama,
					"sender" => $rows->user_id,
					"recipient" => $rows->user_penolong,
				));
			}
			echo json_encode(array(
				"status" => 200,
				"values" => $response
			));
		} else {
			echo json_encode(array(
				"status" => 404,
				"message" => "Data tidak ditemukan"
			));
		}
	}
}
