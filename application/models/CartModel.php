<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class CartModel extends CI_Model
{
    public function __construct()
    {
        $this->load->database();
        $this->load->helper('string');
    }
    public function AddJuiceToCart($cart)
    {
        $result = $this->db->select('quantity')->where('userid', $cart['UserId'])->where('juiceId', $cart['JuiceId'])->get('cart')->row()->quantity;
        if ($result>0) {
            $this->db->set('quantity', $result + 1)->where('userid', $cart['UserId'])->where('juiceid', $cart['JuiceId'])->update('cart');
        } else {
            $this->db->insert('cart', $cart);
        }
        return true;
    }
    public function GetCart($userId)
    {
        $result = $this->db->select('juice.Id, juice.Name ,juice.Price ,cart.Quantity')
        ->from('juice')->join('cart','juice.id = cart.juiceid')
        ->where('cart.userid',$userId)->where('cart.isactive',1)->get();
        return $result->result_array();
    }
    
}
