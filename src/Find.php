<?php

namespace CL\LunaJsonStore;

use CL\LunaCore\Model\AbstractModel;
use CL\LunaCore\Model\State;
use CL\LunaCore\Save\AbstractFind;

/*
 * @author     Ivan Kerin
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Find extends AbstractFind
{
    /**
     * @param  mixed   $value
     * @param  mixed   $condition
     * @return boolean
     */
    public static function isConditionMatch($value, $condition)
    {
        if ($condition instanceof Not) {
            return $value !== $condition->getValue();
        } elseif (is_array($condition)) {
            return in_array($value, $condition);
        } else {
            return $value === $condition;
        }
    }

    /**
     * @var array
     */
    private $conditions = array();

    /**
     * @var int
     */
    public $limit = null;

    /**
     * @var int
     */
    public $offset = 0;

    public function __construct(AbstractJsonRepo $repo)
    {
        parent::__construct($repo);
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param  string $property
     * @param  mixed  $value
     * @return Find   $this
     */
    public function where($property, $value)
    {
        $this->conditions[$property] = $value;

        return $this;
    }

    /**
     * @param  string $property
     * @param  mixed  $value
     * @return Find   $this
     */
    public function whereNot($property, $value)
    {
        $this->conditions[$property] = new Not($value);

        return $this;
    }

    /**
     * @param  string $property
     * @param  array  $value
     * @return Find   $this
     */
    public function whereIn($property, array $value)
    {
        $this->conditions[$property] = $value;

        return $this;
    }

    /**
     * @return Find   $this
     */
    public function clearWhere()
    {
        $this->conditions = null;

        return $this;
    }

    /**
     * @param  int  $limit
     * @return Find $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return Find   $this
     */
    public function clearLimit()
    {
        $this->limit = null;

        return $this;
    }

    /**
     * @param  int  $offset
     * @return Find $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return Find   $this
     */
    public function clearOffset()
    {
        $this->offset = 0;

        return $this;
    }

    /**
     * @param  array   $properties
     * @return boolean
     */
    public function isMatch(array $properties)
    {
        foreach ($this->conditions as $propertyName => $condition) {

            if (! isset($properties[$propertyName])) {
                return false;
            }

            if (! self::isConditionMatch($properties[$propertyName], $condition)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array         $properties
     * @return AbstractModel
     */
    public function newModel(array $properties)
    {
        if ($this->getRepo()->getInherited()) {
            $class = $properties['class'];

            return new $class($properties, State::SAVED);
        } else {
            return $this->getRepo()->newModel($properties, State::SAVED);
        }
    }

    /**
     * @return AbstractModel[]
     */
    public function execute()
    {
        $found = array();

        $contents = $this->getRepo()->getContents();

        foreach ($contents as $properties) {
            if ($this->isMatch($properties)) {
                $found []= $this->newModel($properties);
            }
        }

        return $found;
    }
}
