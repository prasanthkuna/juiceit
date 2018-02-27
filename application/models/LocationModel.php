<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class LocationModel extends CI_Model {
  public function __construct() {
    $this->load->database();
    $this->load->helper('string');
  }
  public function GetNearestStalls()
  {
      $result = $this->db->select('Id,Name, Address , state , pincode, Latitude, Longitude')
      ->get('stall');
      return $result->result_array();
  }
}