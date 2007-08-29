<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com 
 * @copyright  Copyright &copy; 2004-2007 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html 
 */
lmb_require('limb/datetime/src/lmbDate.class.php');
lmb_require('limb/datetime/src/lmbDatePeriod.class.php');

/**
 * class lmbTimePeriod.
 *
 * @package datetime
 * @version $Id: lmbTimePeriod.class.php 6243 2007-08-29 11:53:10Z pachanga $
 */
class lmbTimePeriod extends lmbDatePeriod
{
  function __construct($start, $end)
  {
    $start_date = new lmbDate($start);
    $end_date = new lmbDate($end);

    parent :: __construct($start_date->setYear(0)->setMonth(0)->setDay(0),
                          $end_date->setYear(0)->setMonth(0)->setDay(0));
  }

  function getDatePeriod($date)
  {
    $date = new lmbDate($date);
    $year = $date->getYear();
    $month = $date->getMonth();
    $day = $date->getDay();

    $start_date = new lmbDate($year, $month, $day,
                              $this->start->getHour(), $this->start->getMinute(), $this->start->getSecond());

    $end_date = new lmbDate($year, $month, $day,
                            $this->end->getHour(), $this->end->getMinute(), $this->end->getSecond());

    return new lmbDatePeriod($start_date, $end_date);
  }
}


