<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}
require APPPATH . 'libraries/REST_Controller.php';
class Resource extends REST_Controller {
	public function __construct() {
		parent::__construct();
    $this->load->model('Resource_model');
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
  //resources list
  public function resources_get(){
    $this->resources_validator($this->get());
  }
  public function resources_validator($params){
    if(isset($params['token']) and $this->Common_model->validate_token($params['token'])){
      $this->form_validation->set_data($params);
      $this->form_validation->set_rules('last_login_dt', 'last_login_dt', 'trim'); 
      $this->form_validation->set_rules('name', 'name', 'trim}alpha_numeric_spaces'); 
      if ($this->form_validation->run() == FALSE){
        $validations = $this->form_validation->error_array();
        if(!empty(array_filter($validations))) {
          foreach($validations as $key => $error) {
            $this->response(['status'=>FALSE,'response'=>$error],REST_Controller::HTTP_OK);
          }
        }
        else {
          $this->resources($params);
        }
      }
      else{
        $this->resources($params);
      }
    }
    else {
      $this->response(['status'=>FALSE,'response'=>'Invalid token.'],REST_Controller::HTTP_OK);
    }
  }
  public function resources($params){
    $data['resources'] = $this->Resource_model->resources($params);
    if($data['resources']) {
      $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
    }
    else{
      $this->response(['status'=>FALSE,'error'=>'sorry no resources found.'],REST_Controller::HTTP_OK);
    }
  }
}