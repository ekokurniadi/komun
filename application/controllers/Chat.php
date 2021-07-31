<?php
class Chat extends MY_Controller{
    public function index(){
        $data['user'] =2; 
        $this->load->view('chat',$data);
    }

    public function detail(){
        $param = $_GET['index'];
        $this->load->view('details',$param);
    }
}
?>