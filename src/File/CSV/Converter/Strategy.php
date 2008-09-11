<?php
/**
 * @license GPL3
 */

interface File_CSV_Converter_Strategy
{
  public function __construct(array $params = array());

  public function convert(array $data, $destinationfile_url);

  public static function getCliCommandSpecification();
}
