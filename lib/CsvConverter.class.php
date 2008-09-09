<?php
class CsvConverter
{
  private
    $strategy,
    $columns_map;

  public function __construct(array $columns_map, csvConversionStrategy $strategy)
  {
    $this->columns_map = $columns_map;
    $this->strategy = $strategy;
  }

  public function convert($csvfile_url, $skip_rows = 0)
  {
    $data = $this->extractData($csvfile_url, $this->columns_map, $skip_rows);
    return $this->strategy->convert($data);
  }

  private function extractData($csvfile_url, array $columns_maps, $skip_rows = 0)
  {
    $handle = fopen($csvfile_url, 'r');
    $data = array();
    $rownum = 1;
    while(($row = fgetcsv($handle)) !== false)
    {
      $rowdata = array();
      $colnum = 0;
      if ($rownum <= $skip_rows)
      {
        $rownum++;
        continue;
      }

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

