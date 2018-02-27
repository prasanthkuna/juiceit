<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require APPPATH . 'libraries/REST_Controller.php';
class Order extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('OrderModel');
        $this->load->model('CommonModel');
        $this->load->library('form_validation');
        $this->load->helper('url');
    }
    public function index()
    {
        $this->form_validation->set_data(array());
        $this->form_validation->set_rules(array(array('field' => 'title', 'label' => 'Title', 'rules' => 'required')));
        if ($this->form_validation->run() == false) {
            //var_dump('Validation Failed');
            $errors = array('validation_error' => $this->form_validation->error_array());
        }
        var_dump($errors);
        print '<br />';
        var_dump(validation_errors());
        //$this->response(['status'=>FALSE,'error'=>'Invalid method'],REST_Controller::HTTP_OK);
    }
    public function PostOrder_post()
    {
        $this->PostOrder($this->post());
    }
    public function PostOrder($params)
    {
        $data['Data'] = $this->OrderModel->PostOrder($params['UserId']);
        if ($data['Data']) {
            $this->response(['status'=>TRUE,'response'=>'Order placed Successfully'],REST_Controller::HTTP_OK);
          }
          else{
            $this->response(['status'=>FALSE,'response'=>'Order failed.'],REST_Controller::HTTP_OK);
          }

    }
}