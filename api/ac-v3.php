<?php
require(__DIR__ . '/api-keys.php');

class ActiveCampaign_API_Miraiedu
{
  private $api_url = AC_API_URL;
  private $api_key = AC_API_KEY;
  private $all_tags;
  private $all_lists;
  private $raw_fields;

  /* DEEP DATA VARS */
  private $connection_id;
  private $connection = array(
    "service" => "Mirai-Bay API",
    "externalid" => "mirai_bay_store",
    "name" => "Merchandising Plaza",
    // Store name
    "logoUrl" => "https://api.miraibay.net/assets/logo/mirai-web.png",
    "linkUrl" => "https://mirai-bay.com/"
  );

  public function get_account_name()
  {
    $url = $this->api_url;
    $url = preg_replace('/https{0,1}:\/\//', "", $url);
    return ucfirst(explode('.', $url)[0]);
  }

  public function check_connection()
  {
    $request = $this->remote_post(
      $this->api_url . "/api/3/contacts/-1",
      array('headers' => $this->get_headers(), "method" => "GET")
    );
    if ($request) {
      return true;
    }
    return false;
  }

  public function add_tag_to_contact($contact_id, $tag_id)
  {
    if (intval($tag_id) < 0) {
      return false;
    }

    $body = array(
      "contactTag" => array(
        "contact" => $contact_id,
        "tag" => $tag_id
      )
    );

    $response = $this->remote_post(
      $this->api_url . "/api/3/contactTags/",
      array(
        'headers' => $this->get_headers(true),
        'method' => 'POST',
        'body' => json_encode($body)
      )
    );
  }

  public function get_all_conn()
  {
    $response = $this->remote_post(
      $this->api_url . "/api/3/connections/",
      array('headers' => $this->get_headers(), "method" => "GET")
    );
    echo ('<pre>');
    echo json_encode(json_decode($response, true), JSON_PRETTY_PRINT);
    echo ('</pre>');
  }

  public function get_conn_id($create_if_not_present = true)
  {

    if ($this->connection_id) {
      return $this->connection_id;
    }

    // Look for connection

    $response = $this->remote_post(
      $this->api_url . "/api/3/connections/?filters[externalid]=" . $this->connection["externalid"],
      array('headers' => $this->get_headers(), "method" => "GET")
    );

    $data = json_decode($response, true);

    if (!empty($data["connections"])) {
      $this->connection_id = $data["connections"][0]["id"];
      return $this->connection_id;
    }

    if (!$create_if_not_present) {
      return NULL;
    }

    // Connection not present: create it!

    $body = array(
      "connection" => $this->connection
    );
    $response = $this->remote_post(
      $this->api_url . "/api/3/connections/",
      array(
        'headers' => $this->get_headers(true),
        'method' => 'POST',
        'body' => json_encode($body)
      )
    );

    $data = json_decode($response, true);

    if (!empty($data['connection'])) {
      $this->connection_id = $data['connection']['id'];
      return $this->connection_id;
    }

    return NULL;

  }

  public function get_ecom_customer_id($email, $external_id = NULL, $create_if_not_present = true)
  {

    // Look for customer

    $response = $this->remote_post(
      $this->api_url . "/api/3/ecomCustomers/?filters[email]=" . $email . "&filters[connectionid]=" . $this->get_conn_id(),
      array('headers' => $this->get_headers(), "method" => "GET")
    );

    $data = json_decode($response, true);

    if (!empty($data["ecomCustomers"])) {
      return $data["ecomCustomers"][0]["id"];
    }

    if (!$create_if_not_present) {
      return NULL;
    }

    // Customer not present: create it!

    if (!$external_id) {
      $external_id = md5($email);
    }

    $body = array(
      "ecomCustomer" => array(
        "connectionid" => $this->get_conn_id(),
        "externalid" => $external_id,
        "email" => $email,
        "acceptsMarketing" => "1"
      )
    );

    $response = $this->remote_post(
      $this->api_url . "/api/3/ecomCustomers/",
      array(
        'headers' => $this->get_headers(true),
        'method' => 'POST',
        'body' => json_encode($body)
      )
    );

    $data = json_decode($response, true);

    if (!empty($data['ecomCustomer'])) {
      return $data['ecomCustomer']['id'];
    }

    return NULL;
  }

  public function delete_ecom_customer($id)
  {
    $response = $this->remote_post(
      $this->api_url . "/api/3/ecomCustomers/" . $id,
      array(
        'headers' => $this->get_headers(true),
        'method' => 'DELETE',
        'data_format' => 'body'
      )
    );
  }

  public function delete_conn($id)
  {
    $response = $this->remote_post(
      $this->api_url . "/api/3/connections/" . $id,
      array(
        'headers' => $this->get_headers(true),
        'method' => 'DELETE',
        'data_format' => 'body'
      )
    );
  }

  public function create_ecom_order($order, $email)
  {
    $order_products = array();

    foreach ($order["items"] as $product) {
      $order_products[] = array(
        // required fields
        "externalid" => $product["id"],
        "name" => $product["name"],
        "price" => $this->format_price($product["price"]),
        "quantity" => $product["quantity"],
        // Optional fields
        "category" => $product["category"]
        // "sku" => "POGO-12",
        // "description" => "lorem ipsum...",
        // "imageUrl" => "https://example.com/product.jpg",
        // "productUrl" => "https://store.example.com/product12345"
      );

    }

    if (!isset($order["currency"]) || !$order["currency"]) {
      $order["currency"] = "EUR";
    }
    if (!isset($order["date"]) || !$order["date"]) {
      $order["date"] = date(DATE_ATOM);
    }

    $body = array(
      "ecomOrder" => array(
        "externalid" => $order["id"],
        "email" => $email,
        "orderProducts" => $order_products,
        "externalCreatedDate" => $order["date"],
        "totalPrice" => $this->format_price($order["total"]),
        "currency" => $order["currency"],
        "connectionid" => $this->get_conn_id(),
        "customerid" => $this->get_ecom_customer_id($email),

        /* OPTIONAL FIELDS */

        "shippingAmount" => $order["shipping"],
        "taxAmount" => $order["tax"],
        // "source" => "1",
        // "orderDiscounts" => array(
        //   array(
        //     "name" => "1OFF",
        //     "type" => "order",
        //     "discountAmount" => 100
        //   )
        // ),
        // "orderUrl" => "https://example.com/orders/3246315233",
        // "externalUpdatedDate" => "2016-09-14T17:41:39-04:00",
        // "shippingMethod" => "UPS Ground",
        // "discountAmount" => 100,
        // "orderNumber" => "myorder-123",
      )
    );


    $response = $this->remote_post(
      $this->api_url . "/api/3/ecomOrders/",
      array(
        'headers' => $this->get_headers(true),
        'method' => 'POST',
        'body' => json_encode($body)
      )
    );
    echo ('<pre>');
    echo json_encode(json_decode($response, true), JSON_PRETTY_PRINT);
    echo ('</pre>');
  }

  public function delete_ecom_order($id)
  {
    $response = $this->remote_post(
      $this->api_url . "/api/3/ecomOrders/" . $id,
      array(
        'headers' => $this->get_headers(true),
        'method' => 'DELETE',
        'data_format' => 'body'
      )
    );
  }

  public function get_ecom_order_by_ext($external_id)
  {
    $request = $this->remote_post(
      $this->api_url . "/api/3/ecomOrders/?filters[externalid]=" . $external_id,
      array('headers' => $this->get_headers(), "method" => "GET")
    );

    if (!$request) {
      return false; // Bail early
    }

    $body = json_decode($request, true);
    return $body["ecomOrders"][0];

  }

  public function get_contact_by_email($email)
  {
    $request = $this->remote_post(
      $this->api_url . "/api/3/contacts/?email=" . $email,
      array('headers' => $this->get_headers(), "method" => "GET")
    );
    if (!$request) {
      return false; // Bail early
    }
    $body = json_decode($request, true);
    if (count($body["contacts"]) > 0) {
      return $body["contacts"][0];
    }
    return false;
  }

  public function sync_contact($data, $fields = NULL)
  {
    if (isset($data["fullName"])) {
      $tmp = explode(" ", $data["fullName"]);
      $data["firstName"] = array_shift($tmp);
      $data["lastName"] = implode(" ", $tmp);
      unset($data["fullName"]);
    }
    if (is_array($fields)) {
      $data["fieldValues"] = array();
      foreach ($fields as $i => $v) {
        $data["fieldValues"][] = ["field" => $i, "value" => $v];
      }
    }
    $body = array("contact" => $data);
    $request = $this->remote_post(
      $this->api_url . "/api/3/contact/sync",
      array(
        'headers' => $this->get_headers(true),
        'method' => 'POST',
        'body' => json_encode($body)
      )
    );
    if (!$request) {
      return false; // Bail early
    }
    $body = json_decode($request, true);
    return $body["contact"];
  }

  public function super_sync_contact($contact, $lists = NULL, $tags = NULL, $fields = NULL)
  {

    if (empty($contact["email"])) {
      return false;
    }

    $contact = $this->sync_contact($contact, $fields);
    $contact_id = $contact['id'];

    if ($contact_id) {

      if (is_array($tags)) {
        foreach ($tags as $tag_id) {
          $this->add_tag_to_contact($contact_id, $tag_id);
        }
      }

      if (is_array($lists)) {
        foreach ($lists as $list_id) {
          $this->subscribe_contact_to_list($contact_id, $list_id);
        }
      }

      return $contact;

    }

    return false;

  }

  public function get_contact_tags($contact_id)
  {
    $request = $this->remote_post(
      $this->api_url . "/api/3/contacts/" . $contact_id . "/contactTags",
      array('headers' => $this->get_headers(), 'method' => "GET")
    );
    if (!$request) {
      return false; // Bail early
    }
    $body = json_decode($request, true);
    return $body["contactTags"];
  }

  public function get_contact_fields($contact_id)
  {
    $request = $this->remote_post(
      $this->api_url . "/api/3/contacts/" . $contact_id . "/fieldValues",
      array('headers' => $this->get_headers(), "method" => "GET")
    );
    if (!$request) {
      return false; // Bail early
    }
    $body = json_decode($request, true);

    return $body["fieldValues"];
  }

  public function remove_tag_from_contact($contact_id, $tag_id)
  {
    $tags = $this->get_contact_tags($contact_id);
    if (!$tags) {
      return false;
    }
    $c_tag_id = NULL;
    foreach ($tags as $tkey => $tval) {
      if (intval($tval['tag']) == intval($tag_id)) {
        $c_tag_id = intval($tval['id']);
        break;
      }
    }
    if ($c_tag_id) {
      $response = $this->remote_post(
        $this->api_url . "/api/3/contactTags/" . $c_tag_id,
        array(
          'headers' => $this->get_headers(true),
          'method' => 'DELETE'
        )
      );
    }
  }

  public function update_contact_field($contact_id, $field_id, $value)
  {
    $body = array(
      "fieldValue" => array(
        "value" => $value,
        "contact" => $contact_id,
        "field" => $field_id
      )
    );


    $response = $this->remote_post(
      $this->api_url . "/api/3/fieldValues/",
      array(
        'headers' => $this->get_headers(true),
        'method' => 'POST',
        'body' => json_encode($body)
      )
    );
  }

  public function subscribe_contact_to_list($contact_id, $list_id)
  {
    if (intval($list_id) < 0) {
      return false;
    }

    $body = array(
      "contactList" => array(
        "contact" => $contact_id,
        "list" => $list_id,
        "status" => 1
      )
    );

    $response = $this->remote_post(
      $this->api_url . "/api/3/contactLists/",
      array(
        'headers' => $this->get_headers(true),
        'method' => 'POST',
        'body' => json_encode($body)
      )
    );

    return $response;

  }

  public function get_all_tags()
  {
    if (isset($this->all_tags)) {
      return $this->all_tags;
    }
    $request = $this->remote_post(
      $this->api_url . "/api/3/tags?limit=100",
      array('headers' => $this->get_headers(), "method" => "GET")
    );
    if (!$request) {
      return false; // Bail early
    }
    $body = json_decode($request, true);
    if (!is_array($body["tags"])) {
      return false;
    }

    $result = [];
    foreach ($body["tags"] as $tag) {
      //if($tag->tagType == "contact"){
      $result[$tag["id"]] = $tag["tag"];
      //}
    }
    asort($result);
    $this->all_tags = $result;
    return $this->all_tags;

  }

  public function get_all_lists()
  {
    if (isset($this->all_lists)) {
      return $this->all_lists;
    }
    $request = $this->remote_post(
      $this->api_url . "/api/3/lists?limit=100",
      array('headers' => $this->get_headers(), "method" => "GET")
    );
    if (!$request) {
      return false; // Bail early
    }
    $body = json_decode($request, true);
    if (!is_array($body["lists"])) {
      return false;
    }

    $result = [];
    foreach ($body["lists"] as $list) {
      //if($list->tagType == "contact"){
      $result[$list["id"]] = $list["name"];
      //}
    }
    asort($result);
    $this->all_lists = $result;
    return $this->all_lists;

  }

  public function get_all_fields($type = "")
  {
    if (!isset($this->raw_fields)) {
      $request = $this->remote_post(
        $this->api_url . "/api/3/fields?limit=100",
        array('headers' => $this->get_headers(), "method" => "GET")
      );
      if (!$request) {
        return false; // Bail early
      }
      $body = json_decode($request, true);
      if (!is_array($body["fields"])) {
        return false;
      }
      $this->raw_fields = $body["fields"];
    }

    $result = [];
    foreach ($this->raw_fields as $field) {
      if ($type == "" || $type == "all" || $type == $field["type"]) {
        $result[$field["id"]] = $field["title"];
      }
    }
    asort($result);
    return $result;

  }

  private function get_headers($with_json = false)
  {
    $res = array('Api-Token: ' . $this->api_key);
    if ($with_json) {
      $res[] = 'Content-Type: application/json; charset=utf-8';
    }
    return $res;
  }

  private function format_price($n)
  {
    if (empty($n)) {
      return "0";
    }
    $n = str_replace(".", "", $n);
    $n = str_replace(",", "", $n);
    return $n;
  }

  private function remote_post($url, $data)
  {
    $body = isset($data["body"]) ? $data["body"] : [];
    $method = isset($data["method"]) ? $data["method"] : "POST";
    $headers = isset($data["headers"]) ? $data["headers"] : [];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $server_output = curl_exec($ch);
    curl_close($ch);
    return $server_output;

  }
}