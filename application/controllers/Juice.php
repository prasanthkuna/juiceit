<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}
require APPPATH . 'libraries/REST_Controller.php';
class Juice extends REST_Controller {
	public function __construct() {
		parent::__construct();
    $this->load->model('Juice_model');
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

  //GetJuices list
  public function GetJuices_get(){
    $this->GetJuices_validator($this->get());
  }
  public function GetJuices_validator($params){
    if(isset($params['token']) and $this->Common_model->validate_token($params['token'])){
      $this->form_validation->set_data($params);
      $this->form_validation->set_rules('last_login_dt', 'last_login_dt', 'trim'); 
      $this->form_validation->set_rules('single','single','trim|is_natural|in_list[0,1]',array('is_natural'=>'single must be a number','in_list'=>'%s must be either 0 or 1'));
      if ($this->form_validation->run() == FALSE){
        $validations = $this->form_validation->error_array();
        if(!empty(array_filter($validations))) {
          foreach($validations as $key => $error) {
            $this->response(['status'=>FALSE,'response'=>$error],REST_Controller::HTTP_OK);
          }
        }
        else {
          $this->GetJuices($params);
        }
      }
      else{
        $this->GetJuices($params);
      }
    }
    else {
      $this->response(['status'=>FALSE,'response'=>'Invalid token.'],REST_Controller::HTTP_OK);
    }
  }
  public function GetJuices($params){
    $data['GetJuices'] = $this->Juice_model->GetJuices($params);
    if($data['GetJuices']) {
      $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
    }
    else{
      $this->response(['status'=>FALSE,'error'=>'sorry no Juices found.'],REST_Controller::HTTP_OK);
    }
  }
  //GetFavourites
  public function GetFavourites_get(){
    $this->GetFavourites($this->get());
  }
  public function GetFavourites($params){
    if(isset($params['token']) and $this->Common_model->validate_token($params['token'])){
      $user_id = $this->Juice_model->get_user_by_token($params['token']);
      $data['favourite'] = $this->Juice_model->get_favourite($user_id);
      if($data['favourite']) {
        $data['favourite'] = ($data['favourite'])? explode(',',trim($data['favourite'])):array();
        $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
      }
      else{
        $this->response(['status'=>FALSE,'error'=>'sorry no Favourites found.'],REST_Controller::HTTP_OK);
      }
    }
    else {
      $this->response(['status'=>FALSE,'response'=>'Invalid token.'],REST_Controller::HTTP_OK);
    }
  }
}