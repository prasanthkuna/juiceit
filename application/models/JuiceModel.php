<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class JuiceModel extends CI_Model {
  public function __construct() {
    $this->load->database();
    $this->load->helper('string');
  }
  public function GetJuices($userId){
    $result = $this->db->select('juice.id,juice.name,juice.price')->from('juice')
    ->join('bugJuice' , 'juice.id = bugJuice.juiceId')->join('preference', 'bugJuice.bugId = preference.bugId')
    ->where('preference.UserId',$userId)->where('preference.IsActive',1)->get();
    return $result->result_array();
  }

  public function GetFavourites($userId){
    $qry = $this->db->select('juice.Id , juice.Name , juice.Price')
    ->from('favourite')->join('juice','favourite.juiceId = juice.Id')
    ->where('favourite.userId',$userId)->where('favourite.IsActive' , 1)->get();
    if( $qry->num_rows()>0 ) {
      return $qry->result_array();
    }
    return FALSE;
  }
  public function PostFavourite($favourite)
  {
    $this->db->insert('favourite',$favourite);
    return true;
  }
  public function GetJuiceDetails($juiceId)
  {
    $result = $this->db->select('resource.id ,resource.name ,resource.image')
    ->from('juice')->join('mixture', 'juice.id = mixture.juiceid')->join('resource' , 'mixture.resourceid = resource.id')
    ->where('juice.id', $juiceId)->get();
    if($result->num_rows()>0)return $result->result_array();
    else return null;
  }
}