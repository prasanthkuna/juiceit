<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}
require APPPATH . 'libraries/REST_Controller.php';
class User extends REST_Controller {
	public function __construct() {
		parent::__construct();
    $this->load->model('User_model');
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

  //RegisterUser functions
  public function RegisterUser_post() {
    $this->RegisterUser_validator($this->post());
  }
  public function RegisterUser_validator($params) {
    $this->form_validation->set_data($params);
    $this->form_validation->set_rules('name', 'name','trim|required');
    $this->form_validation->set_rules('mobile', 'mobile','trim|required|is_natural_no_zero|exact_length[10]|is_unique[user.mobile]',array('is_natural_no_zero'=>'Mobile number must have digits only','exact_length'=>'mobile number must be 10 digits long','is_unique'=>'mobile number already existed'));
    $this->form_validation->set_rules('password', 'password','trim|required');
    $this->form_validation->set_rules('password_conf', 'confirm password ','trim|required|matches[password]');
    $this->form_validation->set_rules('email', 'email','trim|required|valid_email|is_unique[user.email]');
    if ($this->form_validation->run() == FALSE){
      foreach($this->form_validation->error_array() as $key => $error) {
        $this->response(['status'=>FALSE,'response'=>$error],REST_Controller::HTTP_OK);
      }
    }
    else{
      $this->RegisterUser($params);
    }
  }
  public function RegisterUser($params){
    $user['name'] = $params['name'];
    $user['mobile'] = $params['mobile'];
    $user['password'] = md5($params['password']);
    $user['email'] = $params['email'];
    $user['created_date'] = date('Y-m-d h:i:s');
    $user['status'] = 1;
    $user['token'] = $this->Common_model->generateToken();

    if ($this->User_model->RegisterUser($user)) {
      $this->response(['status'=>TRUE,'response'=>'User Registered successfully.'],REST_Controller::HTTP_OK);
    }
    else{
      $this->response(['status'=>FALSE,'response'=>'Sorry! User not Registered.'],REST_Controller::HTTP_OK);
    }
  }

  //AuthenticateUser functions
  public function AuthenticateUser_post(){
    $this->AuthenticateUser_validator($this->post());
  }
  public function AuthenticateUser_validator($params){
    $this->form_validation->set_data($params);
    $this->form_validation->set_rules('mobile', 'mobile','trim|required|is_natural_no_zero|exact_length[10]',array('is_natural_no_zero'=>'Mobile number must have digits only','exact_length'=>'mobile number must be 10 digits long'));
    $this->form_validation->set_rules('password', 'password','trim|required');
    if ($this->form_validation->run() == FALSE){
      foreach($this->form_validation->error_array() as $key => $error) {
        $this->response(['status'=>FALSE,'response'=>$error],REST_Controller::HTTP_OK);
      }
    }
    else{
      $this->AuthenticateUser($params);
    }
  }
  public function AuthenticateUser($params){
    $params['password'] = md5($params['password']);
    $data = $this->User_model->AuthenticateUser($params);
    if ($data['user']) {
      if(!$data['user']['token']){
        $data['user']['token'] = $this->Common_model->update_token($data['user']['id']);
      }
      $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
    }
    else{
      $this->response(['status'=>FALSE,'response'=>'Name and password not valid.'],REST_Controller::HTTP_OK);
    }
  }

  //users list
  public function users_get(){
    $this->users_validator($this->get());
  }
  public function users_validator($params){
    if(isset($params['token']) and $this->Common_model->validate_token($params['token'])){
      $this->form_validation->set_data($params);
      $this->form_validation->set_rules('last_login_dt', 'last_login_dt', 'trim'); 
      $this->form_validation->set_rules('name', 'name', 'trim|alpha_numeric_spaces'); 
      $this->form_validation->set_rules('email', 'email', 'trim|valid_email'); 
      $this->form_validation->set_rules('created_date', 'created_date', 'trim'); 
      $this->form_validation->set_rules('modified_date', 'modified_date', 'trim'); 
      $this->form_validation->set_rules('mobile', 'mobile','trim|is_natural_no_zero|exact_length[10]',array('is_natural_no_zero'=>'Mobile number must have digits only','exact_length'=>'mobile number must be 10 digits long'));
      $this->form_validation->set_rules('status','status','trim|is_natural|in_list[0,1]',array('is_natural'=>'status must be a number','in_list'=>'%s must be either 0 or 1'));
      if ($this->form_validation->run() == FALSE){
        $validations = $this->form_validation->error_array();
        if(!empty(array_filter($validations))) {
          foreach($validations as $key => $error) {
            $this->response(['status'=>FALSE,'response'=>$error],REST_Controller::HTTP_OK);
          }
        }
        else {
          $this->users($params);
        }
      }
      else{
        $this->users($params);
      }
    }
    else {
      $this->response(['status'=>FALSE,'response'=>'Invalid token.'],REST_Controller::HTTP_OK);
    }
  }
  public function users($params){
    $data['users'] = $this->User_model->users($params);
    if($data['users']) {
      $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
    }
    else{
      $this->response(['status'=>FALSE,'error'=>'sorry no users found.'],REST_Controller::HTTP_OK);
    }
  }

}