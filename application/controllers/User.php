<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require APPPATH . 'libraries/REST_Controller.php';
class User extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('UserModel');
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

    //RegisterUser functions
    public function RegisterUser_post()
    {
        $this->RegisterUser_validator($this->post());
    }
    public function RegisterUser_validator($params)
    {
        $this->form_validation->set_data($params);
        $this->form_validation->set_rules('Name', 'Name', 'trim|required');
        $this->form_validation->set_rules('Mobile', 'Mobile', 'trim|required|regex_match[/^[1-9][0-9]{9}$/]', array('regex_match' => 'mobile number must be 10 numeric digits long'));
        $this->form_validation->set_rules('Password', 'Password', 'trim|required');
        $this->form_validation->set_rules('Email', 'Email', 'trim|required|valid_email');
        if ($this->form_validation->run() == false) {
            foreach ($this->form_validation->error_array() as $key => $error) {
                $this->response(['status' => false, 'response' => $error], REST_Controller::HTTP_OK);
            }
        } else {
            $this->RegisterUser($params);
        }
    }
    public function RegisterUser($params)
    {
        if ($this->UserModel->DoesEmailExists($params['Email'])) {
            $this->response(['status' => false, 'response' => 'Email already exists.'], REST_Controller::HTTP_OK);
        }
        $user['Name'] = $params['Name'];
        $user['Mobile'] = $params['Mobile'];
        $user['Password'] = md5($params['Password']);
        $user['Email'] = $params['Email'];
        $user['CreatedDate'] = date('Y-m-d H:i:s');
        $user['IsDeleted'] = 0;
        $user['Token'] = $this->CommonModel->generateToken();

        if ($this->UserModel->RegisterUser($user)) {
            $this->response(['status' => true, 'response' => 'User Registered successfully.'], REST_Controller::HTTP_OK);
        } else {
            $this->response(['status' => false, 'response' => 'Sorry! User not Registered.'], REST_Controller::HTTP_OK);
        }
    }

    //AuthenticateUser functions
    public function AuthenticateUser_post()
    {
        $this->AuthenticateUser_validator($this->post());
    }
    public function AuthenticateUser_validator($params)
    {
        $this->form_validation->set_data($params);
        $this->form_validation->set_rules('Mobile', 'Mobile', 'trim|required|regex_match[/^[1-9][0-9]{9}$/]', array('regex_match' => 'mobile number must be 10 numeric digits long'));
        $this->form_validation->set_rules('password', 'password', 'trim|required');
        if ($this->form_validation->run() == false) {
            foreach ($this->form_validation->error_array() as $key => $error) {
                $this->response(['status' => false, 'response' => $error], REST_Controller::HTTP_OK);
            }
        } else {
            $this->AuthenticateUser($params);
        }
    }
    public function AuthenticateUser($params)
    {
        $params['password'] = md5($params['password']);
        $data = $this->UserModel->AuthenticateUser($params);
        if ($data['user']) {
            if (!$data['user']['token']) {
                $data['user']['token'] = $this->Common_model->update_token($data['user']['id']);
            }
            $this->response(['status' => true, 'response' => $data], REST_Controller::HTTP_OK);
        } else {
            $this->response(['status' => false, 'response' => 'Name and password not valid.'], REST_Controller::HTTP_OK);
        }
    }

    //users list
    public function users_get()
    {
        $this->users_validator($this->get());
    }
    public function users_validator($params)
    {
        if (isset($params['token']) and $this->Common_model->validate_token($params['token'])) {
            $this->form_validation->set_data($params);
            $this->form_validation->set_rules('last_login_dt', 'last_login_dt', 'trim');
            $this->form_validation->set_rules('name', 'name', 'trim|alpha_numeric_spaces');
            $this->form_validation->set_rules('email', 'email', 'trim|valid_email');
            $this->form_validation->set_rules('created_date', 'created_date', 'trim');
            $this->form_validation->set_rules('modified_date', 'modified_date', 'trim');
            $this->form_validation->set_rules('mobile', 'mobile', 'trim|is_natural_no_zero|exact_length[10]', array('is_natural_no_zero' => 'Mobile number must have digits only', 'exact_length' => 'mobile number must be 10 digits long'));
            $this->form_validation->set_rules('status', 'status', 'trim|is_natural|in_list[0,1]', array('is_natural' => 'status must be a number', 'in_list' => '%s must be either 0 or 1'));
            if ($this->form_validation->run() == false) {
                $validations = $this->form_validation->error_array();
                if (!empty(array_filter($validations))) {
                    foreach ($validations as $key => $error) {
                        $this->response(['status' => false, 'response' => $error], REST_Controller::HTTP_OK);
                    }
                } else {
                    $this->users($params);
                }
            } else {
                $this->users($params);
            }
        } else {
            $this->response(['status' => false, 'response' => 'Invalid token.'], REST_Controller::HTTP_OK);
        }
    }
    public function users($params)
    {
        $data['users'] = $this->UserModel->users($params);
        if ($data['users']) {
            $this->response(['status' => true, 'response' => $data], REST_Controller::HTTP_OK);
        } else {
            $this->response(['status' => false, 'error' => 'sorry no users found.'], REST_Controller::HTTP_OK);
        }
    }

    // Verify mobile number
    public function IsMobileExists_post()
    {
        $this->IsMobileExists($this->post());
    }
    public function IsMobileExists_validator($params)
    {
        $this->form_validation->set_data($params);
        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|exact_length[10]|', array('exact_length' => 'mobile number must be 10 digits long'));
        if ($this->form_validation->run() == false) {
            foreach ($this->form_validation->error_array() as $key => $error) {
                $this->response(['status' => false, 'response' => $error], REST_Controller::HTTP_OK);
            }
        } else {
            $this->IsMobileExists($params);
        }
    }
    public function IsMobileExists($params)
    {
        $mobile = $params['Mobile'];

        if ($this->UserModel->IsMobileExists($mobile)) {
            $this->response(['status' => false, 'response' => 'Mobile Number Already Exists.'], REST_Controller::HTTP_OK);
        } else {
            $result['VerificationCode'] = $this->UserModel->InsertVerificationCode($mobile);
            $this->response(['status' => true, 'response' => $result], REST_Controller::HTTP_OK);
        }
    }

}
