<?php

  /**
   * Make links in a text clickable
   *
   * @param string $text
   * @return string
   */
  function smarty_modifier_clickable($text) {
  	
  	if (MAKE_LINKS_CLICKABLE && MAKE_LINKS_CLICKABLE === true) {
  		require_once SMARTY_PATH . '/plugins/modifier.linkify.php';

  		if($text != '') {
  			$text = ' '.$text;
  			$text = preg_replace('#([\s\(\)]|[,]|[<p.*>])(https?|ftp|news){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^"\s\(\)<\[]*)?)#ieu', '\'$1\'.smarty_modifier_linkify(\'$2://$3\')', $text);
  			$text = preg_replace('#([\s\(\)]|[,]|[<p.*>])(www)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^"\s\(\)<\[]*)?)#ieu', '\'$1\'.smarty_modifier_linkify(\'$2.$3\', \'$2.$3\')', $text);
  			$text = preg_replace("#(^|[\n ]|[,]|[\s]|)([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#iu", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $text);
  		} // if
  	} // if
    
  	return trim($text);
  } // smarty_modifier_clickable

?>