<?php
/**
 * @license GPL3
 */

// Include dependencies
require_once dirname(__FILE__).'/../lib/dompdf-0.5.1/dompdf_config.inc.php';
require_once dirname(__FILE__).'/../lib/CsvConverter.class.php';
require_once dirname(__FILE__).'/../lib/CsvConversionStrategy.class.php';
require_once dirname(__FILE__).'/../lib/CsvToHtmlConversionStrategy.class.php';
require_once dirname(__FILE__).'/../lib/CsvToDomPdfConversionStrategy.class.php';

// TODO : better arguments parsing and abstraction
if (!isset($_SERVER['argv'][1]) || !$csv_filepath = $_SERVER['argv'][1])
{
  throw new RuntimeException('Please provide source csv file path');
}
if (!is_readable($csv_filepath))
{
  throw new RuntimeException(sprintf('"%s" must be readable', $csv_filepath));
}

$templates_basedir =  dirname(__FILE__) . '/../templates/scrum';

// Define columns map
$columns_map = array('id', null, null, 'title', 'importance', 'estimation', 'description', 'howtodemo', 'notes');

// Instanciate and configure csv -> html conversion strategy (required by the csv -> pdf strategy)
$html_strategy_params = array('layout_filepath'      => sprintf('%s/layout.html.php', $templates_basedir),
                              'template_filepath'    => sprintf('%s/card.html.php', $templates_basedir),
                              'destination_filepath' => dirname(__FILE__).'/../cards.html');
$csv_to_html_strategy = new CsvToHtmlConversionStrategy($html_strategy_params);

// Instanciate and configure csv -> pdf convertion strategy
$csv_to_pdf_strategy = new CsvToDomPdfConversionStrategy(array('html_strategy'        => $csv_to_html_strategy,
                                                               'destination_filepath' => dirname(__FILE__).'/../cards.pdf'));

$converter = new CsvConverter($columns_map, $csv_to_pdf_strategy);
$filepath = $converter->convert($csv_filepath);

echo sprintf('Cards where generated in "%s"', realpath(dirname(__FILE__).'/../cards.pdf')), "\n";