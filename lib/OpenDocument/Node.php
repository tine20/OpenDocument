<?php
/**
 * Tine 2.0
 *
 * @package     OpenDocument
 * @subpackage  OpenDocument
 * @license     http://framework.zend.com/license/new-bsd     New BSD License
 * @copyright   Copyright (c) 2014 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Cornelius Weiß <l.kneschke@metaways.de>
 */

/**
 * represents an opendocument node
 *
 * @package     OpenDocument
 * @subpackage  OpenDocument
 */
abstract class OpenDocument_Node
{
    /**
     * configure style family property
     *
     * @var string
     */
    protected static $_styleFamily = '';

    /**
     * configure supported styles per node type
     *
     * @var array
     */
    protected static $_supportedStyles = array();

    /**
     * holds all nodes
     *
     * @var array id => element
     */
    protected static $_nodes = array();

    /**
     * holds OpenDocument_Node children
     *
     * @var array parenId => array(children nodeIds)
     */
    protected static $_children = array();

    /**
     * holds OpenDocument_Node parents
     *
     * @var array childId => parentId
     */
    protected static $_parents = array();

    /**
     * holds SXElements
     * @var array nodeId => SXElement
     */
    protected static $_SXElements = array();

    /**
     * global node registry
     *
     * @param  $node    OpenDocument_Node
     * @param  $parent  OpenDocument_Node
     * @throws Exception
     */
    public static function registerNode(OpenDocument_Node $node, $parent = null)
    {
        if (self::getNodeId($node) !== false) {
            throw new Exception ('node already registered');
        }

        $nodeId = count(self::$_nodes);
        self::$_nodes[$nodeId] = $node;

        $body = $node->getBody();
        if ($body instanceof OpenDocument_Node) {
            $body = $body->getBody();
        }
        if ($body instanceof SimpleXMLElement) {
            self::$_SXElements[$nodeId] = $body;
        }

        // does this node have a parent already?
        $oldParentId = self::getParentNode($node);

        if (! $parent) {
            // new root element
            self::$_children[$nodeId] = array();
        } else {
            if (! $parent instanceof OpenDocument_Node) {
                throw new Exception('parent must be a OpenDocument_Node');
            }
            // register node
            $parentId = array_search($parent, self::$_nodes, true);
            if ($parentId === false) {
                throw new Exception('parent node not registered');
            }

            self::$_children[$parentId][] = $nodeId;
            self::$_parents[$nodeId] = $parentId;
        }
    }

    public function unregisterNode($node)
    {
        $nodeId = array_search($node, self::$_nodes, true);
        if ($nodeId === false) {
            throw new Exception('node not registered');
        }
        self::$_nodes[$nodeId] = null;
        self::$_SXElements[$nodeId] = null;

        /*
        unset(self::$_parents[$nodeId]);

        $parent = self::getParentNode($node);
        if ($parent) {
            $parentId = array_search($parent, self::$_nodes, true);
            unset (self::$_children[array_search($nodeId, self::$_children[$parentId])]);
        }
        */

    }

    /**
     * get node by id
     *
     * @param  int|OpenDocument_Node|SimpleXMLElement $nodeId
     * @return OpenDocument_Node
     * @throws Exception
     */
    public static function getNode($nodeId)
    {
        if ($nodeId instanceof OpenDocument_Node) {
            return $nodeId;
        }

        if ($nodeId instanceof SimpleXMLElement) {
            $nodeId = self::getNodeId($nodeId);
            if ($nodeId === false) {
                throw new Exception('node not registerd');
            }
        }

        if (! array_key_exists($nodeId, self::$_nodes)) {
            throw new Exception('node not registerd');
        }

        return self::$_nodes[$nodeId];
    }

    /**
     * get id of node
     *
     * @param  OpenDocument_Node | SimpleXMLElement $node
     * @return int|null
     */
    public static function getNodeId($node)
    {
        return $node instanceof OpenDocument_Node ?
            array_search($node, self::$_nodes, true) :
            array_search($node, self::$_SXElements, true);
    }

    /**
     * returns parent node if exists
     *
     * @param OpenDocument_Node $node
     * @return OpenDocument_Node | null
     */
    public static function getParentNode(OpenDocument_Node $node)
    {
        $nodeId = self::getNodeId($node);
        return $nodeId && array_key_exists($nodeId, self::$_parents)? self::$_nodes[self::$_parents[$nodeId]] : null;
    }

    /**
     * find parent by class/type
     *
     * @param $type
     * @return null|OpenDocument_Node
     */
    public function findParentByType($type)
    {
        $current = $this;
        do {
            $current = self::getParentNode($current);
            if ($current instanceof $type) {
                break;
            }

        } while ($current instanceof self);

        return $current;
    }

    /**
     * append style definition to document header
     *
     * @param  string $name    style name
     * @param  array  $styles  array of styles
     * @return $this
     */
    public function appendStyle($name, $styles)
    {
        $areas = array();

        foreach($styles as $styleName => $value) {
            if (! array_key_exists($styleName, static::$_supportedStyles)) {
                throw new Exception("unsupported style $styleName");
            }

            $def = static::$_supportedStyles[$styleName];
            $area = $def[0];
            if (! array_key_exists($area, $areas)) {
                $areas[$area] = array();
            }

            $areas[$area][] = " {$def[1]}:$styleName=\"$value\"";
        }
        $styleXML = '<style:style style:name="' . $name . '" style:family="' . static::$_styleFamily . '" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0">';
        foreach($areas as $areaName => $areaStyles) {
            $styleXML .= "<style:$areaName";
            foreach($areaStyles as $areaStyle) {
                $styleXML .= $areaStyle;
            }
            $styleXML .= "/>";
        }
        $styleXML .= '</style:style>';

        $document = self::findParentByType('OpenDocument_Document');
        return $document->addStyle($styleXML);
    }

    /**
     * set style for this node
     *
     * Examples:
     *   setStyle('ce1');                   // set the style-name attribute
     *   setStyle('font-weight', 'bold');   // set fond weight bold
     *   setStyle(array(                    // set multiple styles at once
     *       'font-weight' => 'bold',
     *       'border-right' => '1.00pt solid #000000'
     *   ));
     *
     * NOTE: styles are not inherited you can't set a name an add additional styles!
     *
     * @param  string | array $style
     * @param  string         $value
     *
     * @return this
     */
    public function setStyle($style, $value=null)
    {
        if (is_string($style)) {
            if (!$value) {
                $this->getBody()->addAttribute('table:style-name', $this->encodeValue($style), OpenDocument_Document::NS_TABLE);
                return $this;
            } else {
                $styles = array($style => $value);
            }
        } else if (is_array($style)) {
            $styles = $style;
        } else {
            throw new Exception('unsupported use of setStyle');
        }

        $name = md5(microtime() . mt_rand(0, 1000));
        $this->appendStyle($name, $styles);
        return $this->setStyle($name);
    }

    /**
     * encodes a value
     *
     * @param $value
     * @return string
     */
    static public function encodeValue($value)
    {
        return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
    }

    /**
     * @return SimpleXMLElement
     */
    abstract public function getBody();
}