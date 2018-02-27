<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class BugModel extends CI_Model {
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

  public function GetUserPreferences($userId){
    $conditions = array('preference.UserId'=>$userId , 'preference.IsActive'=>1);
    $qry = $this->db->select('bug.Id,bug.Name, bug.Description')->from('bug')
    ->join('preference','bug.Id = preference.bugId')
    ->where($conditions)->get();
    if( $qry->num_rows()>0 ) {
      return $qry->result_Array();
    }
    return NULL;
  }
  public function SetUserPrefernce($preferences)
  {
    $qry = $this->db->insert_batch('preference',$preferences);
    return true;
  }
  public function SetBugToInActive($userId,$bugIds)
  { 
    $qry = $this->db->set('IsActive',0)->where('UserId',$userId)->where_in('BugId', $bugIds)->update('preference');
    
  }
}