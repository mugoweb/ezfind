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
         * @TODO: move eZFindElevateConfiguration::synchronizeWithSolr to eZFConfigurationTransporterSolr
         */
        $solr = new eZSolr();

        if( $solr->UseMultiLanguageCores )
        {
            foreach ( $solr->SolrLanguageShards as $shard )
            {
                eZFindElevateConfiguration::synchronizeWithSolr( $shard );
            }
            return true;
        }
        else
        {
            return eZFindElevateConfiguration::synchronizeWithSolr( $solr );
        }
    }
}