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
   *
   * @return  string  Path to the resulting file
   *
   * @todo  CSV file specification must be configurable
   */
  public function convert($csvfile_url)
  {
    $data = $this->extractData($csvfile_url, $this->columns_map);
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
      // Check that the columns map is coherent w/ the csv file
      if ($rownum == 1)
      {
        if (count($columns_maps) != count($row))
        {
          throw new RuntimeException(
            sprintf('Columns map must have the same number of columns than the CSV file (%d/%d)',
                    count($columns_maps),
                    count($row)
            )
          );
        }
      }

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

