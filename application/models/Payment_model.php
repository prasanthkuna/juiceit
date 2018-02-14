<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Payment_model extends CI_Model {
  public function __construct() {
    $this->load->database();
    $this->load->helper('string');
  }
  public function GetPaymentModes($params){
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
}