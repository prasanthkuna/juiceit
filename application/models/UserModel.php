<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class UserModel extends CI_Model {
  public function __construct() {
    $this->load->database();
    $this->load->helper('string');
  }
  
  //register
  public function RegisterUser($user){
    $this->db->insert('user',$user);
    return $this->db->insert_id();
  }
  //login db query
  public function AuthenticateUser($params) {
    $this->db->select('id,name,mobile,email,token')->where('mobile', $params['mobile'])->where('password', $params['password']);
    $query = $this->db->get('user');
    if($query->num_rows() == 1) {
      $data['user'] = $query->row_array();
      $qry = $this->db->select('id,name')->get('bug');
      if($qry->num_rows()>0) {
        $data['bugs'] = $qry->result_array();
      }
      else{
        $data['bugs'] = array();
      }
      return $data;
    }
    return FALSE;
  }
  //user with token
  public function get_user_by_token($token){
    $this->db->select('id')->where('token',$token)->from('user');
    $qry = $this->db->get();
    if($qry->num_rows()>0){
      return $qry->row_array()['id'];
    }
    return FALSE;
  }

  //Is mobile number exists
  public function IsMobileExists($mobile)
  {
      $this->db->select('mobile')->where('mobile',$mobile)->from('user');
      $qry = $this->db->get();
      if($qry->num_rows()>0)return true;
      else return false;
  }
  public function DoesEmailExists($email)
  {
    $this->db->select('Email')->where('Email', $email)->from('user');
    $qry = $this->db->get();
    if($qry->num_rows()>0)return true;
    else return false; 
  }
  public function InsertVerificationCode($mobile)
  {
    $data['mobile'] = $mobile;
    $data['code'] = mt_rand(100000, 999999);
    $data['expirydate'] = date("Y-m-d H:i:s", strtotime("+1 hour"));
    $this->db->insert('verificationcode',$data);
    return $data['code'];
  }
  //Get users
  public function users($params){
    $this->db->select('id,name,mobile,email,created_date,modified_date,status');
    if(isset($params['last_login_dt'])) { 
      $this->db->where('created_date >=',date('Y-m-d',strtotime($params['last_login_dt'])));
      $this->db->or_where('modified_date >=',date('Y-m-d',strtotime($params['last_login_dt'])));
    } 
    if(isset($params['name']) and trim($params['name'])!='') { 
      $this->db->where('name',$params['name']);
    }
    if(isset($params['mobile']) and trim($params['mobile'])!='') { 
      $this->db->where('mobile',$params['mobile']);
    }
    if(isset($params['email']) and trim($params['email'])!='') { 
      $this->db->where('email',$params['email']);
    }
    if(isset($params['created_date']) and trim($params['created_date'])!='') { 
      $this->db->where('created_date',$params['created_date']);
    }
    if(isset($params['modified_date']) and trim($params['modified_date'])!='') { 
      $this->db->where('modified_date',$params['modified_date']);
    }
    $qry = $this->db->get('user');
    if( $qry->num_rows()>0 ) {
      return $qry->result_array();
    }
    return FALSE;
  }
}