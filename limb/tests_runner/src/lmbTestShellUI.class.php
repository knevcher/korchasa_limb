<?php
/**
 * Limb Web Application Framework
 *
 * @link http://limb-project.com
 *
 * @copyright  Copyright &copy; 2004-2007 BIT
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 * @version    $Id: lmbTestShellUI.class.php 5877 2007-05-13 05:18:20Z pachanga $
 * @package    tests_runner
 */
require_once(dirname(__FILE__) . '/lmbTestGetopt.class.php');
require_once(dirname(__FILE__) . '/lmbTestRunner.class.php');

class lmbTestShellUI
{
  protected $test_path;
  protected $argv;
  protected $posix_opts = true;
  protected $call_exit = true;

  function __construct($argv = null)
  {
    try
    {
      $this->argv = is_array($argv) ? $argv : lmbTestGetopt::readPHPArgv();
    }
    catch(Exception $e)
    {
      $this->_error($e->getMessage() . "\n");
    }
  }

  function setPosixMode($flag = true)
  {
    $this->posix_opts = $flag;
  }

  function exitAfterRun($flag = true)
  {
    $this->call_exit = $flag;
  }

  function help($script = '')
  {
    global $argv;
    if(!$script && isset($argv[0]))
      $script = basename($argv[0]);

    $usage = <<<EOD
Usage:
  $script OPTIONS <file|dir> [<file|dir>, <file|dir>, ...]
  Advanced SimpleTest unit tests runner. Finds and executes unit tests within filesystem.
Options:
  -c, --config=/file.php        PHP configuration file path
  -h, --help                    Displays this help and exit
  --cover=path1;path2           Sets paths delimitered with ';' which should be analyzed for coverage
  --cover-report=dir            Sets coverage report directory
  --cover-exclude=path1;path2   Sets paths delimitered with ';' which should be excluded from coverage analysis

EOD;
    return $usage;
  }

  protected function _help($code = 0)
  {
    echo $this->help();
    exit($code);
  }

  protected function _error($message, $code = 1)
  {
    echo "ERROR: $message";
    echo $this->_help();
    exit($code);
  }

  static function getShortOpts()
  {
    return 'ht:b:c:';
  }

  static function getLongOpts()
  {
    return array('help', 'config=', 'cover=', 'cover-report=', 'cover-exclude=');
  }

  function run()
  {
    $res = $this->_doRun();

    if($this->call_exit)
      exit($res ? 0 : 1);
    else
      return $res;
  }

  function runEmbedded()
  {
    return $this->_doRun();
  }

  protected function _doRun()
  {
    $short_opts = self :: getShortOpts();
    $long_opts = self :: getLongOpts();

    try
    {
      if($this->posix_opts)
        $options = lmbTestGetopt::getopt($this->argv, $short_opts, $long_opts);
      else
        $options = lmbTestGetopt::getopt2($this->argv, $short_opts, $long_opts);
    }
    catch(Exception $e)
    {
      $this->_help(1);
    }

    $configured = false;
    $cover_include = '';
    $cover_exclude = '';
    $cover_report_dir = null;

    foreach($options[0] as $option)
    {
      switch($option[0])
      {
        case 'h':
        case '--help':
          $this->_help(0);
          break;
        case 'c':
        case '--config':
          include_once(realpath($option[1]));
          $configured = true;
          break;
        case '--cover':
          $cover_include = $option[1];
          break;
        case '--cover-report':
          $cover_report_dir = $option[1];
          break;
        case '--cover-exclude':
          $cover_exclude = $option[1];
          break;
      }
    }

    if(!$configured && $config = getenv('LIMB_TESTS_RUNNER_CONFIG'))
      include_once($config);

    if(!is_array($options[1]))
      $this->_help(1);
    
    if(!$cover_report_dir && defined('LIMB_TESTS_RUNNER_COVERAGE_REPORT_DIR'))
      $cover_report_dir = LIMB_TESTS_RUNNER_COVERAGE_REPORT_DIR;

    $runner = new lmbTestRunner($options[1]);

    if($cover_include)
      $runner->useCoverage($cover_include, $cover_exclude, $cover_report_dir);

    $found = false;
    $res = $runner->run($found);

    if(!$found)
      $this->_help(1);

    return $res;
  }
}

?>