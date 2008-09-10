<?php
class File_CSV_Converter_CommandLine extends Console_CommandLine
{

  private $strategies_registry = array();
  private $reversed_strategies_registry = array();

  public function __construct(array $params = array(), array $strategies_registry)
  {
    $params['description'] = 'Strategy based CSV file converter';
    $params['version']     = '0.0.1';

    $this->strategies_registry = $strategies_registry;
    $this->reversed_strategies_registry = array_flip($strategies_registry);

    $this->initialize($strategies_registry);

    parent::__construct($params);
  }

  public function getStrategyClassByCommand($command)
  {
    return $this->reversed_strategies_registry[$command];
  }

  /**
   * @param string $option_value
   * @return array
   */
  public static function extractColumnsMapFromCliOption($option_value)
  {
    return explode(',', $option_value);
  }

  private function initialize(array $strategies_registry)
  {
    // Conversion subcommands
    foreach ($this->strategies_registry as $strategy_classname => $strategy_commandname)
    {
      $conversion_cmd = $this->addCommand(
        call_user_func(array($strategy_classname, 'getCliCommandName')), array(
        'description' => call_user_func(array($strategy_classname, 'getCliCommandDescription'))
      ));
      $command_spec = call_user_func(array($strategy_classname, 'getCliCommandSpecification'));

      if (isset($command_spec['options']) && is_array($command_spec['options']))
      {
        foreach ($command_spec['options'] as $option_name => $option_spec)
        {
          $conversion_cmd->addOption($option_name, $option_spec);
        }
      }

      if (isset($command_spec['arguments']) && is_array($command_spec['arguments']))
      {
        foreach ($command_spec['arguments'] as $arg_name => $arg_spec)
        {
          $conversion_cmd->addArgument($arg_name, $arg_spec);
        }
      }

      $conversion_cmd->addArgument('input_file', array('description' => 'path to input file'));
    }

    $this->addOption('output_file', array(
      'short_name'  => '-o',
      'long_name'   => '--output',
      'description' => 'path to output file (defaults to "./converted.out")',
      'help_name'   => 'FILEPATH',
      'action'      => 'StoreString',
      'default'     => getcwd().'/converted.out'
    ));

    $this->addOption('map', array(
      'short_name'  => '-m',
      'long_name'   => '--map',
      'description' => 'CSV columns map',
      'help_name'   => 'MAP',
      'action'      => 'Callback',
      'callback'    => array(__CLASS__, 'extractColumnsMapFromCliOption')
    ));
  }

}