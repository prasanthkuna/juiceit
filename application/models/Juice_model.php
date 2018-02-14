<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Juice_model extends CI_Model {
  public function __construct() {
    $this->load->database();
    $this->load->helper('string');
  }
  public function GetJuices($params){
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

  public function GetFavourites($user_id){
    $qry = $this->db->select('juice_id')->where('user_id',$user_id)->from('favourite')->get();
    if( $qry->num_rows()>0 ) {
      return $qry->row_array()['juice_id'];
    }
    return FALSE;
  }
}