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

        //confusing to send an id which is the object ID
        if ( isset( $rows['id'] ) )
        {
            $this->ContentObjectID = $rows['id'];
        }

        $this->LocalAttributeValueList = array();
        $this->LocalAttributeNameList = array(
            'is_local_installation',
            'installation_id',
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
                switch( $attr )
                {
                    case 'installation_id':
                    {
                        $retVal = $this->LocalAttributeValueList[ 'doc' ][ eZSolr::getMetaFieldName( $attr ) ];
                    }
                    break;

                    // Timestamps are stored as human friendly string for remote objects, so it must be converted.
                    // It is better to fetch 'published' from the object
                    case 'published':
                    {
                        $retVal = strtotime( $this->LocalAttributeValueList[ $attr ] );
                    }
                    break;

                    default:
                    {
                        $retVal = isset( $this->LocalAttributeValueList[ $attr ] ) ? $this->LocalAttributeValueList[$attr] : null;
                    }
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
                                'doc' => &$this->LocalAttributeValueList[ 'doc' ],
                                'is_local_installation' => $this->attribute( 'is_local_installation' )
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
                        $retVal = $this->LocalAttributeValueList[ 'doc' ][ eZSolr::getMetaFieldName( $attr ) ];
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

