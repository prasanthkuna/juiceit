<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class PaymentModel extends CI_Model {
  public function __construct() {
    $this->load->database();
    $this->load->helper('string');
  }
  public function GetPaymentModes(){
    $result = $this->db->select('id,name')->get('paymentMode');
    return $result->result_array();
    
  }
}