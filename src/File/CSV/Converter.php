<?php
/**
 * @license GPL3
 */

/**
 * Converts a CSV file using a conversion strategy.
 */
class File_CSV_Converter
{
  private
    /**
     * @var csvConversionStrategy
     */
    $strategy,
    /**
     * @var array
     */
    $columns_map;

  /**
   * Instanciates a converter object.
   *
   * @param array                 $columns_map  Definition of csv columns names
   * @param csvConversionStrategy $strategy     The conversion strategy to be used
   */
  public function __construct(array $columns_map, File_CSV_Converter_Strategy $strategy)
  {
    $this->columns_map = $columns_map;
    $this->strategy    = $strategy;
  }

  /**
   * Converts supplied CSV file using current converter's strategy.
   *
   * @param   string    $csvfile_url   Path to the file
   * @param   integer   $skip_rows     (optional)Number of rows to ignore in the file
   *
   * @return  string  Path to the resulting file
   *
   * @todo  CSV file specification must be configurable
   */
  public function convert($csvfile_url, $skip_rows = 0)
  {
    $data = $this->extractData($csvfile_url, $this->columns_map, $skip_rows);
    return $this->strategy->convert($data);
  }

  /**
   * Turns data in the CSV file into an associative array.
   *
   * @param   string    $csvfile_url
   * @param   array     $columns_maps
   *
   * @return array
   *
   * @todo  Throw an exception when columns map does not have the right number of columns
   * @todo  Optionnaly use http://pear.php.net/package/File for reading file
   */
  private function extractData($csvfile_url, array $columns_maps)
  {
    if (!is_readable($csvfile_url))
    {
      throw new RuntimeException(sprintf('"%s" is not readable', $csvfile_url));
    }

    // Open file
    $handle = fopen($csvfile_url, 'r');

    // Extract each row
    $data = array();
    $rownum = 1;
    while(($row = fgetcsv($handle)) !== false)
    {
      $rowdata = array();
      $colnum = 0;

      // Build hash, using columns map definition
      foreach ($columns_maps as $column_name)
      {
        if (!is_null($column_name))
        {
          $rowdata[$column_name] = $row[$colnum];
        }
        $colnum++;
      }
      $rownum++;
      $data[] = $rowdata;
    }

    return $data;
  }
}

