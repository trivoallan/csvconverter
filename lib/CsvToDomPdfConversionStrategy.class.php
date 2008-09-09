<?php
class CsvToDomPdfConversionStrategy implements CsvConversionStrategy
{

  private
    $destination_filepath,
    $html_strategy;

  public function __construct(array $params = array())
  {
    $this->destination_filepath = realpath($params['destination_filepath']);
    $this->html_strategy = $params['html_strategy'];
  }

  public function convert(array $data)
  {
    $dompdf = new DOMPDF();
    $dompdf->set_paper('a4', 'portrait');
    $dompdf->load_html_file($this->html_strategy->convert($data));
    $dompdf->render();
    file_put_contents($this->destination_filepath, $dompdf->output());

    return $this->destination_filepath;
  }

}