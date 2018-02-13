<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}
require APPPATH . 'libraries/REST_Controller.php';
class API extends REST_Controller {
	public function __construct() {
		parent::__construct();
    $this->load->model('API_model');
    $this->load->helper('url');
	}
	public function index(){
   	$this->validator->set_data(array());
    $this->validator->set_rules(array(array('field' => 'title','label' => 'Title','rules' => 'required')));
    if($this->validator->run() ==  false){
      var_dump('Validation Failed');
      $errors = array('validation_error'  => $this->validator->error_array());
    }
    var_dump($errors);
    var_dump(validation_errors());
  }
}