<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ResourceModel extends CI_Model {
  public function __construct() {
    $this->load->database();
    $this->load->helper('string');
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
}