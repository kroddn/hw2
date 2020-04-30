<?php
// Determines whether "register_globals" directive needs to be emulated
define('CFG_PHP_REGISTER_GLOBALS', true);

// If "register_globals" directive needs to be emulated
// and "register_globals" directive is no longer available (PHP 5.4+)
if(CFG_PHP_REGISTER_GLOBALS and !ini_get('register_globals')){
   /**
    * Emulates "register_globals" directive.
    * See http://php.net/manual/en/faq.misc.php#faq.misc.registerglobals
    */
   function register_globals(){
      $superglobals = array($_SERVER, $_ENV, $_FILES, $_COOKIE, $_POST, $_GET);
      foreach ($superglobals as $superglobal) {
         foreach($superglobal as $key => $value){
            // If there is a collision, don't overwrite the existing global variable
            if(!array_key_exists($key, $GLOBALS)){
               $GLOBALS[$key] = $value;
            }
         }
      }  

      if(isset($_SESSION)){ 
         foreach($_SESSION as $key => $value){
            // If there is a collision, don't overwrite the existing global variable
            if(!array_key_exists($key, $GLOBALS)){
               // Use assingment by reference to update session variable 
               // when global variable changes
               $GLOBALS[$key] = &$_SESSION[$key];
            }
         }
      }
   }

   // Emulate "register_globals" directive
   register_globals();
}

// If session_is_registered() function is no longer available (PHP 5.4+)
if (!function_exists('session_is_registered')){
   /**
    * Emulates session_is_registered() function.
    */
   function session_is_registered($key){
      return array_key_exists($key, $_SESSION);
   }
}

// If session_unregister() function is no longer available (PHP 5.4+)
if (!function_exists('session_unregister')){
   /**
    * Emulates session_unregister() function.
    */
   function session_unregister($key){
      unset($_SESSION[$key]);
   }
}

// If session_register() function is no longer available (PHP 5.4+)
if (!function_exists('session_register')){
   /**
    * Emulates session_register() function.
    */
   function session_register(){
      // If session_start() was not called before this function is called, 
      // an implicit call to session_start() with no parameters will be made.
      if(session_id() === ''){ session_start(); }

      $args = func_get_args();
      foreach ($args as $key){
         // If "register_globals" directive needs to be emulated
         // or if session variable is not registered
         if (CFG_PHP_REGISTER_GLOBALS or !session_is_registered($key)) {
            $_SESSION[$key] = array_key_exists($key, $GLOBALS) ? $GLOBALS[$key] : null;
         }

         // Use assingment by reference to update session variable 
         // when global variable changes
         $GLOBALS[$key] = &$_SESSION[$key];
      }
   }
}
?>