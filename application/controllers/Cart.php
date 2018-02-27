<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require APPPATH . 'libraries/REST_Controller.php';
class Cart extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('CartModel');
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
    public function AddJuiceToCart_post()
    {
        $this->AddJuiceToCart($this->post());
    }
    public function AddJuiceToCart($params)
    {
        $cart['UserId'] = $params['UserId'];
        $cart['JuiceId'] = $params['JuiceId'];
        $cart['Quantity'] = 1;
        $cart['Isactive'] = 1;
        $result['Data'] = $this->CartModel->AddJuiceToCart($cart);

        if ($result['Data']) {
            $this->response(['status' => true, 'response' => 'Juice Added to Cart'], REST_Controller::HTTP_OK);
        } else {
            $this->response(['status' => false, 'error' => 'sorry no details found.'], REST_Controller::HTTP_OK);
        }
    }
    public function GetCart_get()
    {
        $this->GetCart($this->get());
    }
    public function GetCart($params)
    {
        $result['Data'] = $this->CartModel->GetCart($params['UserId']);
        $this->response(['status' => true, 'response' => $result], REST_Controller::HTTP_OK);
    }
}
