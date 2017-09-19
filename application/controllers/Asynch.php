<?php
  class Asynch extends CI_Controller{

    //ok
    public function __construct(){

      parent::__construct();

      $this->load->library('session');
      $this->load->model('main_model');
      $this->load->helper('url_helper');

    }

    //ok
    public function fetchwindow(){

      $query = $this->main_model->getclients();

      if(!empty($query)){

        $result = $query->result();

        echo '<tr>';
        echo '<td class="table-data name" colspan="'.$query->num_rows().'">'.$this->main_model->getqueuename().'</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td class="table-data total" colspan="'.$query->num_rows().'">'.$this->main_model->getdeployno().'</td>';
        echo '</tr>';

        echo '<tr>';
        foreach ($query->result() as $row){
          echo '<td class="table-data display">'.$row->display_name.'</td>';
        }
        echo '</tr>';

        echo '<tr>';
        foreach ($query->result() as $row){
          echo '<td class="table-data current">'.$row->current.'</td>';
        }
        echo '</tr>';
      }
      else{
        echo '<tr>';
        echo '<td class="table-data name">No Queue Joined</td>';
        echo '</tr>';
      }

    }

    //ok
    public function fetchlist(){

      $search_result = $this->main_model->fetchlist();

      if(!empty($search_result)){

        foreach ($search_result as $row){
          echo '<div class="list-group-item list-selected">';
          echo '<span class="list-qname"><strong>'.$row->queue_name.'</strong></span>';
          echo '</div>';
        }
      }else{
        echo '';
      }
    }

    //ok
    public function leave(){

      echo json_encode(array( 'success' => $this->main_model->leave()));
    }

    //ok
    public function editdetail(){

      echo json_encode($this->main_model->editq($this->input->post('type'), $this->input->post('content')));
    }

    //ok
    public function editdisplay(){

      echo json_encode($this->main_model->editdisplay($this->input->post('content')));
    }

    //ok
    public function status(){

      if($this->main_model->hasqueue()){

        $result = array(
          'success' => TRUE,
          'qnum' => $this->main_model->getcurrentservicenum(),
          'idnum' => $this->main_model->getcurrentid(),
          'qstatus' => $this->main_model->getstatus(),
          'totalsub' => $this->main_model->getdeployno(),
        );
      }else{
        $result = array(
          'success' => FALSE,
        );
      }

      echo json_encode($result);
    }

    //ok
    public function fetchdetail(){

      if($this->main_model->hasqueue()){

        $query_result = $this->main_model->fetchdetail();

        if(!empty($query_result)){
          $result = array(
            'display' => "true",
            'qname' => $query_result->queue_name,
            'code' => $query_result->queue_code,
            'seats' => $query_result->seats_offered,
            'desc' => $query_result->queue_description,
            'req' => $query_result->requirements,
            'venue' => $query_result->venue,
            'rest' => $query_result->queue_restriction,
          );
        }else{
          $result = array(
            'display' => "false",
          );
        }

      }else{

        $result = array(
          'display' => "false",
        );
      }

      echo json_encode($result);
    }

    //ok
    public function fetchdisplay(){
      echo $this->main_model->fetchdisplay($this->input->post('content'));
    }

    //ok
    public function join(){
      echo $this->main_model->join();
    }

    //ok
    //little validation
    public function create(){

      $create = $this->main_model->create();
      $row = $this->main_model->fetchqueue();
      if(!empty($row) && $create == 'CREATED'){
        echo json_encode(array(
          'Result' => $create,
          'qname' => $row->queue_name,
          'code' => $row->queue_code,
          'seats' => $row->seats_offered,
          'desc' => $row->queue_description,
          'req' => $row->requirements,
          'venue' => $row->venue,
          'rest' => $row->queue_restriction,
        ));
      }else{
        echo json_encode(array(
          'Result' => $create,
        ));
      }
    }

    //ok
    public function pause(){
      $this->main_model->setstatus(2);
      echo json_encode($this->main_model->getstatus());
    }

    //ok
    public function resume(){
      $this->main_model->setstatus(1);
      echo json_encode($this->main_model->getstatus());
    }

    //ok
    public function close(){
      $this->main_model->setstatus(3);
      echo json_encode($this->main_model->getstatus());
    }

    //ok
    public function reset(){

      echo json_encode(array('True' => $this->main_model->reset()));
    }

    //ok
    public function next(){

      if($this->main_model->getcurrentservicenum() < $this->main_model->getdeployno()){
        $this->send_notification();
        echo json_encode(array(
          'servicenum' => $this->main_model->incrementcurrent(),
          'idnum' => $this->main_model->incrementid(),
          'qname' => $this->main_model->getqueuename(),
          'max' => FALSE));
      }else{
        echo json_encode(array(
          'servicenum' => $this->main_model->getcurrentservicenum(),
          'idnum' => $this->main_model->getcurrentid(),
          'qname' =>$this->main_model->getqueuename(),
          'max' => TRUE));
      }
    }
    
    //ongoing
    function send ($tokens, $message){
      $url = 'https://fcm.googleapis.com/fcm/send';
      $fields = array(
         'registration_ids' => $tokens,
         'data' => $message
        );

      $headers = array(
        'Authorization:key = AAAAACPXaDU:APA91bEmmrQYSw1Is1yq8s_AM81AouVuB6-fCcYBPjcDSOQtEcGg1kg04W_fxMRLuM3YM3jtId3QQpnqWZxsitdQoL0fprdYwYaMDfooJPxAWxlznzQ0HOX4V7gV0ifseAEaorR4rhUE',
        'Content-Type: application/json'
        );

       $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);  
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
         $result = curl_exec($ch);           
         if ($result === FALSE) {
             die('Curl failed: ' . curl_error($ch));
         }
         curl_close($ch);
         return $result;
    }
    
    //ongoing
    //this
    public function send_notification(){
      
      $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/firebase_credentials.json');
      $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        ->create();
        
      $database = $firebase
        ->getDatabase();
        
      $result = $database
        //->getReference('/token/')
        ->getReference('queue/'+$this->main_model->getqueuename()+'/queuer/'+$this->main_model->getcurrentservicenum())
        ->getValue();
      
      $tokens = array();
      
      foreach ($result as $row){
        $tokens[] = $row;
      }
      
      $message = array("message" => " FCM PUSH NOTIFICATION TEST MESSAGE");
      $message_status = $this->send($tokens, $message);
      echo $message_status;
    }

    //ok
    public function check_session(){

      if(!isset($_SESSION['username'])){
        echo json_encode(array('REDIRECT' => TRUE));
      }else{
        echo json_encode(array('REDIRECT' => FALSE));
      }
    }

  }
?>
