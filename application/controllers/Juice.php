<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}
require APPPATH . 'libraries/REST_Controller.php';
class Juice extends REST_Controller {
	public function __construct() {
		parent::__construct();
    $this->load->model('JuiceModel');
    $this->load->model('CommonModel');
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
    if(isset($params['token']) and $this->CommonModel->validate_token($params['token'])){
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
    $data['Data'] = $this->JuiceModel->GetJuices($params['UserId']);
    if($data['Data']) {
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
    
      $data['Data'] = $this->JuiceModel->GetFavourites($params['UserId']);
      if($data['Data']) {
        $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
      }
      else{
        $this->response(['status'=>FALSE,'error'=>'sorry no Favourites found.'],REST_Controller::HTTP_OK);
      }
    
  }
  public function PostFavourite_post()
  {
    $this->PostFavourite($this->post());
  }
  public function PostFavourite($params)
  {
    $favourite['UserId'] = $params['UserId'];
    $favourite['JuiceId'] = $params['JuiceId'];
    $favourite['IsActive'] = 1;
    if ($this->JuiceModel->PostFavourite($favourite)) {
      $this->response(['status'=>TRUE,'response'=>'favourited successfully.'],REST_Controller::HTTP_OK);
    }
    else{
      $this->response(['status'=>FALSE,'response'=>'Error.'],REST_Controller::HTTP_OK);
    }

  }
  //Get Juice Details
  public function GetJuiceDetails_get()
  {
    $this->GetJuiceDetails($this->get());
  }
  public function GetJuiceDetails($params)
  {
    $result['Data'] = $this->JuiceModel->GetJuiceDetails($params['JuiceId']);
    if($result['Data']) {
      $this->response(['status'=>TRUE,'response'=>$result],REST_Controller::HTTP_OK);
    }
    else{
      $this->response(['status'=>FALSE,'error'=>'sorry no details found.'],REST_Controller::HTTP_OK);
    }

  }
}