<?php
/**
 * @license GPL3
 */

interface CsvConversionStrategy
{
  public function __construct(array $params = array());

  public function convert(array $data);
}
