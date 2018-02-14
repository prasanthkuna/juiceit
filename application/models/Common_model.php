<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Common_model extends CI_Model {
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
}
  