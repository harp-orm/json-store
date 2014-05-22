<?php

namespace CL\LunaJsonStore\Rel;

use CL\LunaJsonStore\AbstractJsonRepo;
use CL\LunaCore\Rel\AbstractRelOne;
use CL\LunaCore\Model\AbstractModel;
use CL\LunaCore\Model\Models;
use CL\LunaCore\Repo\LinkOne;

/**
 * @author     Ivan Kerin
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class One extends AbstractRelOne
{
    /**
     * Its protected so it can be modified by the parent class
     * @var string
     */
    protected $key;

    /**
     * @param string           $name
     * @param AbstractJsonRepo $repo
     * @param AbstractJsonRepo $foreignRepo
     * @param array            $options
     */
    public function __construct(
        $name,
        AbstractJsonRepo $repo,
        AbstractJsonRepo $foreignRepo,
        array $options = array()
    ) {
        $this->key = lcfirst($foreignRepo->getName()).'Id';

        parent::__construct($name, $repo, $foreignRepo, $options);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param  AbstractModel $model
     * @param  AbstractModel $foreign
     * @return boolean
     */
    public function areLinked(AbstractModel $model, AbstractModel $foreign)
    {
        return $model->{$this->key} == $foreign->getId();
    }

    /**
     * @param  Models $models
     * @return boolean
     */
    public function hasForeign(Models $models)
    {
        $keys = array_filter($models->pluckProperty($this->key));

        return ! empty($keys);
    }

    /**
     * @param  Models $models
     * @param  int $flags
     * @return AbstractModel[]
     */
    public function loadForeign(Models $models, $flags = null)
    {
        $keys = array_filter($models->pluckProperty($this->key));

        return $this->getForeignRepo()
            ->findAll()
            ->whereIn($this->getRepo()->getPrimaryKey(), $keys)
            ->loadRaw($flags);
    }

    /**
     * @param AbstractModel $model
     * @param LinkOne  $link
     */
    public function update(AbstractModel $model, LinkOne $link)
    {
        $model->{$this->key} = $link->get()->getId();
    }
}
