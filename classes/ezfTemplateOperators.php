<?php
/**
 * File containing the ezfTemplateOperators class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

/**
 * Operators for eZFind
 */
class ezfTemplateOperators
{
    const QUOTES_TO_ESCAPE = '"';

    public function operatorList()
    {
        return array(
            'solr_escape',
            'solr_quotes_escape',
            'solr_realescape',
            'solr_date',
            'solr_local_time',
        );
    }

    public function namedParameterPerOperator()
    {
        return true;
    }

    public function namedParameterList()
    {
        return array(
            'solr_escape' => array(),
            'solr_date' => array(),
            'solr_local_time' => array(),
            'solr_realescape' => array(),
            'solr_quotes_escape' => array(
                'leave_edge_quotes' => array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => false
                )
            ),
        );
    }

    public function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        switch( $operatorName )
        {
            case 'solr_escape':
                $operatorValue = $this->escapeQuery( $operatorValue );
                break;

            case 'solr_realescape':
                $operatorValue = $this->realEscapeString( $operatorValue );
                break;

            case 'solr_date':
                $operatorValue = $this->solrDate( $operatorValue );
                break;

            case 'solr_local_time':
                $operatorValue = $this->solrLocalTime( $operatorValue );
                break;

            case 'solr_quotes_escape':
                $operatorValue = $this->escapeQuotes( $operatorValue, (bool)$namedParameters[ 'leave_edge_quotes' ] );
                break;
        }
    }

    /**
     * Encloses $query inside double quotes so that special characters can be handled litteraly by Solr.
     * If there are double quotes inside $query, they will be escaped.
     * Edge quotes are ignored.
     *
     * @param string $query
     * @return string
     * @see http://lucene.apache.org/core/old_versioned_docs/versions/3_4_0/queryparsersyntax.html#Escaping%20Special%20Characters
     * @see http://issues.ez.no/18701
     */
    public function escapeQuery( $query )
    {
        if( $query[ 0 ] === '"' && $query[ strlen( $query ) - 1 ] === '"' )
        {
            $query = substr( $query, 1, -1 );
        }

        return '"' . $this->escapeQuotes( $query ) . '"';
    }

    public function solrDate( $string )
    {
        return ezfSolrDocumentFieldBase::convertTimestampToDate( $string );
    }

    public function solrLocalTime( $string )
    {
        $return = false;

        $utc_date = DateTime::createFromFormat(
            'Y-m-d?H:i:s?',
            $string,
            new DateTimeZone( 'UTC' )
        );

        if( $utc_date )
        {
            $local_date = $utc_date;
            $local_date->setTimeZone(new DateTimeZone( 'America/New_York' ) );

            $return = $local_date->getTimestamp();
        }

        return $return;
    }

    public function realEscapeString( $string )
    {
        $map = array(
            ' ' => '\\ ',
            '+' => '\\+',
            '-' => '\\-',
            '&&' => '\\&&',
            '||' => '\\||',
            '!' => '\\!',
            '(' => '\\(',
            ')' => '\\)',
            '{' => '\\{',
            '}' => '\\}',
            '[' => '\\[',
            ']' => '\\]',
            '^' => '\\^',
            '"' => '\\""',
            '~' => '\\~',
            '*' => '\\*',
            '?' => '\\?',
            ':' => '\\:',
            '\\' => '\\\\',
            '/' => '\\/', // cannot start with forward slash
        );

        return str_replace( array_keys( $map ), array_values($map ), $string );
    }

    /**
     * Escapes quotes in $query.
     *
     * @param string $query
     * @param bool $leaveEdgeQuotes If true, edge quotes surrounding $query will be ignored.
     * @return string
     */
    public function escapeQuotes( $query, $leaveEdgeQuotes = false )
    {
        if ( $leaveEdgeQuotes && $query[0] === '"' && $query[strlen( $query ) -1] === '"' )
        {
            return '"' . addcslashes(
                substr( $query, 1, -1 ),
                self::QUOTES_TO_ESCAPE
            ) . '"';
        }
        else
        {
            return addcslashes( $query, self::QUOTES_TO_ESCAPE );
        }
    }
}
