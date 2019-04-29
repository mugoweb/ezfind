<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

class eZFindResultObject extends eZContentObject
{
    /*!
     \reimp
    */
    function __construct( $rows = array() )
    {
        $this->LocalAttributeValueList = array();
		$this->LocalAttributeNameList = array( 'doc' );

        foreach ( $rows as $name => $value )
        {
            $this->setAttribute( $name, $value );
        }
    }

    /*!
     \reimp
    */
    function attribute( $attr, $noFunction = false )
    {
        $returnVal = null;

        if( isset( $this->LocalAttributeValueList[ $attr ] ) )
        {
            $returnVal = $this->LocalAttributeValueList[ $attr ];
        }
        else
        {
            switch( $attr )
            {
                case 'published':
                {
                    $returnVal = strtotime( $this->LocalAttributeValueList[ 'doc' ][ eZSolr::getMetaFieldName( 'published' ) ] );
                } break;

                case 'state_id_array':
                {
                    $returnVal = $this->LocalAttributeValueList[ 'doc' ][ eZSolr::getMetaFieldName( 'object_states' ) ];
                } break;

                case 'contentclass_id':
                case 'class_identifier':
                {
                    $returnVal = $this->LocalAttributeValueList[ 'doc' ][ eZSolr::getMetaFieldName( $attr ) ];
                } break;
            }
        }

        return $returnVal;
    }

    /*!
     \reimp
    */
    function setAttribute( $attr, $value )
    {
        if ( in_array( $attr, $this->LocalAttributeNameList ) )
        {
            $this->LocalAttributeValueList[$attr] = $value;
        }
    }

    /*!
     \reimp
    */
    function attributes()
    {
        return array_merge( $this->LocalAttributeNameList,
                            eZContentObject::attributes() );
    }

    /*!
     \reimp
    */
    function hasAttribute( $attr )
    {
        return ( in_array( $attr, $this->LocalAttributeNameList ) ||
                 eZContentObject::hasAttribute( $attr ) );
    }


    /// Object variables
    var $LocalAttributeValueList;
    var $LocalAttributeNameList;
}

