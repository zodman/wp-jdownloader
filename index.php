<?php

/**
 * The plugin bootstrap file
 *
 *
 * @link              https://github.com/zodman/wp-jdownloader
 * @since             1.0.0
 * @package           jdownloader
 *
 * @wordpress-plugin
 * Plugin Name:       jdownloader
 * Plugin URI:        https://github.com/zodman/wp-jdownloader
 * Description:       Wordpress plugin for generate jdownloader Click'N'Load
 * Version:           1.0.0
 * Author:            zodman
 * Author URI:        http://opensrc.mx
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       [jdownloader][jdownloader]
 * Domain Path:       /languages
 */



if( ! function_exists('jdownloader') ){
    add_action('plugins_loaded', function() {
        add_shortcode("jdownloader", "encrypt_jdownloader");
    });

    function encrypt_link($link){
        function base16Encode($arg){
            $ret="";
            for($i=0;$i<strlen($arg);$i++){
                $tmp=ord(substr($arg,$i,1));
                $ret.=dechex($tmp);
            }
            return $ret;
        }

        $key="";
        for( $i = 0; $i <16; $i++){
            $key .= mt_rand(0,9);
        }
        $transmitKey=base16Encode($key);
        $cp = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
        @mcrypt_generic_init($cp, $key,$key);
        $enc = mcrypt_generic($cp, $link);
        mcrypt_generic_deinit($cp);
        mcrypt_module_close($cp);
        $crypted=base64_encode($enc);
        return [$crypted,$transmitKey];
    }


    function encrypt_jdownloader($atts=[], $content="" ) {
        list($crypted, $keye) = encrypt_link($content);
        $template = "
<script>
    var jdownloader=false;
    setInterval(function (){
        var btn = document.querySelector('#jdownloader_btn');
        if(!jdownloader) {
            btn.disabled=true;
        } else {
            btn.disabled=false;
        }
        
    }, 1000*3);
</script>
  <script language='javascript' src='http://127.0.0.1:9666/jdcheck.js'></script>


                   <FORM ACTION='http://127.0.0.1:9666/flash/addcrypted2' METHOD='POST'>
           <INPUT TYPE='hidden' NAME='source' VALUE='http://localhost:8000'>
           <INPUT TYPE='hidden' NAME='jk' VALUE=\"function f(){ return '$keye';}\">
           <INPUT TYPE='hidden' NAME='crypted' VALUE='$crypted'>
           <button id='jdownloader_btn' disabled class='btn btn-default' TYPE='SUBMIT' NAME='submit'>
                    Agregar al JDownloader <img src='//i.imgur.com/nSzoPLh.png'>
            </button><br>
            <small> Abre el jdownloder para habilitar el boton y recarga la p&aacute;gina</small>
            
        </FORM>
        ";
    return $template;
    
    }

}
