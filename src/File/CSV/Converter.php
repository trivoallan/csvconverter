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
   * @param   string    $csvfile_url           Path to the  input file
   * @param   string    $destinationfile_url   Path to the output file
   *
   * @return  string  Path to the resulting file
   */
  public function convert($csvfile_url, $destinationfile_url)
  {
    $data = $this->extractData($csvfile_url, $this->columns_map);
    $this->strategy->convert($data, $destinationfile_url);
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

    // Try to guess CSV file format
    $csv_format = $this->discoverFormat($handle);

    // Extract each row
    $data = array();
    $rownum = 1;
    while(($row = fgetcsv($handle, 0, $csv_format['sep'], $csv_format['quote'])) !== false)
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

    fclose($handle);

    return $data;
  }

  /**
   * Discover the format of a CSV file (the number of fields, the separator
   * and if it quote string fields)
   *
   * Code taken from http://pear.php.net/package/File
   *
   * @param resource $fp
   * @param array extra separators that should be checked for.
   * @return mixed Assoc array or false
   */
  private function discoverFormat($fp, $extraSeps = array())
  {
    // Set auto detect line ending for Mac EOL support
    $oldini = ini_get('auto_detect_line_endings');
    if ($oldini != '1') {
      ini_set('auto_detect_line_endings', '1');
    }

    // Take the first 30 lines and store the number of ocurrences
    // for each separator in each line
    $lines = '';
    for ($i = 0; $i < 30 && $line = fgets($fp, 4096); $i++) {
      $lines .= $line;
    }

    if ($oldini != '1') {
      ini_set('auto_detect_line_endings', $oldini);
    }

    $seps = array("\t", ';', ':', ',');
    $seps = array_merge($seps, $extraSeps);
    $matches = array();
    $quotes = '"\'';

    $lines = str_replace('""', '', $lines);
    while ($lines != ($newLines = preg_replace('|((["\'])[^"]*(\2))|', '\2_\2', $lines))){
      $lines = $newLines;
    }

    $eol   = strpos($lines, "\r") ? "\r" : "\n";
    $lines = explode($eol, $lines);
    foreach ($lines as $line) {
      $orgLine = $line;
      foreach ($seps as $sep) {
        $line = preg_replace("|^[^$quotes$sep]*$sep*([$quotes][^$quotes]*[$quotes])|sm", '_', $orgLine);
        // Find all seps that are within qoutes
        ///FIXME ... counts legitimit lines as bad ones

        // In case there's a whitespace infront the field
        $regex = '|\s*?';
        // Match the first quote (optional), also optionally match = since it's excel stuff
        $regex.= "(?:\=?[$quotes])";
        $regex.= '(.*';
        // Don't match a sep if we are inside a quote
        // also don't accept the sep if it has a quote on the either side
        ///FIXME has to be possible if we are inside a quote! (tests fail because of this)
        $regex.= "(?:[^$quotes])$sep(?:[^$quotes])";
        $regex.= '.*)';
        // Close quote (if it's present) and the sep (optional, could be end of line)
        $regex.= "(?:[$quotes](?:$sep?))|Ums";
        preg_match_all($regex, $line, $match);
        // Finding all seps, within quotes or not
        $sep_count = substr_count($line, $sep);
        // Real count
        $matches[$sep][] = $sep_count - count($match[0]);
      }
    }

    $final = array();
    // Group the results by amount of equal ocurrences
    foreach ($matches as $sep => $res) {
      $times = array();
      $times[0] = 0;
      foreach ($res as $k => $num) {
        if ($num > 0) {
          $times[$num] = (isset($times[$num])) ? $times[$num] + 1 : 1;
        }
      }
      arsort($times);

      // Use max fields count.
      $fields[$sep] = max(array_flip($times));
      $amount[$sep] = $times[key($times)];
    }

    arsort($amount);
    $sep = key($amount);

    $conf['fields'] = $fields[$sep] + 1;
    $conf['sep']    = $sep;

    // Test if there are fields with quotes around in the first 30 lines
    $quote  = null;

    $string = implode('', $lines);
    foreach (array('"', '\'') as $q) {
      if (preg_match_all("|$sep(?:\s*?)(\=?[$q]).*([$q])$sep|Us", $string, $match)) {
        if ($match[1][0] == $match[2][0]) {
          $quote = $match[1][0];
          break;
        }
      }

      if (
      preg_match_all("|^(\=?[$q]).*([$q])$sep{0,1}|Ums", $string, $match)
      || preg_match_all("|(\=?[$q]).*([$q])$sep\s$|Ums", $string, $match)
      ) {
        if ($match[1][0] == $match[2][0]) {
          $quote = $match[1][0];
          break;
        }
      }
    }

    $conf['quote'] = $quote;
    return $conf;
  }
}

