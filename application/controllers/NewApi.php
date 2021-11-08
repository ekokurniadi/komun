<?php
class Newapi extends MY_Controller
{
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
}
