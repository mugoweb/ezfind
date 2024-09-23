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

	/**
	 * @param string $elevateXmlString
	 * @return bool
	 */
    public function push( $elevateXmlString )
    {
        exec($this->cli . ' set '. $this->configFilePath . ' \'' . $elevateXmlString . '\'', $output, $exitCode );

        if( $exitCode === 0 )
        {
            $solrBase = new eZSolrBase();
            return $solrBase->reloadCollection();
        }

        return false;
    }
}