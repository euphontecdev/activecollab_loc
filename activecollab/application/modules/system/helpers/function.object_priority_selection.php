<?php

  /**
   * object_priority_selection helper
   *
   * @package activeCollab.modules.system
   * @subpackage helpers
   */
  
  /**
   * Return object priority icon
   *
   * @param array $params
   * @param Smarty $smarty
   * @return string
   */
  function smarty_function_object_priority_selection($params, &$smarty) {
    $object = array_var($params, 'object');
    if(!instance_of($object, 'ProjectObject')) {
      return new InvalidParamError('object', $object, '$object is not valid instance of ProjectObject class', true);
    } // if

    $select = '<select onchange="modify_priority_status(this);">' . 
			    //BOF:mod 20121107
				'<option value="' . PRIORITY_URGENT . '" ' . ((int)$object->getPriority()==(int)PRIORITY_URGENT ? ' selected ' : '') . '>' . lang('Urgent Priority') . '</option>' .
				//EOF:mod 20121107
    			'<option value="' . PRIORITY_HIGHEST . '" ' . ((int)$object->getPriority()==(int)PRIORITY_HIGHEST ? ' selected ' : '') . '>' . lang('Highest Priority') . '</option>' .
    			'<option value="' . PRIORITY_HIGH . '" ' . ((int)$object->getPriority()==(int)PRIORITY_HIGH ? ' selected ' : '') . '>' . lang('High Priority') . '</option>' .
    			'<option value="' . PRIORITY_NORMAL . '" ' . ((int)$object->getPriority()==(int)PRIORITY_NORMAL ? ' selected ' : '') . '>' . lang('Normal Priority') . '</option>' .
    			'<option value="' . PRIORITY_LOW . '" ' . ((int)$object->getPriority()==(int)PRIORITY_LOW ? ' selected ' : '') . '>' . lang('Low Priority') . '</option>' .
    			'<option value="' . PRIORITY_LOWEST . '" ' . ((int)$object->getPriority()==(int)PRIORITY_LOWEST ? ' selected ' : '') . '>' . lang('Lowest Priority') . '</option>' .
				//BOF:mod 20121107
				/*
				//EOF:mod 20121107
    			'<option value="' . PRIORITY_ONGOING . '" ' . ((int)$object->getPriority()==(int)PRIORITY_ONGOING ? ' selected ' : '') . '>' . lang('Ongoing Priority') . '</option>' .
				//BOF:mod 20121107
				*/
				//EOF:mod 20121107
				'<option value="' . PRIORITY_HOLD . '" ' . ((int)$object->getPriority()==(int)PRIORITY_HOLD ? ' selected ' : '') . '>' . lang('Hold Priority') . '</option>' .    			
    		  '</select>';	
    return $select;
  } // smarty_function_object_priority

?>