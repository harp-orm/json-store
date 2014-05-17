<?php

namespace CL\LunaJsonStore;

use CL\LunaCore\Model\AbstractModel;

/*
 * @author     Ivan Kerin
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Select
{
    /**
     * @param  mixed   $value
     * @param  mixed   $condition
     * @return boolean
     */
    public static function isConditionMatch($value, $condition)
    {
        if (is_array($condition)) {
            return in_array($value, $condition);
        } else {
            return $value == $condition;
        }
    }

    /**
     * @var AbstractJsonRepo
     */
    private $repo;

    /**
     * @var array
     */
    private $conditions = array();

    public function __construct(AbstractJsonRepo $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @return AbstractJsonRepo
     */
    public function getRepo()
    {
        return $this->repo;
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param  string $name
     * @param  mixed  $value
     * @return Select $this
     */
    public function where($name, $value)
    {
        $this->conditions[$name] = $value;

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
     * @return AbstractModel[]
     */
    public function loadRaw()
    {
        $found = array();

        $contents = $this->repo->getContents();

        foreach ($contents as $properties) {
            if ($this->isMatch($properties)) {
                $found []= $this->repo->newInstance($properties, AbstractModel::PERSISTED);
            }
        }

        return $found;
    }

    /**
     * @return AbstractModel[]
     */
    public function load()
    {
        $found = $this->loadRaw();

        return $this->repo->getIdentityMap()->getArray($found);
    }
}
