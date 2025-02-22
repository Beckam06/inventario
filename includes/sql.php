<?php
  require_once('includes/load.php');

/*--------------------------------------------------------------*/
/* Function for find all database table rows by table name
/*--------------------------------------------------------------*/
function find_all($table) {
   global $db;
   if(tableExists($table))
   {
     return find_by_sql("SELECT * FROM ".$db->escape($table));
   }
}
/*--------------------------------------------------------------*/
/* Function for Perform queries
/*--------------------------------------------------------------*/
function find_by_sql($sql)
{
  global $db;
  $result = $db->query($sql);
  $result_set = $db->while_loop($result);
 return $result_set;
}
/*--------------------------------------------------------------*/
/*  Function for Find data from table by id
/*--------------------------------------------------------------*/
/********** */
/*function find_by_id($table,$id)
{
  global $db;
  $id = (int)$id;
    if(tableExists($table)){
          $sql = $db->query("SELECT * FROM {$db->escape($table)} WHERE id='{$db->escape($id)}' LIMIT 1");
          if($result = $db->fetch_assoc($sql))
            return $result;
          else
            return null;
     }
}
*/
function find_by_id($table, $id, $id_column = 'id') {
  global $db;
  $id = (int)$id;
  if (tableExists($table)) { 
      $sql = "SELECT * FROM {$db->escape($table)} WHERE {$db->escape($id_column)}='{$db->escape($id)}' LIMIT 1";
      $result = $db->query($sql);
      if ($result && $db->num_rows($result) > 0) {
          return $db->fetch_assoc($result);
      } else {
          return false; // Devuelve false si no se encuentra el registro
      }
  }
  return false;
}
/*--------------------------------------------------------------*/
/* Function for Delete data from table by id
/*--------------------------------------------------------------
function delete_by_id($table,$id)
{
  global $db;
  if(tableExists($table))
   {
    $sql = "DELETE FROM ".$db->escape($table);
    $sql .= " WHERE id=". $db->escape($id);
    $sql .= " LIMIT 1";
    $db->query($sql);
    return ($db->affected_rows() === 1) ? true : false;
   }
}
*/


function delete_by_id($table, $id, $id_column = 'id') {
  global $db;
  if (tableExists($table)) {
      $sql = "DELETE FROM " . $db->escape($table);
      $sql .= " WHERE " . $db->escape($id_column) . "=" . $db->escape($id);
      $sql .= " LIMIT 1";
      $result = $db->query($sql);
      return ($db->affected_rows() === 1) ? true : false;
  }
  return false;
}

/*--------------------------------------------------------------*/
/* Function for Count id  By table name
/*--------------------------------------------------------------*/

function count_by_id($table){
  global $db;
  if(tableExists($table))
  {
    $sql    = "SELECT COUNT(id) AS total FROM ".$db->escape($table);
    $result = $db->query($sql);
     return($db->fetch_assoc($result));
  }
}
/*--------------------------------------------------------------*/
/* Determine if database table exists
/*--------------------------------------------------------------*/
function tableExists($table){
  global $db;
  $table_exit = $db->query('SHOW TABLES FROM '.DB_NAME.' LIKE "'.$db->escape($table).'"');
      if($table_exit) {
        if($db->num_rows($table_exit) > 0)
              return true;
         else
              return false;
      }
  }
 /*--------------------------------------------------------------*/
 /* Login with the data provided in $_POST,
 /* coming from the login form.
/*--------------------------------------------------------------*/
  function authenticate($username='', $password='') {
    global $db;
    $username = $db->escape($username);
    $password = $db->escape($password);
    $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
    $result = $db->query($sql);
    if($db->num_rows($result)){
      $user = $db->fetch_assoc($result);
      $password_request = sha1($password);
      if($password_request === $user['password'] ){
        return $user['id'];
      }
    }
   return false;
  }
  /*--------------------------------------------------------------*/
  /* Login with the data provided in $_POST,
  /* coming from the login_v2.php form.
  /* If you used this method then remove authenticate function.
 /*--------------------------------------------------------------*/
   function authenticate_v2($username='', $password='') {
     global $db;
     $username = $db->escape($username);
     $password = $db->escape($password);
     $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
     $result = $db->query($sql);
     if($db->num_rows($result)){
       $user = $db->fetch_assoc($result);
       $password_request = sha1($password);
       if($password_request === $user['password'] ){
         return $user;
       }
     }
    return false;
   }


  /*--------------------------------------------------------------*/
  /* Find current log in user by session id
  /*--------------------------------------------------------------*/
  function current_user(){
      static $current_user;
      global $db;
      if(!$current_user){
         if(isset($_SESSION['user_id'])):
             $user_id = intval($_SESSION['user_id']);
             $current_user = find_by_id('users',$user_id);
        endif;
      }
    return $current_user;
  }
  /*--------------------------------------------------------------*/
  /* Find all user by
  /* Joining users table and user gropus table
  /*--------------------------------------------------------------*/
  function find_all_user(){
      global $db;
      $results = array();
      $sql = "SELECT u.id,u.name,u.username,u.user_level,u.status,u.last_login,";
      $sql .="g.group_name ";
      $sql .="FROM users u ";
      $sql .="LEFT JOIN user_groups g ";
      $sql .="ON g.group_level=u.user_level ORDER BY u.name ASC";
      $result = find_by_sql($sql);
      return $result;
  }
  /*--------------------------------------------------------------*/
  /* Function to update the last log in of a user
  /*--------------------------------------------------------------*/

 function updateLastLogIn($user_id)
	{
		global $db;
    $date = make_date();
    $sql = "UPDATE users SET last_login='{$date}' WHERE id ='{$user_id}' LIMIT 1";
    $result = $db->query($sql);
    return ($result && $db->affected_rows() === 1 ? true : false);
	}

  /*--------------------------------------------------------------*/
  /* Find all Group name
  /*--------------------------------------------------------------*/
  function find_by_groupName($val)
  {
    global $db;
    $sql = "SELECT group_name FROM user_groups WHERE group_name = '{$db->escape($val)}' LIMIT 1 ";
    $result = $db->query($sql);
    return($db->num_rows($result) === 0 ? true : false);
  }
  /*--------------------------------------------------------------*/
  /* Find group level
  /*--------------------------------------------------------------*/
  function find_by_groupLevel($level)
  {
    global $db;
    $sql = "SELECT group_level FROM user_groups WHERE group_level = '{$db->escape($level)}' LIMIT 1 ";
    $result = $db->query($sql);
    return($db->num_rows($result) === 0 ? true : false);
  }
  /*--------------------------------------------------------------*/
  /* Function for cheaking which user level has access to page
  /*--------------------------------------------------------------*/
   function page_require_level($require_level){
     global $session;
     $current_user = current_user();
     $login_level = find_by_groupLevel($current_user['user_level']);
     //if user not login
     if (!$session->isUserLoggedIn(true)):
            $session->msg('d','Por favor Iniciar sesión...');
            redirect('index.php', false);
      //if Group status Deactive
     elseif($login_level['group_status'] === '0'):
           $session->msg('d','Este nivel de usaurio esta inactivo!');
           redirect('home.php',false);
      //cheackin log in User level and Require level is Less than or equal to
     elseif($current_user['user_level'] <= (int)$require_level):
              return true;
      else:
            $session->msg("d", "¡Lo siento!  no tienes permiso para ver la página.");
            redirect('home.php', false);
        endif;

     }
    
   /* Function for Finding all product name
   /* JOIN with categorie  and media database table
   /--------------------------------------------------------------*/
   
   function join_product_table() {
    global $db;
    $sql  = "SELECT p.id_producto, p.nombreProducto, p.marca, p.modelo, p.descripcion, p.cantidad,  p.precio, p.proveedor, c.categoria AS categorie";
    $sql .= " FROM producto p";
    $sql .= " LEFT JOIN categoria c ON c.id_categoria = p.id_categoria";
    $sql .= " ORDER BY p.id_producto ASC";
    return find_by_sql($sql);
}
/*
     /--------------------------------------------------------------/
/* Function for Searching products by id or name
/--------------------------------------------------------------/*/

function search_product_table($search) {
  global $db;
  $sql  = "SELECT p.id_producto, p.nombreProducto, p.marca, p.modelo, p.descripcion, p.cantidad, p.garantia, p.precio, p.proveedor, c.categoria AS categorie, p.fechaIngreso, p.stock_minimo";
  $sql .= " FROM producto p";
  $sql .= " LEFT JOIN categoria c ON c.id_categoria = p.id_categoria";
  $sql .= " WHERE p.id_producto LIKE '%{$db->escape($search)}%' OR p.nombreProducto LIKE '%{$db->escape($search)}%'";
  $sql .= " ORDER BY p.id_producto ASC";
  return find_by_sql($sql);
}
    

/* Function for Finding product with category by id
/--------------------------------------------------------------/
*/

function find_product_with_category($product_id) {
  global $db;
  $sql  = "SELECT p.*, c.categoria FROM producto p";
  $sql .= " LEFT JOIN categoria c ON c.id_categoria = p.id_categoria";
  $sql .= " WHERE p.id_producto = '{$db->escape($product_id)}' LIMIT 1";
  $result = find_by_sql($sql);
  return !empty($result) ? $result[0] : null;
}

  /*--------------------------------------------------------------*/
  /* Function for Finding all product name
  /* Request coming from ajax.php for auto suggest
  /*--------------------------------------------------------------*/

   function find_product_by_title($product_name){
     global $db;
     $p_name = remove_junk($db->escape($product_name));
     $sql = "SELECT name FROM products WHERE name like '%$p_name%' LIMIT 5";
     $result = find_by_sql($sql);
     return $result;
   }

  /*--------------------------------------------------------------*/
  /* Function for Finding all product info by product title
  /* Request coming from ajax.php
  /*--------------------------------------------------------------*/
  function find_all_product_info_by_title($title){
    global $db;
    $sql  = "SELECT * FROM products ";
    $sql .= " WHERE name ='{$title}'";
    $sql .=" LIMIT 1";
    return find_by_sql($sql);
  }

  /*--------------------------------------------------------------*/
  /* Function for Update product quantity
  /*--------------------------------------------------------------*/
  function update_product_qty($qty,$p_id){
    global $db;
    $qty = (int) $qty;
    $id  = (int)$p_id;
    $sql = "UPDATE products SET quantity=quantity -'{$qty}' WHERE id = '{$id}'";
    $result = $db->query($sql);
    return($db->affected_rows() === 1 ? true : false);

  }
  /*--------------------------------------------------------------*/
  /* Function for Display Recent product Added
  /*--------------------------------------------------------------*/
 function find_recent_product_added($limit){
   global $db;
   $sql   = " SELECT p.id,p.name,p.sale_price,p.media_id,c.name AS categorie,";
   $sql  .= "m.file_name AS image FROM products p";
   $sql  .= " LEFT JOIN categories c ON c.id = p.categorie_id";
   $sql  .= " LEFT JOIN media m ON m.id = p.media_id";
   $sql  .= " ORDER BY p.id DESC LIMIT ".$db->escape((int)$limit);
   return find_by_sql($sql);
 }
 /*--------------------------------------------------------------*/
 /* Function for Find Highest saleing Product
 /*--------------------------------------------------------------*/
 function find_higest_saleing_product($limit){
   global $db;
   $sql  = "SELECT p.name, COUNT(s.product_id) AS totalSold, SUM(s.qty) AS totalQty";
   $sql .= " FROM sales s";
   $sql .= " LEFT JOIN products p ON p.id = s.product_id ";
   $sql .= " GROUP BY s.product_id";
   $sql .= " ORDER BY SUM(s.qty) DESC LIMIT ".$db->escape((int)$limit);
   return $db->query($sql);
 }
 /*--------------------------------------------------------------*/
 /* Function for find all sales
 /*--------------------------------------------------------------*/
 function find_all_sale(){
   global $db;
   $sql  = "SELECT s.id,s.qty,s.price,s.date,p.name";
   $sql .= " FROM sales s";
   $sql .= " LEFT JOIN products p ON s.product_id = p.id";
   $sql .= " ORDER BY s.date DESC";
   return find_by_sql($sql);
 }
 /*--------------------------------------------------------------*/
 /* Function for Display Recent sale
 /*--------------------------------------------------------------*/
function find_recent_sale_added($limit){
  global $db;
  $sql  = "SELECT s.id,s.qty,s.price,s.date,p.name";
  $sql .= " FROM sales s";
  $sql .= " LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " ORDER BY s.date DESC LIMIT ".$db->escape((int)$limit);
  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate sales report by two dates
/*--------------------------------------------------------------*/
function find_sale_by_dates($start_date,$end_date){
  global $db;
  $start_date  = date("Y-m-d", strtotime($start_date));
  $end_date    = date("Y-m-d", strtotime($end_date));
  $sql  = "SELECT s.date, p.name,p.sale_price,p.buy_price,";
  $sql .= "COUNT(s.product_id) AS total_records,";
  $sql .= "SUM(s.qty) AS total_sales,";
  $sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price,";
  $sql .= "SUM(p.buy_price * s.qty) AS total_buying_price ";
  $sql .= "FROM sales s ";
  $sql .= "LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " WHERE s.date BETWEEN '{$start_date}' AND '{$end_date}'";
  $sql .= " GROUP BY DATE(s.date),p.name";
  $sql .= " ORDER BY DATE(s.date) DESC";
  return $db->query($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate Daily sales report
/*--------------------------------------------------------------*/
function  dailySales($year,$month){
  global $db;
  $sql  = "SELECT s.qty,";
  $sql .= " DATE_FORMAT(s.date, '%Y-%m-%e') AS date,p.name,";
  $sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price";
  $sql .= " FROM sales s";
  $sql .= " LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " WHERE DATE_FORMAT(s.date, '%Y-%m' ) = '{$year}-{$month}'";
  $sql .= " GROUP BY DATE_FORMAT( s.date,  '%e' ),s.product_id";
  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate Monthly sales report
/*--------------------------------------------------------------*/
function  monthlySales($year){
  global $db;
  $sql  = "SELECT s.qty,";
  $sql .= " DATE_FORMAT(s.date, '%Y-%m-%e') AS date,p.name,";
  $sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price";
  $sql .= " FROM sales s";
  $sql .= " LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " WHERE DATE_FORMAT(s.date, '%Y' ) = '{$year}'";
  $sql .= " GROUP BY DATE_FORMAT( s.date,  '%c' ),s.product_id";
  $sql .= " ORDER BY date_format(s.date, '%c' ) ASC";
  return find_by_sql($sql);
}

function generar_pdf_orden_salida($id_orden_salida, $id_solicitud, $id_departamento, $responsable, $cantidad_entregada, $nombre_producto, $marca, $modelo) {
  try {
      require_once('tcpdf/tcpdf.php'); // Asegúrate de que la ruta sea correcta

      // Crear la carpeta si no existe
      $upload_dir = __DIR__ . '/../uploads/ordenes_salida';
      if (!is_dir($upload_dir)) {
          mkdir($upload_dir, 0777, true); // Crea la carpeta con permisos de escritura
      }

      // Crear el PDF
      $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
      $pdf->SetCreator(PDF_CREATOR);
      $pdf->SetAuthor('Sistema de Inventario');
      $pdf->SetTitle('Nota de Entrega #' . $id_orden_salida);
      $pdf->SetSubject('Nota de Entrega');
      $pdf->SetKeywords('Nota, Entrega, Inventario');

      $pdf->AddPage();
      $pdf->SetFont('helvetica', '', 12);

      // Obtener más detalles del departamento
      $nombre_departamento = obtener_nombre_departamento($id_departamento);

      // Contenido del PDF
      $html = '<h1>Nota de Entrega #' . $id_orden_salida . '</h1>';
      $html .= '<p><strong>Fecha de Entrega:</strong> ' . date('Y-m-d H:i:s') . '</p>';
      $html .= '<p><strong>Departamento:</strong> ' . $nombre_departamento . '</p>';
      $html .= '<p><strong>Responsable:</strong> ' . $responsable . '</p>';
      $html .= '<p><strong>Producto:</strong> ' . $nombre_producto . '</p>';
      $html .= '<p><strong>Marca:</strong> ' . $marca . '</p>';
      $html .= '<p><strong>Modelo:</strong> ' . $modelo . '</p>';
      $html .= '<p><strong>Cantidad Entregada:</strong> ' . $cantidad_entregada . '</p>';
      $html .= '<p><strong>Nota de Responsabilidad:</strong> El responsable ' . $responsable . ' se hace cargo de los productos entregados.</p>';

      $pdf->writeHTML($html, true, false, true, false, '');

      // Guardar el PDF en la carpeta de uploads
      $pdf_path = $upload_dir . '/nota_entrega_' . $id_orden_salida . '.pdf';
      $pdf->Output($pdf_path, 'F');

      // Retornar la ruta relativa para el enlace
      return 'uploads/ordenes_salida/nota_entrega_' . $id_orden_salida . '.pdf';
  } catch (Exception $e) {
      throw new Exception("Error al generar el PDF: " . $e->getMessage());
  }
}


function obtener_nombre_departamento($id_departamento) {
  global $db; // Asegúrate de que $db esté disponible en este contexto

  // Consulta para obtener el nombre del departamento
  $sql = "SELECT nombre_departamento FROM departamento WHERE id_departamento = {$id_departamento} LIMIT 1";
  $result = $db->query($sql);

  if ($result && $db->num_rows($result) > 0) {
      $departamento = $db->fetch_assoc($result);
      return $departamento['nombre_departamento'];
  }

  return 'Desconocido'; // Si no se encuentra el departamento
}
/*--------------------------------------------------------------*/
/* FUNCION PARA ARCHIVOS
/*--------------------------------------------------------------


function find_by_producto($table, $id_producto) {
  global $db;
  $sql = "SELECT * FROM {$table} WHERE id_producto = {$id_producto}";
  $result = $db->query($sql);
  return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}*/

?>