<?php

namespace CL\LunaJsonStore\Rel;

use CL\LunaJsonStore\AbstractJsonRepo;
use CL\LunaCore\Rel\UpdateInterface;
use CL\LunaCore\Rel\AbstractRelOne;
use CL\LunaCore\Model\AbstractModel;
use CL\LunaCore\Repo\AbstractLink;
use CL\LunaCore\Repo\LinkOne;
use CL\Util\Arr;
use InvalidArgumentException;

/**
 * @author     Ivan Kerin
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class One extends AbstractRelOne implements UpdateInterface
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
     * @param  AbstractModel[] $models
     * @return boolean
     */
    public function hasForeign(array $models)
    {
        $keys = Arr::pluckUniqueProperty($models, $this->key);

        return ! empty($keys);
    }

    /**
     * @param  AbstractModel[] $models [description]
     * @return AbstractModel[]
     */
    public function loadForeign(array $models)
    {
        return $this->getForeignRepo()
            ->findAll()
            ->where('id', Arr::pluckUniqueProperty($models, $this->key))
            ->loadRaw();
    }

    /**
     * @param AbstractModel $model [description]
     * @param AbstractLink  $link  [description]
     */
    public function update(AbstractModel $model, AbstractLink $link)
    {
        if (! ($link instanceof LinkOne)) {
            throw new InvalidArgumentException('Must use a LinkOne');
        }

        $model->{$this->key} = $link->get()->getId();
    }
}
