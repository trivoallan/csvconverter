<?php
/**
 * @license GPL3
 */
// TODO : autoload
require_once realpath(dirname(__FILE__).'/../../../../../vendors/dompdf-0.5.1/dompdf_config.inc.php');

class File_CSV_Converter_Strategy_ToDomPDF implements File_CSV_Converter_Strategy
{

  private
    $document_orientation,
    $html_strategy;

  public function __construct(array $params = array())
  {
    if (!class_exists('DOMPDF'))
    {
      throw new Exception(sprintf('The DOMPDF library is required by the "%s" conversion strategy. See package documentation for further informations.', __CLASS__));
    }
    $this->html_strategy = new File_CSV_Converter_Strategy_ToHTML($params);
    $this->document_orientation = $params['orientation'];
    $this->paper_format = $params['format'];
  }

  public function convert(array $data, $destinationfile_url)
  {
    if ($destinationfile_url == '/dev/stdout')
    {
      throw new RuntimeException(sprintf('The "%s" conversion strategy does not support outputing to "/dev/stdout". Please use the "--output" option.', __CLASS__));
    }

    $dompdf = new DOMPDF();
    $dompdf->set_paper($this->paper_format, $this->document_orientation);
    $this->html_strategy->convert($data, $destinationfile_url);
    $dompdf->load_html_file($destinationfile_url);
    $dompdf->render();
    file_put_contents($destinationfile_url, $dompdf->output());
  }

  public static function getCliCommandSpecification()
  {
    $spec = array(
      'name'          => 'to-dompdf',
      'description'   => sprintf('converts a CSV file to a PDF file using dompdf (provided by %s)', __CLASS__),
      'options' => array(
        'orientation' => array(
          'short_name'  => '-o',
          'long_name'   => '--orientation',
          'description' => 'document orientation',
          'choices'     => array('portrait', 'landscape'),
          'default'     => 'portrait'),
        'format' => array(
          'short_name'  => '-o',
          'long_name'   => '--format',
          'description' => 'paper format',
          'default'     => 'a4'),
        'templates_dir' => array(
          'short_name'  => '-t',
          'long_name'   => '--templates-dir',
          'description' => 'path to the directory containing the templates',
          'help_name'   => 'DIRECTORY')
      )
    );

    return $spec;
  }
}