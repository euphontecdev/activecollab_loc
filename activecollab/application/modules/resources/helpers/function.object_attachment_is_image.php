<?php

  /**
   * attachment_is_image helper
   *
   * @package activeCollab.modules.resources
   * @subpackage helpers
   */
  
  /**
   * List object attachments
   * 
   * Parameters:
   * 
   * - object - selected object
   *
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_object_attachment_is_image($params, &$smarty) {
    $object = array_var($params, 'object');
    if(!instance_of($object, 'Attachment')) {
      return new InvalidParamError('object', $object, '$object is expected to be an instance of ProjectObject class', true);
    } // if
    
    if (strpos($object->getMimeType(), 'image')!==false){
    	//return '<img src="' . $object->getViewUrl() . '" width="100" height="100" />';
    	return '<img src="' . $object->getThumbnailUrl() . '" />';
    }
    return '';
	/*$temp = strtolower(trim($object->getName()));
	if (substr($temp, -4)=='.jpg' || substr($temp, -4)=='jpeg' || substr($temp, -4)=='.gif' || substr($temp, -4)=='.bmp' || substr($temp, -4)=='.png')
		$is_image = 'Yes';
	else
		$is_image = 'No';*/
		
	
    
  } // attachment_is_image

?>