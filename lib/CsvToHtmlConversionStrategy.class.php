<?php
class CsvToHtmlConversionStrategy implements csvConversionStrategy
{

  private
    $layout_filepath,
    $template_filepath,
    $destination_filepath;

  public function __construct(array $params = array())
  {
    $this->layout_filepath = $params['layout_filepath'];
    $this->template_filepath = $params['template_filepath'];
    $this->destination_filepath = $params['destination_filepath'];
  }

  public function convert(array $data)
  {
    $html_results = array();
    $rownum = 1;
    foreach ($data as $row)
    {
      $decoded_row = array_map('utf8_decode', $row);
      $decoded_row['rownum'] = $rownum;
      extract($decoded_row);
      ob_start();
      ob_implicit_flush(0);
      require($this->template_filepath);

      $html_results[] = ob_get_clean();
      $rownum++;
    }
    ob_start();
    ob_implicit_flush(0);
    extract(array('content' => implode('', $html_results)));
    require($this->layout_filepath);
    $decorated_html = ob_get_clean();

    file_put_contents($this->destination_filepath, $decorated_html);
    return $this->destination_filepath;
  }
}
