<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}
require APPPATH . 'libraries/REST_Controller.php';
class API extends REST_Controller {
	public function __construct() {
		parent::__construct();
    $this->load->model('API_model');
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

  //register functions
  public function register_get() {
    $this->register_validator($this->get());
  }
  public function register_post() {
    $this->register_validator($this->post());
  }
  public function register_put() {
    $this->register_validator($this->put());
  }
  public function register_validator($params) {
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
      $this->register($params);
    }
  }
  public function register($params){
    $user['name'] = $params['name'];
    $user['mobile'] = $params['mobile'];
    $user['password'] = md5($params['password']);
    $user['email'] = $params['email'];
    $user['created_date'] = date('Y-m-d h:i:s');
    $user['status'] = 1;
    $user['token'] = $this->API_model->generateToken();

    if ($this->API_model->register($user)) {
      $this->response(['status'=>TRUE,'response'=>'User registered successfully.'],REST_Controller::HTTP_OK);
    }
    else{
      $this->response(['status'=>FALSE,'response'=>'Sorry! User not registered.'],REST_Controller::HTTP_OK);
    }
  }

  //login functions
  public function login_get(){
    $this->login_validator($this->get());
  }
  public function login_post(){
    $this->login_validator($this->post());
  }
  public function login_validator($params){
    $this->form_validation->set_data($params);
    $this->form_validation->set_rules('mobile', 'mobile','trim|required|is_natural_no_zero|exact_length[10]',array('is_natural_no_zero'=>'Mobile number must have digits only','exact_length'=>'mobile number must be 10 digits long'));
    $this->form_validation->set_rules('password', 'password','trim|required');
    if ($this->form_validation->run() == FALSE){
      foreach($this->form_validation->error_array() as $key => $error) {
        $this->response(['status'=>FALSE,'response'=>$error],REST_Controller::HTTP_OK);
      }
    }
    else{
      $this->login($params);
    }
  }
  public function login($params){
    $params['password'] = md5($params['password']);
    $data = $this->API_model->login($params);
    if ($data['user']) {
      if(!$data['user']['token']){
        $data['user']['token'] = $this->API_model->update_token($data['user']['id']);
      }
      $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
    }
    else{
      $this->response(['status'=>FALSE,'response'=>'Name and password not valid.'],REST_Controller::HTTP_OK);
    }
  }

  //validate token
  /*public function validate_token($token){
    if($this->API_model->validate_token($token)){
      $this->form_validation->set_message('validate_token', 'Invalid %s.please use valid token');
      return FALSE;
    }
    else{
      return TRUE;
    }
  }*/
  public function validate_token($token){
    return $this->API_model->validate_token($token);
  }

  //bugs list
  public function bugs_get(){
    $this->bugs_validator($this->get());
  }
  public function bugs_post(){
    $this->bugs_validator($this->post());
  }
  public function bugs_validator($params){
    if(isset($params['token']) and $this->validate_token($params['token'])){
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
      		$this->bugs($params);
      	}
      }
      else {
        $this->bugs($params);
      }
    }
    else {
      $this->response(['status'=>FALSE,'response'=>'Invalid token.'],REST_Controller::HTTP_OK);
    }
  }
  public function bugs($params){
    $data['bugs'] = $this->API_model->bugs($params);
    if($data['bugs']) {
      $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
    }
    else{
      $this->response(['status'=>FALSE,'response'=>'sorry no data found.'],REST_Controller::HTTP_OK);
    }
  }

  //juices list
  public function juices_get(){
    $this->juices_validator($this->get());
  }
  public function juices_post(){
    $this->juices_validator($this->post());
  }
  public function juices_validator($params){
    if(isset($params['token']) and $this->validate_token($params['token'])){
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
          $this->juices($params);
        }
      }
      else{
        $this->juices($params);
      }
    }
    else {
      $this->response(['status'=>FALSE,'response'=>'Invalid token.'],REST_Controller::HTTP_OK);
    }
  }
  public function juices($params){
    $data['juices'] = $this->API_model->juices($params);
    if($data['juices']) {
      $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
    }
    else{
      $this->response(['status'=>FALSE,'error'=>'sorry no juices found.'],REST_Controller::HTTP_OK);
    }
  }

  //users list
  public function users_get(){
    $this->users_validator($this->get());
  }
  public function users_post(){
    $this->users_validator($this->post());
  }
  public function users_validator($params){
    if(isset($params['token']) and $this->validate_token($params['token'])){
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
    $data['users'] = $this->API_model->users($params);
    if($data['users']) {
      $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
    }
    else{
      $this->response(['status'=>FALSE,'error'=>'sorry no users found.'],REST_Controller::HTTP_OK);
    }
  }

  //resources list
  public function resources_get(){
    $this->resources_validator($this->get());
  }
  public function resources_post(){
    $this->resources_validator($this->post());
  }
  public function resources_validator($params){
    if(isset($params['token']) and $this->validate_token($params['token'])){
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
    $data['resources'] = $this->API_model->resources($params);
    if($data['resources']) {
      $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
    }
    else{
      $this->response(['status'=>FALSE,'error'=>'sorry no resources found.'],REST_Controller::HTTP_OK);
    }
  }

  //payment modes list
  public function payment_modes_get(){
    $this->payment_modes_validator($this->get());
  }
  public function payment_modes_post(){
    $this->payment_modes_validator($this->post());
  }
  public function payment_modes_validator($params){
    if(isset($params['token']) and $this->validate_token($params['token'])){
      $this->form_validation->set_data($params);
      $this->form_validation->set_rules('last_login_dt', 'last_login_dt', 'trim'); 
      $this->form_validation->set_rules('status','status','trim|is_natural|in_list[0,1]',array('is_natural'=>'status must be a number','in_list'=>'%s must be either 0 or 1'));
      if ($this->form_validation->run() == FALSE){
        $validations = $this->form_validation->error_array();
        if(!empty(array_filter($validations))) {
          foreach($validations as $key => $error) {
            $this->response(['status'=>FALSE,'response'=>$error],REST_Controller::HTTP_OK);
          }
        }
        else {
          $this->payment_modes($params);
        }
      }
      else{
        $this->payment_modes($params);
      }
    }
    else {
      $this->response(['status'=>FALSE,'response'=>'Invalid token.'],REST_Controller::HTTP_OK);
    }
  }
  public function payment_modes($params){
    $data['payment_modes'] = $this->API_model->payment_modes($params);
    if($data['payment_modes']) {
      $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
    }
    else{
      $this->response(['status'=>FALSE,'error'=>'sorry no payment modes found.'],REST_Controller::HTTP_OK);
    }
  }

  //mixtures list
  public function mixtures_get(){
    $this->mixtures_validator($this->get());
  }
  public function mixtures_post(){
    $this->mixtures_validator($this->post());
  }
  public function mixtures_validator($params){
    if(isset($params['token']) and $this->validate_token($params['token'])){
      $this->form_validation->set_data($params);
      $this->form_validation->set_rules('last_login_dt', 'last_login_dt', 'trim'); 
      $this->form_validation->set_rules('juice_id','juice_id','trim|is_natural',array('is_natural'=>'juice_id must be a number'));
      $this->form_validation->set_rules('resource_id','resource_id','trim|is_natural',array('is_natural'=>'resource_id must be a number'));
      if ($this->form_validation->run() == FALSE){
        $validations = $this->form_validation->error_array();
        if(!empty(array_filter($validations))) {
          foreach($validations as $key => $error) {
            $this->response(['status'=>FALSE,'response'=>$error],REST_Controller::HTTP_OK);
          }
        }
        else {
          $this->mixtures($params);
        }
      }
      else{
        $this->mixtures($params);
      }
    }
    else {
      $this->response(['status'=>FALSE,'response'=>'Invalid token.'],REST_Controller::HTTP_OK);
    }
  }
  public function mixtures($params){
    $data['mixtures'] = $this->API_model->mixtures($params);
    if($data['mixtures']) {
      $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
    }
    else{
      $this->response(['status'=>FALSE,'error'=>'sorry no mixtures found.'],REST_Controller::HTTP_OK);
    }
  }


  public function bug_juices_get(){
    $this->bug_juices_validator($this->get());
  }
  public function bug_juices_post(){
    $this->bug_juices_validator($this->post());
  }
  public function bug_juices_validator($params){
  	if(isset($params['token']) and $this->validate_token($params['token'])){
	    $this->form_validation->set_data($params);
      $this->form_validation->set_rules('bug','bug','trim|required');
	    if ($this->form_validation->run() == FALSE){
	    	$validations = $this->form_validation->error_array();
        if(!empty(array_filter($validations))) {
          foreach($validations as $key => $error) {
            $this->response(['status'=>FALSE,'response'=>array('error'=>$error)],REST_Controller::HTTP_OK);
          }
        }
        else {
          $this->bug_juices($params);
        }
	    }
	    else{
	      $this->bug_juices($params);
	    }
    }
    else {
      $this->response(['status'=>FALSE,'response'=>'Invalid token.'],REST_Controller::HTTP_OK);
    }
  }
  public function bug_juices($params){
    $params['bug'] = (trim($params['bug'])) ? array_filter(explode(',',trim($params['bug']))) : array();
    if(!empty($params['bug'])) {
      $data['juices'] = $this->API_model->bug_juices($params);
      if ($data['juices']) {
        $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
      }
      else{
        $this->response(['status'=>FALSE,'response'=>'sorry no juices found for this bug'],REST_Controller::HTTP_OK);
      }
    }
    else{
      $this->response(['status'=>FALSE,'response'=>'Invalid bug Ids'],REST_Controller::HTTP_OK);
    }
  }
  /*public function bug_juices($params){
    $params['bug'] = (trim($params['bug'])) ? array_filter(explode(',',trim($params['bug']))) : array();
    if(!empty($params['bug'])) {
      $params['user_id'] = $this->API_model->get_user_by_token($params['token']);

      $params['prefernce'] = $this->API_model->get_preference($params['user_id']);
      $params['prefernce'] = ($params['prefernce']) ? explode(',',$params['prefernce']):array();
      $params['history'] = array_unique(array_filter(array_merge($params['bug'],$params['prefernce'])));
      $params['history'] = (!empty($params['history'])) ? implode(',', $params['history']) :""; 

      $params['preference'] = implode(',', $params['bug']);

      $favourites = $this->API_model->get_bug_juices($params['bug']);
      if($favourites){
        foreach ($favourites as $key => $value) {
          $params['favourite'][] = $value['juice_id'];
        }
      }
      $params['favourite'] = (isset($params['favourite'])) ? implode(',',$params['favourite']):"";
      
      $data['juices'] = $this->API_model->bug_juices($params);
      if ($data['juices']) {
        $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
      }
      else{
        $this->response(['status'=>FALSE,'response'=>'sorry no juices found for this bug'],REST_Controller::HTTP_OK);
      }
    }
    else{
      $this->response(['status'=>FALSE,'response'=>'Invalid bug Ids'],REST_Controller::HTTP_OK);
    }
  }*/

  //favourites
  public function favourites_get(){
    $this->favourites($this->get());
  }
  public function favourites_post(){
    $this->favourites($this->post());
  }
  public function favourites($params){
    if(isset($params['token']) and $this->validate_token($params['token'])){
      $user_id = $this->API_model->get_user_by_token($params['token']);
      $data['favourite'] = $this->API_model->get_favourite($user_id);
      if($data['favourite']) {
        $data['favourite'] = ($data['favourite'])? explode(',',trim($data['favourite'])):array();
        $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
      }
      else{
        $this->response(['status'=>FALSE,'error'=>'sorry no favourites found.'],REST_Controller::HTTP_OK);
      }
    }
    else {
      $this->response(['status'=>FALSE,'response'=>'Invalid token.'],REST_Controller::HTTP_OK);
    }
  }

  //preferences
  public function preferences_get(){
    $this->preferences($this->get());
  }
  public function preferences_post(){
    $this->preferences($this->post());
  }
  public function preferences($params){
    if(isset($params['token']) and $this->validate_token($params['token'])){
      $user_id = $this->API_model->get_user_by_token($params['token']);
      $data['preference'] = $this->API_model->get_preference($user_id);
      if($data['preference']) {
        $data['preference'] = ($data['preference']) ? json_encode(explode(',',$data['preference'])): array();
        $this->response(['status'=>TRUE,'response'=>$data],REST_Controller::HTTP_OK);
      }
      else{
        $this->response(['status'=>FALSE,'error'=>'sorry no preferences found.'],REST_Controller::HTTP_OK);
      }
    }
    else {
      $this->response(['status'=>FALSE,'response'=>'Invalid token.'],REST_Controller::HTTP_OK);
    }
  }
}


  
