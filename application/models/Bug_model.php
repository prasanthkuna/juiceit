<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Bug_model extends CI_Model {
  public function __construct() {
    $this->load->database();
    $this->load->helper('string');
  }
  
  //bugs list
  public function GetAllBugs($params){
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

  public function GetUserPreferences($user_id){
    $qry = $this->db->select('preference')->where('user_id',$user_id)->get('preference');
    if( $qry->num_rows()>0 ) {
      return $qry->row_array()['preference'];
    }
    return FALSE;
  }
}