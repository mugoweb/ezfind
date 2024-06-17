<?php

class eZFConfigurationTransporterZookeeperCli extends ezfConfigurationTransporter
{
    protected $cli;
    protected $configFilePath;

    public function __construct( $parameters )
    {
        $this->cli = $parameters[ 'cli' ] ?? '/opt/zookeeper/bin/zkCli.sh';
        $this->configFilePath = $parameters[ 'ConfigFilePath' ] ?? '/configs/ezp_collection_config/elevate.xml';

        parent::__construct( $parameters );
    }

    public function push( $configuration )
    {
        exec($this->cli . ' set '. $this->configFilePath . ' ' . escapeshellarg( $configuration ), $output, $exitCode );

        return $exitCode === 0;
    }
}