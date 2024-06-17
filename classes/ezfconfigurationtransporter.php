<?php

class eZFConfigurationTransporter
{
    protected $parameters;

    public function __construct( $parameters )
    {
        $this->parameters = $parameters;
    }

    public function push( $configuration )
    {
        eZFindElevateConfiguration::$lastSynchronizationError = 'ConfigurationTransporter class does not exits.';
        return false;
    }

    public static function factory()
    {
        $settings = eZINI::instance( 'solr.ini' );
        $class = $settings->variable( 'SolrBase', 'ConfigurationTransporter' );
        $parameters = $settings->variable( 'SolrBase', 'ConfigurationTransporterParameters' );

        if( class_exists( $class ) )
        {
            return new $class( $parameters );
        }
        else
        {
            return new self( $parameters );
        }
    }
}