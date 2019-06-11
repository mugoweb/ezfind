<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

class eZFindResultNode extends eZContentObjectTreeNode
{
    /*!
     \reimp
    */
    public function __construct( $rows = array() )
    {
        parent::__construct( $rows );
        if ( isset( $rows['id'] ) )
        {
            $this->ContentObjectID = $rows['id'];
        }
        $this->LocalAttributeValueList = array();
        $this->LocalAttributeNameList = array(
            'is_local_installation',
            'name',
            'global_url_alias',
            'published',
            'language_code', // should not be here
            'highlight',
            'score_percent',
            'elevated',
            'doc',
        );
    }

    /*!
     \reimp
    */
    function attribute( $attr, $noFunction = false )
    {
        $retVal = null;

        // SOLR specific attributes
        if( in_array( $attr, $this->LocalAttributeNameList ) )
        {
            // Extra effort for language code - keeping BC
            if( $attr == 'language_code' )
            {
                if ( $this->attribute( 'is_local_installation' ) )
                {
                    $eZObj = parent::attribute( 'object' );
                    $retVal = $eZObj->attribute( 'current_language' );
                }
                else
                {
                    $retVal = $this->CurrentLanguage;
                }
            }
            else
            {
                $retVal = isset( $this->LocalAttributeValueList[ $attr ] ) ? $this->LocalAttributeValueList[$attr] : null;
                // Timestamps are stored as strings for remote objects, so it must be converted.
                if ( $attr == 'published' )
                {
                    $retVal = strtotime( $retVal );
                }
            }
        }
        else
        {
            if ( $this->attribute( 'is_local_installation' ) )
            {
                $retVal = eZContentObjectTreeNode::attribute( $attr, $noFunction );
            }
            else
            {
                switch ( $attr )
                {
                    case 'object':
                    {
                        if ( empty( $this->ResultObject ) )
                        {
                            $objectRow = array(
                                'doc' => &$this->LocalAttributeValueList[ 'doc' ]
                            );
                            $this->ResultObject = new eZFindResultObject( $objectRow );
                        }

                        $retVal = $this->ResultObject;
                    } break;

                    case 'class_identifier':
                    case 'contentclass_id':
                    case 'state_id_array':
                    {
                        /** @var eZFindResultObject $resultObject */
                        $resultObject = $this->attribute( 'object' );
                        $retVal = $resultObject->attribute( $attr );
                    } break;

                    case 'url_alias':
                    {
                        $retVal = $this->LocalAttributeValueList[ 'doc' ][ 'meta_main_url_alias_ms' ];
                    } break;
                }
            }
        }

        return $retVal;
    }

    /*!
     \reimp
    */
    function attributes()
    {
        return array_merge( $this->LocalAttributeNameList,
                            eZContentObjectTreeNode::attributes() );
    }

    /*!
     \reimp
    */
    function hasAttribute( $attr )
    {
        return ( in_array( $attr, $this->LocalAttributeNameList ) ||
                 eZContentObjectTreeNode::hasAttribute( $attr ) );
    }

    /*!
     \reimp
    */
    function setAttribute( $attr, $value )
    {
        switch( $attr )
        {
            case 'language_code':
            {
                $this->CurrentLanguage = $value;
            } break;

            default:
            {
                if ( in_array( $attr, $this->LocalAttributeNameList ) )
                {
                    $this->LocalAttributeValueList[$attr] = $value;
                }
                else
                {
                    eZContentObjectTreeNode::setAttribute( $attr, $value );
                }
            }
        }
    }

    /// Member vars
    var $CurrentLanguage;
    var $LocalAttributeValueList;
    var $LocalAttributeNameList;
    var $ResultObject;
}

