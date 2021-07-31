<?php
class Api extends MY_Controller
{
	function __construct()
	{
		parent::__construct();
		// ini_set('display_errors', 0);
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

	public function buatKomunitas(){
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
		$insert = $this->db->insert('komunitas',$data);
		if($insert){
			echo json_encode(array(
				"status"=>"200",
				"message"=>"Berhasil membuat komunitas."
			));
		}else{
			echo json_encode(array(
				"status"=>403,
				"message"=>"Terjadi kesalahan mohon coba kembali."
			));
		}
	}

	public function getChatRoom()
	{
		$filter = $this->input->post('filter');
		$idUser = $this->input->post('idUser');
		$where ="WHERE 1=1 AND idTo ='$idUser' or idFrom ='$idUser' ";
		if($filter == ""){
			$where .="";
		}else{
			$where .=" AND (nameTo like '%$filter%') ";
		}
		$where .=" ORDER BY created_at ASC";
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
				));
			}
			echo json_encode(array(
				"status" => "200",
				"values" => $response,
			));
		} else {
			echo json_encode(array(
				"status" => "404",
				"message" => "Kotak pesan masih kosong",
			));
		}
	}


	public function getChatFromSender()
	{
		$idUser = $this->input->post('idUser');

		$data = $this->db->query("SELECT user.nama,user.id,user.picture,msh.idTo,msh.idFrom
		 from user user join message_user msh on msh.idTo=user.id");
	}

	public function getChatFromRecipient()
	{
		$idUser = $this->input->post('idUser');

		$data = $this->db->query("SELECT user.nama,user.id,user.picture,msh.idTo,msh.idFrom
		 from user user join message_user msh on msh.idFrom=user.id");
	}

	public function getAlbum()
	{
		$id = $this->input->post('id');
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
				"pengikut" => (String)$this->db->get_where('komunitas_followers', array('id_komunitas' => $idKomunitas))->num_rows(),
				"post" => (String)$this->db->get_where('komunitas_album', array('id_komunitas' => $idKomunitas))->num_rows(),
				"lokasi" => $data->row()->lokasi,
				"cover" => $data->row()->cover,
				"tentang" => $data->row()->tentang,
				"contact" => $data->row()->contact,
				"admin"=>$data->row()->admin,
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

	public function uploadAlbum(){
		$id = $_POST['id'];
		$image = $_POST['image'];
		$name = $_POST['name'];
		$folderPath = "./image/" . $name;
		$realImage = base64_decode($image);
		$files = file_put_contents("./image/" . $name, $realImage);
		$data = array(
			"id_komunitas"=>$id,
			"foto" => $name,
		);
		$this->db->insert('komunitas_album',$data);
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
					$sub_array[] = "Hallo " . $rows->nama . ", " . $rows->pesan;
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
}