<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_databases extends CI_Driver {

  public function view () {
    global $bp, $ci, $page;
    if (!is_admin(1)) $page->eject();
    if ( isset($_GET['file']) ||
         (isset($_GET['db']) && (isset($_GET['server']) || isset($_GET['sqlite']) || isset($_GET['sqlite2']) || isset($_GET['pgsql']) || isset($_GET['oracle']) || isset($_GET['mssql']))) ) {
         
      $errors =& load_class('Exceptions', 'core');
      $errors->log = false;
      include APPPATH . 'libraries/Admin/adminer-3.6.2.php';
      
      // include APPPATH . 'libraries/Admin/adminer-4.1.0.php';
      exit;
    }
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
      $ci->db->query('DELETE FROM ci_databases WHERE id = ?', array($_GET['delete']));
      $page->eject($page->url('delete', '', 'delete'));
    }
    $page->plugin('jQuery', 'code', '
      $("a.delete").click(function(){
        var url = $(this).data("url");
        if (confirm("Are you sure you would like to delete this database?")) {
          window.location = url;
        }
        return false;
      });
    ');
    $html = '<div class="page-header" style="margin-top:20px;"><h3>Select A Database</h3></div>';
    
    $url = $page->url('delete', '', '?');
    
    #-- CodeIgniter --#
    $dl = array('CodeIgniter');
    $dl[][] = '<a href="' . $page->url('add', $url, array('sqlite'=>'', 'db'=>BASE_URI . 'CodeIgniter.db')) . '">' . $page->get('domain') . '</a>';
    $html .= $bp->lister('dl', $dl, 'dl-horizontal');
    
    #-- Databases --#
    $query = $ci->db->query('SELECT id, driver, database, config FROM ci_databases ORDER BY driver, database ASC');
    if ($query->num_rows() > 0) {
      $display = array();
      foreach ($query->result_array() AS $row) {
        list($id, $driver, $database, $config) = array_values($row);
        $config = unserialize($config);
        switch ($driver) {
          case 'mssql':   $adminer = 'mssql'; break;
          case 'mysqli':  $adminer = 'server'; break;
          case 'oci8':    $adminer = 'oracle'; break;
          case 'postgre': $adminer = 'pgsql'; break;
          case 'sqlite':  $adminer = 'sqlite2'; break;
          case 'sqlite3':  $adminer = 'sqlite'; break;
        }
        if (!isset($adminer)) continue;
        if ($driver == 'sqlite' || $driver == 'sqlite3') {
          if (file_exists($config['database'])) {
            $link = $page->url('add', $url, array($adminer=>$config['hostname'], 'db'=>$config['database']));
            $link = '<a href="' . $link . '">' . str_replace(array(BASE_URI, BASE), array('BASE_URI . ', 'BASE . '), $config['database']) . '</a>';
          } else {
            $ci->db->query('DELETE FROM ci_databases WHERE id = ' . $id);
            continue;
          }
        } else {
          $link = $page->url('add', $url, array($adminer=>$config['hostname'], 'username'=>$config['username'], 'db'=>$config['database']));
          $link = '<a href="' . $link . '">' . $config['database'] . '</a>';
          if (!empty($config['password'])) $link .= ' (' . $config['password'] . ')';
          $link .= '<a href="#" data-url="' . $page->url('add', '', 'delete', $id). '" class="delete pull-right" title="Delete ' . $config['database'] . ' Database">' . $bp->icon('trash') . '</a>';
        }
        switch ($driver) {
          case 'mssql':   $display['MS SQL'][] = $link; break;
          case 'mysqli':  $display['MySQL'][] = $link; break;
          case 'oci8':    $display['Oracle'][] = $link; break;
          case 'postgre': $display['PostgreSQL'][] = $link; break;
          case 'sqlite':  $display['SQLite 2'][] = $link; break;
          case 'sqlite3': $display['SQLite 3'][] = $link; break;
        }
      }
      foreach ($display as $driver => $database) {
        $dl = array($driver);
        foreach ($database as $link) $dl[][] = $link;
        $html .= $bp->lister('dl', $dl, 'dl-horizontal');
      }
    }
    
    return $this->admin($html);
  }
  
}

/* End of file Admin_databases.php */
/* Location: ./application/libraries/Admin/drivers/Admin_databases.php */