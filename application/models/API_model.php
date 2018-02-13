<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class API_model extends CI_Model {
  public function __construct() {
    $this->load->database();
    $this->load->helper('string');
  }

  public function generateToken(){
    $token = random_string('unique',20);
    if($this->db->select('token')->where('token',$token)->get('user')->row_array()['token']){
      $token = $this->generateToken();
    }
    return $token;
  }
  public function validate_token($token){
    $query = $this->db->select('token')->where('token',$token)->get('user');
    if($query->num_rows() > 0) {
      return TRUE;
    }
    return FALSE;
  }
  public function update_token($user_id){
    $token = $this->generateToken();
    $this->db->where('id',$user_id)->update('user',array('token'=>$token));
    if($this->db->affected_rows()){
      return $token;
    }
    return FALSE;
  }

  //register
  public function register($user){
    $this->db->insert('user',$user);
    return $this->db->insert_id();
  }
  //login db query
  public function login($params) {
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
  //bugs list
  public function bugs($params){
    $this->db->select('id,name,description,popular');
    if(isset($params['popular']) and trim($params['popular'])!='') { 
      $this->db->where('popular',$params['popular']);
    }
    if(isset($params['last_login_dt'])) { 
      $this->db->where('created_date >=',date('Y-m-d',strtotime($params['last_login_dt'])));
      $this->db->or_where('modified_date >=',date('Y-m-d',strtotime($params['last_login_dt'])));
    }
    $qry = $this->db->get('bug');
    if( $qry->num_rows()>0 ) {
      return $qry->result_array();
    }
    return FALSE;
  }
  public function juices($params){
    $this->db->select('id,name,price,single');
    if(isset($params['single']) and trim($params['single'])!='') { 
      $this->db->where('single',$params['single']);
    }
    if(isset($params['last_login_dt'])) { 
      $this->db->where('created_date >=',date('Y-m-d',strtotime($params['last_login_dt'])));
      $this->db->or_where('modified_date >=',date('Y-m-d',strtotime($params['last_login_dt'])));
    } 
    $qry = $this->db->get('juice');
    if( $qry->num_rows()>0 ) {
      return $qry->result_array();
    }
    return FALSE;
  }
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
  public function resources($params){
    $this->db->select('id,name,image');
    if(isset($params['name']) and trim($params['name'])!='') { 
      $this->db->where('name',$params['name']);
    }
    if(isset($params['last_login_dt'])) { 
      $this->db->where('created_date >=',date('Y-m-d',strtotime($params['last_login_dt'])));
      $this->db->or_where('modified_date >=',date('Y-m-d',strtotime($params['last_login_dt'])));
    }
    $qry = $this->db->get('resource');
    if( $qry->num_rows()>0 ) {
      return $qry->result_array();
    }
    return FALSE;
  }
  public function payment_modes($params){
    $this->db->select('id,name,status');
    if(isset($params['status']) and trim($params['status'])!='') { 
      $this->db->where('status',$params['status']);
    }
    $qry = $this->db->get('payment_mode');
    if( $qry->num_rows()>0 ) {
      return $qry->result_array();
    }
    return FALSE;
  }
  public function mixtures($params){
    $this->db->select('m.id,m.juice_id,m.resource_id,m.quantity,r.name,j.name');
    $this->db->from('mixture m');
    $this->db->join('resource r','m.resource_id = r.id');
    $this->db->join('juice j','m.juice_id = j.id');
    if(isset($params['juiece_id']) and trim($params['juice_id'])!='') { 
      $this->db->where('m.juice_id',$params['juice_id']);
    }
    if(isset($params['resource_id']) and trim($params['resource_id'])!='') { 
      $this->db->where('m.resource_id',$params['resource_id']);
    }
    if(isset($params['last_login_dt'])) { 
      $this->db->where('m.created_date >=',date('Y-m-d',strtotime($params['last_login_dt'])));
      $this->db->or_where('m.modified_date >=',date('Y-m-d',strtotime($params['last_login_dt'])));
    } 
    $qry = $this->db->get();
    if( $qry->num_rows()>0 ) {
      return $qry->result_array();
    }
    return FALSE;
  }
  /*public function get_bug_juices($bugs){
    $this->db->select('juice_id')->where_in('bug_id',$bugs)->from('bug_juice');
    $qry = $this->db->get();
    if($qry->num_rows()>0){
      return $qry->result_array();
    }
    return FALSE;
  }*/
  public function bug_juices($params){
    $this->db->select('dj.bug_id,dj.juice_id,j.name as juice,j.price,j.single,d.name as bug');
    $this->db->from('bug_juice dj');
    $this->db->join('juice j','dj.juice_id=j.id');
    $this->db->join('bug d','dj.bug_id=d.id');
    $this->db->where_in('dj.bug_id',$params['bug']);
    $qry = $this->db->get();
    if( $qry->num_rows()>0 ) {
      return $qry->result_array();
    }
    return FALSE;
  }
  public function get_favourite($user_id){
    $qry = $this->db->select('juice_id')->where('user_id',$user_id)->from('favourite')->get();
    if( $qry->num_rows()>0 ) {
      return $qry->row_array()['juice_id'];
    }
    return FALSE;
  }
  public function get_preference($user_id){
    $qry = $this->db->select('preference')->where('user_id',$user_id)->get('preference');
    if( $qry->num_rows()>0 ) {
      return $qry->row_array()['preference'];
    }
    return FALSE;
  }
}