<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com 
 * @copyright  Copyright &copy; 2004-2007 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html 
 */

/**
 * @tag pager:SECTION
 * @parent_tag_class WactPagerListTag
 * @restring_self_nesting true
 * @package wact
 * @version $Id: section.tag.php 6243 2007-08-29 11:53:10Z pachanga $
 */
class WactPagerSectionTag extends WactCompilerTag
{
  function generateTagContent($code)
  {
    $parent = $this->findParentByClass('WactPagerNavigatorTag');
    $code->writePhp('if (!' . $parent->getComponentRefCode() . '->isDisplayedSection()) {');

    $code->writePhp($this->getDataSource()->getComponentRefCode() . '["href"] = ' . $parent->getComponentRefCode() . '->getSectionUri();' . "\n");
    $code->writePhp($this->getDataSource()->getComponentRefCode() . '["number_begin"] = ' . $parent->getComponentRefCode() . '->getSectionBeginPage();' . "\n");
    $code->writePhp($this->getDataSource()->getComponentRefCode() . '["number_end"] = ' . $parent->getComponentRefCode() . '->getSectionEndPage();' . "\n");

    parent :: generateTagContent($code);

    $code->writePhp('}');
  }
}


