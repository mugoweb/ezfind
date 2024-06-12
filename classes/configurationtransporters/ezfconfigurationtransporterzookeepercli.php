<?php

class eZFConfigurationTransporterZookeeperCli extends ezfConfigurationTransporter
{
    protected $cli;

    public function __construct( $parameters )
    {
        $this->cli = $parameters[ 'cli' ] ?? '/opt/zookeeper/bin/zkCli.sh';
        parent::__construct( $parameters );
    }

    public function push( $configuration )
    {
        exec($this->cli . ' set /configs/ezp_collection_config/elevate.xml ' . escapeshellarg( $configuration ), $output, $exitCode );

        return $exitCode === 0;
    }
}