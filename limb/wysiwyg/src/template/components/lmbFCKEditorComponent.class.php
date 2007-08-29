<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com 
 * @copyright  Copyright &copy; 2004-2007 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html 
 */
lmb_require('limb/wysiwyg/src/template/components/lmbWysiwygComponent.class.php');

@define('LIMB_FCKEDITOR_DIR', 'limb/wysiwyg/lib/FCKeditor/');

/**
 * class lmbFCKEditorComponent.
 *
 * @package wysiwyg
 * @version $Id: lmbFCKEditorComponent.class.php 6243 2007-08-29 11:53:10Z pachanga $
 */
class lmbFCKEditorComponent extends lmbWysiwygComponent
{
  var $dir = '';

  function renderContents()
  {
    $this->renderEditor();
  }

  function renderEditor()
  {
    include_once(LIMB_FCKEDITOR_DIR . '/fckeditor.php');

    $editor = new FCKeditor($this->getAttribute('name')) ;
    $this->_setEditorParameters($editor);
    $editor->Value = $this->getValue();

    $editor->Create();
  }

  function _setEditorParameters($editor)
  {
    if($this->getIniOption('base_path'))
      $editor->BasePath	= $this->getIniOption('base_path');
    else
      $editor->BasePath = '/FCKEditor/';

    if($this->getIniOption('Config'))
      $editor->Config	= $this->getIniOption('Config');

    if($this->getIniOption('ToolbarSet'))
      $editor->ToolbarSet	= $this->getIniOption('ToolbarSet');

    $editor->Width = $this->getAttribute('width');
    $editor->Height = $this->getAttribute('height');
  }
}


