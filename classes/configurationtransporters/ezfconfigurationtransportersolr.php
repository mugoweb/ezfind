<?php

class eZFConfigurationTransporterSolr extends eZFConfigurationTransporter
{
    public function push( $configuration )
    {
        /**
         * synchronises elevate configuration across language shards in case of
         * multiple language indexes, or the default one
         *
         * @TODO: handle exceptions properly
         */
        $solr = new eZSolr();

        if( $solr->UseMultiLanguageCores )
        {
            foreach ( $solr->SolrLanguageShards as $shard )
            {
                $this->synchronizeWithSolr( $shard, $configuration );
            }
            return true;
        }
        else
        {
            return $this->synchronizeWithSolr( new eZSolrBase(), $configuration );
        }
    }

    /**
     * Synchronizes the elevate configuration stored in the DB
     * with the one actually used by Solr.
     */
    protected function synchronizeWithSolr( $shard, $configuration )
    {
        if( $configuration )
        {
            try
            {
                $this->pushConfigurationToSolr( $shard, $configuration );
            }
            catch ( Exception $e )
            {
                eZFindElevateConfiguration::$lastSynchronizationError = $e->getMessage();
                eZDebug::writeError( eZFindElevateConfiguration::$lastSynchronizationError, __METHOD__ );
                return false;
            }
        }
        else
        {
            $message = ezpI18n::tr( 'extension/ezfind/elevate', "Error while generating the configuration XML" );
            eZFindElevateConfiguration::$lastSynchronizationError = $message;
            eZDebug::writeError( $message, __METHOD__ );
            return false;
        }

        return true;
    }

    /**
     * Pushes the configuration XML to Solr through a custom requestHandler ( HTTP/ReST ).
     * The requestHandler ( Solr extension ) will take care of reloading the configuration.
     */
    protected function pushConfigurationToSolr( $shard, $configuration )
    {
        $params = array(
            'qt' => 'ezfind',
            eZFindElevateConfiguration::CONF_PARAM_NAME => $configuration
        );


        $result = $shard->pushElevateConfiguration( $params );

        if ( ! $result )
        {
            $message = ezpI18n::tr( 'extension/ezfind/elevate', 'An unknown error occured in updating Solr\'s elevate configuration.' );
            eZDebug::writeError( $message, __METHOD__ );
            throw new Exception( $message );
        }
        elseif ( isset( $result['error'] ) )
        {
            eZDebug::writeError( $result['error'], __METHOD__ );
        }
        else
        {
            eZDebug::writeNotice( "Successful update of Solr's configuration.", __METHOD__ );
        }
    }

}