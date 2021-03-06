<?php
/**
 * Created by PhpStorm.
 * User: legovaer
 * Date: 23/02/15
 * Time: 19:07
 */

namespace legovaer;
class CLI {

  protected $options = array();
  protected $arguments = array();
  protected $shortOptions = 'ht:cs:loc:';

  protected $longOptions = array(
    'title:',
    'phploc:',
    'checkstyle:',
    'dry:',
    'pmd:',
    'help',
  );

  public function __construct($exit = true) {
    $this->handleArguments();
    $this->run();
  }

  private function run() {
    $this->checkForTitle();

    $parser = new AnalysisParser();
    if (isset($this->arguments['phploc'])) {
      $parser->setLocFile($this->arguments['phploc']);
    }

    if (isset($this->arguments['pmd'])) {
      $parser->setPmdFile($this->arguments['pmd']);
    }

    if (isset($this->arguments['checkstyle'])) {
      $parser->setCheckStyleFile($this->arguments['checkstyle']);
    }

    if (isset($this->arguments['dry'])) {
      $parser->setDryFile($this->arguments['dry']);
    }

    $analysis = $parser->analyze();

    $grader = new Grader($analysis);
    $analysis = $grader->analyze();
    $standards = $grader->getStandards();
    $result = new \legovaer\ResultGenerator();
    $result->setAnalysis($analysis, $standards, $this->arguments['title']);
    $result->generate();
  }

  private function checkForTitle() {
    if (!isset($this->arguments['title'])) {
      echo "\033[0;31mError: title was not set.\033[0m\n";
      $this->showHelp();
      exit();
    }
  }

  public function handleArguments() {
    $this->options = getopt($this->shortOptions, $this->longOptions);
    foreach ($this->options as $option=>$value) {
      switch ($option) {
        case 'help':
        case 'h':{
          $this->showHelp();
          exit();
          }
        break;

        case 'title':
        case 't':{
          $this->arguments['title'] = $value;
          }
        break;

        case 'checkstyle':
        case 'cs':{
          $this->arguments['checkstyle'] = $value;
          }
        break;

        case 'dry':{
          $this->arguments['dry'] = $value;
          }
        break;

        case 'pmd':{
          $this->arguments['pmd'] = $value;
          }
        break;

        case 'loc':
        case 'phploc':{
          $this->arguments['phploc'] = $value;
          }
        break;
      }
    }
  }

  private function showHelp()
  {
    print <<<EOT
\033[0;33m Usage:\033[0m
  php CodeGrader.php [options]

\033[0;33mCode Grader Options:\033[0m
 \033[0;32m--title\033[0m (-t)              Title or name of the project.
 \033[0;32m--phploc\033[0m <file>           The phploc.csv file, generated by PHPLOC. \033[0;33m(default: "src/phploc.csv")\033[0m
 \033[0;32m--checkstyle\033[0m (-cs) <file> The checkstyle-warnings.xml file, generated by Jenkins. \033[0;33m(default: "src/checkstyle-warnings.xml")\033[0m
 \033[0;32m--dry\033[0m <file>              The dry-warnings.xml file, generated by Jenkins. \033[0;33m(default: "src/dry-warnings.xml")\033[0m
 \033[0;32m--pmd\033[0m <file>              The pmd-warnings.xml file, generated by Jenkins. \033[0;33m(default: "src/pmd-warnings.xml")\033[0m
 \033[0;32m--help\033[0m (-h)               Prints this usage information.
EOT;
  }
}