<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}
require APPPATH . 'libraries/REST_Controller.php';
class Bug extends REST_Controller {
	public function __construct() {
		parent::__construct();
    $this->load->model('Bug_model');
    $this->load->model('Common_model');
    $this->load->library('form_validation');
    $this->load->helper('url');
	}
	public function index(){
		$this->form_validation->set_data(array());
    $this->form_validation->set_rules(array(array('field' =>'title','label' =>'Title','rules' =>'required')));
    if($this->form_validation->run() ==  false){
      //var_dump('Validation Failed');
      $errors = array('validation_error'  => $this->form_validation->error_array());
    }
    var_dump($errors);
    print '<br />';
    var_dump(validation_errors());
    //$this->response(['status'=>FALSE,'error'=>'Invalid method'],REST_Controller::HTTP_OK);
  }
  
  //GetAllBugs list
  public function GetAllBugs_get(){
    $this->GetAllBugs_validator($this->get());
  }
  public function GetAllBugs_validator($params){
    if(isset($params['token']) and $this->Common_model->validate_token($params['token'])){
      $this->form_validation->set_data($params);
      $this->form_validation->set_rules('last_login_dt', 'last_login_dt', 'trim'); 
      $this->form_validation->set_rules('popular','popular','trim|is_natural|in_list[0,1]',array('in_list'=>'The %s must be either 0 or 1'));
      if($this->form_validation->run() == FALSE) {
      	$validations = $this->form_validation->error_array();
      	if(!empty($validations)) {
	        foreach($validations as $key => $error) {
	          $this->response(['status'=>FALSE,'response'=>$error],REST_Controller::HTTP_OK);
	        }
      	}
      	else {
      		$this->GetAllBugs($params);
      	}
      }
      else {
        $this->GetAllBugs($params);
      }
    }
    else {
      $this->response(['status'=>FALSE,'response'=>'Invalid token.'],REST_Controller::HTTP_OK);
    }
  }
  public function GetAllBugs($params){
    $data['GetAllBugs'] = $this->Bug_model->GetAllBugs($params);
    if($data['GetAllBugs']) {
      $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
    }
    else{
      $this->response(['status'=>FALSE,'response'=>'sorry no data found.'],REST_Controller::HTTP_OK);
    }
  }

  //GetUserPreferences
  public function GetUserPreferences_get(){
    $this->GetUserPreferences($this->get());
  }
  public function GetUserPreferences($params){
    if(isset($params['token']) and $this->Common_model->validate_token($params['token'])){
      $user_id = $this->User_model->get_user_by_token($params['token']);
      $data['preference'] = $this->Bug_model->get_preference($user_id);
      if($data['preference']) {
        $data['preference'] = ($data['preference']) ? json_encode(explode(',',$data['preference'])): array();
        $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
      }
      else{
        $this->response(['status'=>FALSE,'error'=>'sorry no Preferences found.'],REST_Controller::HTTP_OK);
      }
    }
    else {
      $this->response(['status'=>FALSE,'response'=>'Invalid token.'],REST_Controller::HTTP_OK);
    }
  }
}