<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require APPPATH . 'libraries/REST_Controller.php';
class Bug extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('BugModel');
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

    //GetAllBugs list
    public function GetAllBugs_get()
    {
        $this->GetAllBugs_validator($this->get());
    }
    public function GetAllBugs_validator($params)
    {
        if (isset($_GET['token']) and $this->CommonModel->validate_token($_GET['token'])) {
            $this->form_validation->set_data($params);
            $this->form_validation->set_rules('last_login_dt', 'last_login_dt', 'trim');
            $this->form_validation->set_rules('popular', 'popular', 'trim|is_natural|in_list[0,1]', array('in_list' => 'The %s must be either 0 or 1'));
            if ($this->form_validation->run() == false) {
                $validations = $this->form_validation->error_array();
                if (!empty($validations)) {
                    foreach ($validations as $key => $error) {
                        $this->response(['status' => false, 'response' => $error], REST_Controller::HTTP_OK);
                    }
                } else {
                    $this->GetAllBugs($params);
                }
            } else {
                $this->GetAllBugs($params);
            }
        } else {
            $this->response(['status' => false, 'response' => 'Invalid token.'], REST_Controller::HTTP_OK);
        }
    }
    public function GetAllBugs($params)
    {
        $result['Message'] = "Success";
        $result['Data'] = $this->BugModel->GetAllBugs($params);
        $this->response(['status' => true, 'response' => $result], REST_Controller::HTTP_OK);

    }
//Set User Preference
    public function SetUserPreference_post()
    {
        $this->SetUserPreference($this->post());
    }
    public function SetUserPreference_validator()
    {
      
    }
    public function SetUserPreference($params)
    {

        $bugIds = $params['BugIds'];
        $existingBugs = array_column($this->BugModel->GetUserPreferences($params['UserId']), 'BugId');
        if (count($existingBugs) > 0) {
            $newBugs = array_diff($bugIds, $existingBugs);
            $commonBugs = array_intersect($bugIds, $existingBugs);
            $bugsToUpdate = array_diff($existingBugs, $commonBugs);
            if (count($bugsToUpdate) > 0) {
                $bugsInInt = array_map('intval', array_values($bugsToUpdate));
                $this->BugModel->SetBugToInActive($params['UserId'], $bugsInInt);
            }
        } else {
            $newBugs = $bugIds;
        }

        $preferences = array();
        if (count($newBugs) > 0) {
            $newBugsChunks = array_chunk($newBugs, 1);
            foreach ($newBugsChunks as $x) {
                $p['UserId'] = $params['UserId'];
                $p['BugId'] = $x[0];
                $p['IsActive'] = 1;
                $p['CreatedDate'] = date('Y-m-d H:i:s');
                array_push($preferences, $p);
            }

            $this->BugModel->SetUserPrefernce($preferences);}
        $result['Message'] = "Success";
        $this->response(['status' => true, 'response' => $result], REST_Controller::HTTP_OK);

    }

    //GetUserPreferences
    public function GetUserPreferences_get()
    {
        $this->GetUserPreferences($this->get());
    }
    public function GetUserPreferences($params)
    {
        
            $data['Data'] = $this->BugModel->GetUserPreferences($params['UserId']);
            if ($data['Data']) {
                
                $this->response(['status' => true, 'response' => $data], REST_Controller::HTTP_OK);
            } else {
                $this->response(['status' => false, 'error' => 'sorry no Preferences found.'], REST_Controller::HTTP_OK);
            }
        
        
    }
}
