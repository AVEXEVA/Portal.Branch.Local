<?php
namespace network;
if(!trait_exists('traits\magic_methods')){require('cgi-bin/php/traits/magic_methods.php');}
class session {
  //traits
  use traits\magic_methods;
  //variables
  protected $id = null;
  protected $user = null;
  protected $token = null;
  protected $time_lapse = null;
  protected $user_agent = null;
  protected $refreshed = null;
  //functions
  public function __verify(){
    $result = $driver->__select($database,
      " select  *
        from    session
                left join token       on  session.token      = token.id
                left join time_lapse  on  session.time_lapse = time_lapse.id
        where   session.user = ?
                and token.alphanumeric = ?
                and dateadd(hour, 3, session.refreshed) >= ?
      ;", array($_session['user_id'], $_session['token_alphanumeric']), date('y-m-d h:i:s', strtotime('now')));
    return count($result) > 0;
  }
  public function __insert(){
    if(self::__validate()){
      $this->__set(array(
        'token' => new token(),
        'time_lapse' => new time_lapse(array(
          'start' => date('y-m-d h:i:s', strtotime('now'))
        )),
        'user_agent' => new user_agent(),
        'refreshed' => date('y-m-d h:i:s', strtotime('now'))
      ));
      $this->__get('token')->__insert();
      $this->__get('time_lapse')->__insert();
      $this->__get('user_agent')->__insert();

    }
  }
}
?>
