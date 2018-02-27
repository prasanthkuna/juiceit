<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class OrderModel extends CI_Model {
  public function __construct() {
    $this->load->database();
    $this->load->helper('string');
  }
  public function PostOrder($userId)
  {
      $cartItems = $this->db->select('juice.price, cart.juiceid, cart.quantity , cart.id ')
      ->from('cart')->join('juice','cart.juiceid = juice.id')
      ->where('cart.userid', $userId)->where('cart.isactive',1)->get()->result_Array();
      $total = 0;

      foreach($cartItems as $x)
      {
          $total = $total + ( $x['quantity'] *  $x['price']);
      }
      $order['userid'] = $userId;
      $order['ordereddate'] = date('Y-m-d H:i:s');
      $order['deliverydate'] = date("Y-m-d H:i:s", strtotime("+30 minutes"));
      $order['amount'] = $total;

      $this->db->insert('order',$order);
      $orderId =  $this->db->insert_id();

      $orderItems = array();
      foreach($cartItems as $x)
      {
          $OrderItem['orderId'] = $orderId;
          $OrderItem['cartId'] = $x['id'];
          array_push($orderItems, $OrderItem);
      }
      $this->db->insert_batch('orderItem',$orderItems);
      $this->db->set('IsActive',0)->where('userId', $userId)->update('cart');
      return $orderItems;
  }
}