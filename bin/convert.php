<?php
error_reporting(E_ALL | E_STRICT);

// TODO : autoload
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . '/../src'));
require_once 'Console/CommandLine.php';
require_once 'File/CSV/Converter.php';
require_once 'File/CSV/Converter/Strategy.php';
require_once 'File/CSV/Converter/Strategy/ToDomPDF.php';
require_once 'File/CSV/Converter/Strategy/ToHTML.php';
require_once 'File/CSV/Converter/CommandLine.php';

// TODO : strategies registry
$conversion_strategies = array('File_CSV_Converter_Strategy_ToHTML' => 'to-html', 'File_CSV_Converter_Strategy_ToDomPDF' => 'to-dompdf');

// Configure parser
$parser = new File_CSV_Converter_CommandLine(array(), $conversion_strategies);

// Parse cli arguments
try {

  $parsed_arguments = $parser->parse();

  if (!$parsed_arguments->command_name)
  {
    throw new Exception('A valid command must be supplied');
  }

  // Build strategy parameters array
  $strategy_parameters = array('destination_filepath' => $parsed_arguments->options['output_file']);
  foreach ($parsed_arguments->command->options as $option_name => $option_value)
  {
    if (!empty($option_value))
    {
      $strategy_parameters[$option_name] = $option_value;
    }
  }

  // Instanciate and configure strategy
  $strategy_classname = $parser->getStrategyClassByCommand($parsed_arguments->command_name);
  $strategy_instance = new $strategy_classname($strategy_parameters);

  // Instanciate converter
  $converter = new File_CSV_Converter($parsed_arguments->options['map'], $strategy_instance);
  $converter->convert($parsed_arguments->command->args['input_file']);

} catch (Exception $e) {

  $parser->displayError($e->getMessage());

}