<?php
error_reporting(E_ALL | E_STRICT);

require_once 'Console/CommandLine.php';
require_once dirname(__FILE__).'/../lib/dompdf-0.5.1/dompdf_config.inc.php';
require_once dirname(__FILE__).'/../lib/CsvConversionStrategy.class.php';
require_once dirname(__FILE__).'/../lib/CsvToDomPdfConversionStrategy.class.php';
require_once dirname(__FILE__).'/../lib/CsvToHtmlConversionStrategy.class.php';
require_once dirname(__FILE__).'/../lib/File_CSV_Converter_CommandLine.php';
require_once dirname(__FILE__).'/../lib/CsvConverter.class.php';

// TODO : strategies registry
$conversion_strategies = array('CsvToHtmlConversionStrategy' => 'to-html', 'CsvToDomPdfConversionStrategy' => 'to-dompdf');

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
  $converter = new CsvConverter($parsed_arguments->options['map'], $strategy_instance);
  $converter->convert($parsed_arguments->command->args['input_file']);

} catch (Exception $e) {

  $parser->displayError($e->getMessage());

}